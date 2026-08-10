<?php

namespace Tests\Feature;

use App\Models\SecondProcessManpower;
use App\Models\SecondProcessReport;
use App\Models\SpProductionSession;
use App\Models\SpWorkOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpProductionManpowerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_add_and_remove_line_manpower()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workOrder = SpWorkOrder::create([
            'wo_number' => 'WO-SP-MANPOWER-01',
            'planned_date' => now()->format('Y-m-d'),
            'unit_line' => 'Line A',
            'process_prod' => 'Assembly',
            'part_number' => 'PN-MP-100',
            'part_name' => 'Widget B',
            'customer' => 'Daihatsu',
            'target_qty' => 500,
            'status' => 'released',
            'created_by' => $user->id,
        ]);

        $session = SpProductionSession::create([
            'work_order_id' => $workOrder->id,
            'operator_id' => $user->id,
            'unit_line' => 'Line A',
            'shift' => '1',
            'status' => 'running',
            'started_at' => now()->subHours(8),
            'total_input' => 100,
            'total_good' => 100,
            'total_reject' => 0,
        ]);

        // Add Manpower
        $response = $this->post(route('app.sp-sessions.add-manpower', $session->id), [
            'operator_name' => 'John Doe',
            'employee_no' => 'EMP-1001',
            'role' => 'Quality Inspector',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sp_session_manpowers', [
            'session_id' => $session->id,
            'operator_name' => 'John Doe',
            'role' => 'Quality Inspector',
        ]);

        $manpower = $session->manpowerEntries()->first();

        // Complete session
        $session->update(['status' => 'completed', 'finished_at' => now()]);
        $session->refresh();

        // Approve session and verify sync bridge creates SecondProcessManpower
        $response = $this->post(route('sp-approvals.approve', $session->id));
        $response->assertRedirect(route('sp-approvals.index'));

        $legacyReport = SecondProcessReport::where('part_number', 'PN-MP-100')->first();
        $this->assertNotNull($legacyReport);

        $this->assertDatabaseHas('second_process_manpowers', [
            'report_id' => $legacyReport->id,
            'name' => 'John Doe',
            'role' => 'Quality Inspector',
        ]);
    }
}
