<?php

namespace Tests\Feature;

use App\Livewire\ProductionDashboard;
use App\Models\AdjustMachineLog;
use App\Models\DailyItemCode;
use App\Models\HourlyRemark;
use App\Models\MasterListItem;
use App\Models\MouldChangeLog;
use App\Models\ProductionNgDetail;
use App\Models\ProductionNgType;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductionDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProductionDashboardOptimizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);

        $this->createTestSchema();
    }

    private function createTestSchema(): void
    {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_list_items', function ($table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name')->nullable();
            $table->decimal('cycle_time', 8, 2)->nullable();
            $table->decimal('setup_time_minute', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('daily_item_codes', function ($table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('item_code');
            $table->date('start_date');
            $table->date('schedule_date')->nullable();
            $table->integer('shift')->default(1);
            $table->decimal('resin_usage', 10, 2)->nullable();
            $table->decimal('temporal_cycle_time', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hourly_remarks', function ($table) {
            $table->id();
            $table->foreignId('dic_id');
            $table->string('start_time')->default('08:00');
            $table->integer('target')->default(100);
            $table->integer('actual_production')->default(90);
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_ng_types', function ($table) {
            $table->id();
            $table->string('ng_type');
            $table->timestamps();
        });

        Schema::create('production_ng_details', function ($table) {
            $table->id();
            $table->foreignId('hourly_remark_id');
            $table->foreignId('ng_type_id');
            $table->integer('ng_quantity')->default(0);
            $table->string('ng_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('adjust_machine_logs', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('item_code')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('pic')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mould_change_logs', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('item_code')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('pic')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_production_dashboard_single_query_pipeline()
    {
        $role = Role::create(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        $machine1 = User::create(['name' => 'K0450A', 'email' => 'k450@example.com', 'password' => 'secret']);
        $machine2 = User::create(['name' => 'K0650A', 'email' => 'k650@example.com', 'password' => 'secret']);

        MasterListItem::create([
            'item_code'         => 'PART-001',
            'cycle_time'        => 30.0,
            'setup_time_minute' => 25.0,
        ]);

        // Machine 1 running on 2026-08-15 Shift 1
        $dic1 = DailyItemCode::create([
            'user_id'    => $machine1->id,
            'item_code'  => 'PART-001',
            'start_date' => '2026-08-15',
            'shift'      => 1,
        ]);
        $hr1 = HourlyRemark::create([
            'dic_id'            => $dic1->id,
            'start_time'        => '08:00',
            'target'            => 120,
            'actual_production' => 100,
            'remark'            => 'Minor nozzle clog',
        ]);
        $ngType = ProductionNgType::create(['ng_type' => 'SCRATCH']);
        ProductionNgDetail::create([
            'hourly_remark_id' => $hr1->id,
            'ng_type_id'       => $ngType->id,
            'ng_quantity'      => 10,
        ]);

        // Machine 2 running on 2026-08-15 Shift 1 (NO adjust log on Machine 2)
        $dic2 = DailyItemCode::create([
            'user_id'    => $machine2->id,
            'item_code'  => 'PART-001',
            'start_date' => '2026-08-15',
            'shift'      => 1,
        ]);
        $hr2 = HourlyRemark::create([
            'dic_id'            => $dic2->id,
            'start_time'        => '09:00',
            'target'            => 100,
            'actual_production' => 90,
            'remark'            => 'Normal run',
        ]);
        ProductionNgDetail::create([
            'hourly_remark_id' => $hr2->id,
            'ng_type_id'       => $ngType->id,
            'ng_quantity'      => 15,
        ]);

        // Adjuster logged on Machine 1 only for Shift 1
        $adjLog = AdjustMachineLog::create([
            'user_id'    => $machine1->id,
            'item_code'  => 'PART-001',
            'pic'        => 'Budi (Adjuster)',
            'end_time'   => '2026-08-15 08:35:00',
        ]);
        $adjLog->created_at = '2026-08-15 08:15:00';
        $adjLog->save();

        $service = app(ProductionDashboardService::class);
        $start = Carbon::create(2026, 8, 1)->startOfMonth();
        $end = Carbon::create(2026, 8, 1)->endOfMonth();

        $allData = $service->getAllDashboardData($start, $end, null, null, 'karawang');

        // Verify summary
        $this->assertEquals(220, $allData['summary']['total_target']);
        $this->assertEquals(190, $allData['summary']['total_actual']);
        $this->assertEquals(25, $allData['summary']['total_ng']);

        // Verify Shift 1 includes Budi as the Adjuster
        $shift1 = $allData['shift_personnel_analysis']['shifts'][1];
        $this->assertContains('Budi (Adjuster)', $shift1['adjusters']);
        $this->assertEquals(25, $shift1['total_ng']); // 10 from K0450A + 15 from K0650A

        // Verify Adjuster NG Trend attributes all 25 NG to Budi
        $adjusterTrend = $allData['adjuster_ng_trend'];
        $this->assertTrue($adjusterTrend['has_data']);
        $this->assertEquals('Budi (Adjuster)', $adjusterTrend['adjuster_summaries'][0]['name']);
        $this->assertEquals(25, $adjusterTrend['adjuster_summaries'][0]['total_ng']);

        // Test Livewire component integration
        $this->actingAs($user);
        Livewire::test(ProductionDashboard::class)
            ->set('plant', 'karawang')
            ->set('year', 2026)
            ->set('month', 8)
            ->assertSet('summary.total_ng', 25)
            ->assertSet('summary.total_actual', 190);

        // Test filtering by Machine 1 (K0450A) which had the adjust log
        $machine1Data = $service->getAllDashboardData($start, $end, null, (string)$machine1->id, 'karawang');
        $this->assertEquals(10, $machine1Data['summary']['total_ng']);
        $this->assertEquals(100, $machine1Data['summary']['total_actual']);
        $this->assertContains('Budi (Adjuster)', $machine1Data['shift_personnel_analysis']['shifts'][1]['adjusters']);

        // Test filtering by Machine 2 (K0650A) which had no direct adjust log
        $machine2Data = $service->getAllDashboardData($start, $end, null, (string)$machine2->id, 'karawang');
        $this->assertEquals(15, $machine2Data['summary']['total_ng']);
        $this->assertEquals(90, $machine2Data['summary']['total_actual']);
        $this->assertEmpty($machine2Data['shift_personnel_analysis']['shifts'][1]['adjusters']);
    }

    public function test_production_dashboard_daily_view()
    {
        $user = User::create([
            'name'     => 'Admin User 2',
            'email'    => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);

        $machine = User::create(['name' => 'K0450A', 'email' => 'k450_2@example.com', 'password' => 'secret']);
        $dic = DailyItemCode::create([
            'user_id'    => $machine->id,
            'item_code'  => 'PART-DAILY',
            'start_date' => '2026-08-15',
            'shift'      => 1,
        ]);
        HourlyRemark::create([
            'dic_id'            => $dic->id,
            'start_time'        => '08:00',
            'target'            => 100,
            'actual_production' => 95,
        ]);

        $service = app(ProductionDashboardService::class);
        $date = Carbon::parse('2026-08-15');
        $dailyData = $service->getAllDashboardData($date->copy()->startOfDay(), $date->copy()->endOfDay());

        $this->assertCount(24, $dailyData['chart_data']);
        $this->assertEquals(100, $dailyData['summary']['total_target']);
        $this->assertEquals(95, $dailyData['summary']['total_actual']);
    }

    public function test_adjuster_and_mould_change_utc_to_wib_shift_assignment()
    {
        $machine = User::create(['name' => 'K0450A', 'email' => 'k0450a@example.com', 'password' => 'secret']);
        $otherMachine = User::create(['name' => 'K0650A', 'email' => 'k0650a@example.com', 'password' => 'secret']);

        MasterListItem::create([
            'item_code'         => 'K-847F1-I6RA0',
            'cycle_time'        => 30.0,
            'setup_time_minute' => 20.0,
        ]);

        // Adjust log 1: Rudi Siswanto at 10:32 WIB (03:32 UTC) -> Shift 1 (07:30 - 15:30)
        $adj1 = AdjustMachineLog::create([
            'user_id'   => $machine->id,
            'item_code' => 'K-847F1-I6RA0',
            'pic'       => 'Rudi Siswanto',
            'end_time'  => '2026-09-03 03:42:00',
        ]);
        $adj1->created_at = '2026-09-03 03:32:00';
        $adj1->save();

        // Adjust log 2: Haerul Anwar at 19:03 WIB (12:03 UTC) -> Shift 2 (15:30 - 23:30)
        $adj2 = AdjustMachineLog::create([
            'user_id'   => $machine->id,
            'item_code' => 'K-84715-I7AA0NNB',
            'pic'       => 'Haerul Anwar',
            'end_time'  => '2026-09-03 12:10:00',
        ]);
        $adj2->created_at = '2026-09-03 12:03:00';
        $adj2->save();

        // Adjust log 3: Rodi Khayrudin at 23:40 WIB (16:40 UTC) -> Shift 3 (23:30 - 07:30)
        $adj3 = AdjustMachineLog::create([
            'user_id'   => $machine->id,
            'item_code' => 'K-84780-I7000NNB-PE',
            'pic'       => 'Rodi Khayrudin',
            'end_time'  => '2026-09-03 16:52:00',
        ]);
        $adj3->created_at = '2026-09-03 16:40:00';
        $adj3->save();

        // Mould change log 1: Wahyu Eko Prawito at 16:35 WIB (09:35 UTC) -> Shift 2
        $mould1 = MouldChangeLog::create([
            'user_id'   => $machine->id,
            'item_code' => 'K-847F1-I6RA0',
            'pic'       => 'Wahyu Eko Prawito',
            'end_time'  => '2026-09-03 09:50:00',
        ]);
        $mould1->created_at = '2026-09-03 09:35:00';
        $mould1->save();

        // Mould change log 2: Wahyu Eko Prawito at 23:15 WIB (16:15 UTC) -> Shift 2
        $mould2 = MouldChangeLog::create([
            'user_id'   => $machine->id,
            'item_code' => 'K-84715-I7AA0NNB',
            'pic'       => 'Wahyu Eko Prawito',
            'end_time'  => '2026-09-03 16:30:00',
        ]);
        $mould2->created_at = '2026-09-03 16:15:00';
        $mould2->save();

        // An adjust log on ANOTHER machine: Other Person at 10:00 WIB (03:00 UTC) on K0650A
        $adjOther = AdjustMachineLog::create([
            'user_id'   => $otherMachine->id,
            'item_code' => 'OTHER-ITEM',
            'pic'       => 'Other Person',
            'end_time'  => '2026-09-03 03:30:00',
        ]);
        $adjOther->created_at = '2026-09-03 03:00:00';
        $adjOther->save();

        $service = app(ProductionDashboardService::class);
        $selectedDate = Carbon::parse('2026-09-03');

        // Query filtered strictly to Machine K0450A
        $data = $service->getAllDashboardData($selectedDate, $selectedDate, null, (string)$machine->id, 'karawang');
        $shifts = $data['shift_personnel_analysis']['shifts'];

        // Shift 1: Only Rudi Siswanto (10:32 WIB)
        $this->assertEquals(['Rudi Siswanto'], $shifts[1]['adjusters']);
        $this->assertEmpty($shifts[1]['mould_changers']);
        $this->assertEquals(1, $shifts[1]['adjust_count']);
        $this->assertEquals(0, $shifts[1]['mould_change_count']);

        // Shift 2: Haerul Anwar (19:03 WIB) and Wahyu Eko Prawito (16:35 & 23:15 WIB)
        $this->assertEquals(['Haerul Anwar'], $shifts[2]['adjusters']);
        $this->assertEquals(['Wahyu Eko Prawito'], $shifts[2]['mould_changers']);
        $this->assertEquals(1, $shifts[2]['adjust_count']);
        $this->assertEquals(2, $shifts[2]['mould_change_count']);

        // Shift 3: Rodi Khayrudin (23:40 WIB)
        $this->assertEquals(['Rodi Khayrudin'], $shifts[3]['adjusters']);
        $this->assertEmpty($shifts[3]['mould_changers']);
        $this->assertEquals(1, $shifts[3]['adjust_count']);
        $this->assertEquals(0, $shifts[3]['mould_change_count']);

        // Assert 'Other Person' from K0650A is NOT present when filtered to K0450A
        $this->assertNotContains('Other Person', $shifts[1]['adjusters']);
    }
}
