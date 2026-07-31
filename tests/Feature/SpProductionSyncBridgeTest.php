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
use Tests\TestCase;

class SpProductionSyncBridgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_session_syncs_to_legacy_second_process_report()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workOrder = SpWorkOrder::create([
            'wo_number' => 'WO-SP-TEST-001',
            'planned_date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'shift' => '1',
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
            'reject_qty' => 20,
        ]);

        // Add a reject entry
        $session->rejectEntries()->create([
            'defect_type' => 'Flash',
            'quantity' => 20,
            'cause' => 'High temperature',
        ]);

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
    }
}
