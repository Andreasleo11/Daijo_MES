<?php

namespace Tests\Feature;

use App\Models\SecondProcessReport;
use App\Models\SecondProcessNgRecord;
use App\Models\SecondProcessTrouble;
use App\Models\SpProductionSession;
use App\Models\SpWorkOrder;
use App\Models\User;
use App\Services\SecondProcessReportSyncBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SpProductionSyncBridgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_session_syncs_to_legacy_second_process_report()
    {
        Gate::define('approve-sp-sessions', fn () => true);

        $user = User::factory()->create();
        $this->actingAs($user);

        $workOrder = SpWorkOrder::create([
            'wo_number' => 'WO-SP-TEST-001',
            'planned_date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'process_prod' => 'Assembly',
            'part_number' => 'PN-SYNC-100',
            'part_name' => 'Widget A',
            'model' => 'Model X',
            'customer' => 'Toyota',
            'target_qty' => 1000,
            'status' => 'released',
            'created_by' => $user->id,
        ]);

        $session = SpProductionSession::create([
            'work_order_id' => $workOrder->id,
            'operator_id' => $user->id,
            'unit_line' => 'Line 1',
            'shift' => '1',
            'status' => 'completed',
            'started_at' => now()->subHours(8),
            'finished_at' => now(),
            'total_input' => 500,
            'total_good' => 480,
            'total_reject' => 20,
            'total_rework_in' => 10,
            'total_rework_recovered' => 8,
            'total_scrap' => 2,
        ]);

        // Add a production entry (15 minutes into the session)
        $session->productionEntries()->create([
            'recorded_at' => $session->started_at->copy()->addMinutes(15),
            'good_qty' => 480,
        ]);

        // Add a reject entry (20 minutes into the session)
        $reject = $session->rejectEntries()->create([
            'defect_type' => 'Flash',
            'quantity' => 20,
            'cause' => 'High temperature',
        ]);
        $reject->timestamps = false;
        $reject->created_at = $session->started_at->copy()->addMinutes(20);
        $reject->save();

        // Add a downtime entry
        $session->downtimeEntries()->create([
            'reason' => 'Material Delay',
            'start_time' => '10:00',
            'resume_time' => '10:25',
            'remarks' => 'Waiting for resin',
        ]);

        // Post to approval endpoint
        $response = $this->post(route('sp-approvals.approve', $session->id));

        $response->assertRedirect(route('sp-approvals.index'));
        $this->assertNotNull($session->fresh()->approved_at);

        // Assert legacy SecondProcessReport was created and mapped correctly
        $this->assertDatabaseHas('second_process_reports', [
            'part_number' => 'PN-SYNC-100',
            'unit_line' => 'Line 1',
            'shift' => '1',
            'jumlah_ok' => 480,
            'jumlah_ng' => 20,
            'jumlah_output' => 500,
            'jml_input_wip' => 500,
            'repairan' => 8,
            'jml_ng_lebur' => 2,
            'status' => 'Approved',
        ]);

        $legacyReport = SecondProcessReport::where('part_number', 'PN-SYNC-100')->first();
        $this->assertNotNull($legacyReport);

        // Assert Hourly Productions synced
        $this->assertDatabaseHas('second_process_hourly_productions', [
            'report_id' => $legacyReport->id,
            'hour_ke' => '1',
            'ok_qty' => 480,
            'ng_qty' => 20,
            'acumulasi_qty' => 480,
        ]);

        // Assert NG records synced
        $this->assertDatabaseHas('second_process_ng_records', [
            'report_id' => $legacyReport->id,
            'ng_name' => 'Flash',
            'total_ng' => 20,
        ]);

        // Assert Troubles synced
        $this->assertDatabaseHas('second_process_troubles', [
            'report_id' => $legacyReport->id,
            'masalah' => 'Material Delay',
            'loss_time_minutes' => 25,
        ]);

        // Assert 1-to-1 foreign key linkage
        $this->assertEquals($legacyReport->id, $session->fresh()->second_process_report_id);
        $this->assertEquals($session->id, $legacyReport->sp_production_session_id);
    }

    public function test_multiple_approved_sessions_on_same_line_and_shift_create_distinct_reports(): void
    {
        Gate::define('approve-sp-sessions', fn () => true);

        $user = User::factory()->create();
        $this->actingAs($user);

        $wo = SpWorkOrder::create([
            'wo_number' => 'WO-MULTI-001',
            'planned_date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'process_prod' => 'Painting',
            'part_number' => 'PN-MULTI-SAME',
            'part_name' => 'Panel X',
            'model' => 'Model X',
            'customer' => 'Customer A',
            'target_qty' => 1000,
            'status' => 'released',
            'created_by' => $user->id,
        ]);

        // Session 1
        $session1 = SpProductionSession::create([
            'work_order_id' => $wo->id,
            'operator_id' => $user->id,
            'unit_line' => 'Line 1',
            'shift' => '1',
            'status' => 'completed',
            'started_at' => now()->subHours(6),
            'finished_at' => now()->subHours(3),
            'total_input' => 300,
            'total_good' => 290,
            'total_reject' => 10,
        ]);

        // Session 2 (same line, shift, date, part)
        $session2 = SpProductionSession::create([
            'work_order_id' => $wo->id,
            'operator_id' => $user->id,
            'unit_line' => 'Line 1',
            'shift' => '1',
            'status' => 'completed',
            'started_at' => now()->subHours(3),
            'finished_at' => now(),
            'total_input' => 200,
            'total_good' => 195,
            'total_reject' => 5,
        ]);

        // Approve Session 1
        $this->post(route('sp-approvals.approve', $session1->id));

        // Approve Session 2
        $this->post(route('sp-approvals.approve', $session2->id));

        // Assert strictly 2 distinct reports exist!
        $this->assertEquals(2, SecondProcessReport::where('part_number', 'PN-MULTI-SAME')->count());

        $session1->refresh();
        $session2->refresh();

        $this->assertNotNull($session1->second_process_report_id);
        $this->assertNotNull($session2->second_process_report_id);
        $this->assertNotEquals($session1->second_process_report_id, $session2->second_process_report_id);

        $report1 = SecondProcessReport::find($session1->second_process_report_id);
        $report2 = SecondProcessReport::find($session2->second_process_report_id);

        $this->assertEquals(290, $report1->jumlah_ok);
        $this->assertEquals(195, $report2->jumlah_ok);
        $this->assertEquals('Approved', $report1->status);
        $this->assertEquals('Approved', $report2->status);

        // Revert Session 1 back for correction
        $this->post(route('sp-approvals.reject', $session1->id), ['reason' => 'Recount needed']);

        // Assert Report 1 is demoted to Draft, while Report 2 remains Approved
        $this->assertEquals('Draft', $report1->fresh()->status);
        $this->assertEquals('Approved', $report2->fresh()->status);
    }
}
