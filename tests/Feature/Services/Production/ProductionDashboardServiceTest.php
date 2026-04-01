<?php

namespace Tests\Feature\Services\Production;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Production\ProductionDashboardService;
use App\Models\DailyItemCode;
use App\Models\User;

class ProductionDashboardServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ProductionDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductionDashboardService();

        // 1. Create Machine
        $machine = new User();
        $machine->name = 'Test Machine D1';
        $machine->email = 'test_machine_' . uniqid() . '@test.com';
        $machine->password = bcrypt('password');
        $machine->role_id = 1;
        $machine->save();

        // 4. Create DailyItemCode for today
        $daily = new DailyItemCode();
        $daily->user_id = $machine->id;
        $daily->item_code = 'DASH-123';
        $daily->schedule_date = '2023-11-01';
        $daily->start_date = '2023-11-01';
        $daily->end_date = '2023-11-01';
        $daily->start_time = '08:00';
        $daily->end_time = '16:00';
        $daily->shift = 1;
        $daily->quantity = 200;
        $daily->loss_package_quantity = 0;
        $daily->final_quantity = 200;
        $daily->actual_quantity = 200;
        $daily->temporal_cycle_time = 15;
        $daily->save();

        // 5. Create MachineJob relation for Dashboard to query against
        $job = new \App\Models\MachineJob();
        $job->user_id = $machine->id;
        $job->item_code = 'DASH-123';
        $job->shift = 1;
        $job->save();
    }

    public function test_get_dashboard_data_structure_and_spk_details()
    {
        $result = $this->service->getDashboardData('2023-11-01', 'Test Machine D1');

        $this->assertIsArray($result);
        $machineData = $result['Test Machine D1'] ?? null;
        
        $this->assertNotNull($machineData);
        
        $this->assertCount(1, $machineData['daily_item_code']);
        $this->assertEquals('DASH-123', $machineData['daily_item_code'][0]['item_code']);
    }
}
