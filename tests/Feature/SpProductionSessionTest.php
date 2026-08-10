<?php

namespace Tests\Feature;

use App\Models\SpWorkOrder;
use App\Models\SpProductionSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SpProductionSessionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $workOrder;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $this->workOrder = SpWorkOrder::create([
            'wo_number' => 'WO-TEST-001',
            'part_number' => 'PN-12345',
            'part_name' => 'Test Part',
            'customer' => 'Test Customer',
            'process_prod' => 'Second Process',
            'target_qty' => 1000,
            'unit_line' => 'Line 1',
            'status' => 'in_progress',
            'planned_date' => now()->toDateString(),
        ]);

        $this->session = SpProductionSession::create([
            'work_order_id' => $this->workOrder->id,
            'operator_id' => $this->user->id,
            'unit_line' => 'Line 1',
            'shift' => '1',
            'status' => 'running',
            'started_at' => now(),
            'total_input' => 0,
            'total_good' => 0,
            'total_reject' => 0,
        ]);
    }

    public function test_can_start_session_from_work_order()
    {
        $newWorkOrder = SpWorkOrder::create([
            'wo_number' => 'WO-TEST-002',
            'part_number' => 'PN-999',
            'part_name' => 'Another Part',
            'customer' => 'Test Customer',
            'process_prod' => 'Second Process',
            'target_qty' => 500,
            'unit_line' => 'Line 2',
            'status' => 'planned',
            'planned_date' => now()->toDateString(),
        ]);

        \App\Models\FirstPieceInspection::create([
            'date' => now()->format('Y-m-d'),
            'model' => 'Model X',
            'part_name' => 'Another Part',
            'part_number' => 'PN-999',
            'overall_judgement' => 'OK',
            'checked_by' => 'Inspector QC',
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->post(route('sp-sessions.start', $newWorkOrder->id));
        $response->assertRedirect();
        
        $this->assertDatabaseHas('sp_production_sessions', [
            'work_order_id' => $newWorkOrder->id,
            'operator_id' => $this->user->id,
            'status' => 'running',
        ]);
    }

    public function test_can_add_production_output()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-production', $this->session->id), [
                'good_qty' => 100,
                'reject_qty' => 0
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sp_production_entries', [
            'session_id' => $this->session->id,
            'good_qty' => 100,
            'reject_qty' => 0,
            'remarks' => null
        ]);

        $this->session->refresh();
        $this->assertEquals(100, $this->session->total_good);
        $this->assertEquals(0, $this->session->total_reject);
    }

    public function test_can_add_defect_type()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-reject', $this->session->id), [
                'defect_type' => 'Scratch',
                'quantity' => 10
            ]);

        $response->assertOk();
        
        $this->assertDatabaseHas('sp_reject_entries', [
            'session_id' => $this->session->id,
            'defect_type' => 'Scratch',
            'quantity' => 10,
            'cause' => null
        ]);
    }

    public function test_can_log_downtime()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-downtime', $this->session->id), [
                'reason' => 'Machine Breakdown',
                'start_time' => '10:00',
                'resume_time' => '10:30'
            ]);

        $response->assertOk();
        
        $this->assertDatabaseHas('sp_downtime_entries', [
            'session_id' => $this->session->id,
            'reason' => 'Machine Breakdown',
            'duration_minutes' => 30
        ]);
    }

    public function test_can_log_input_wip()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 500
            ]);

        $response->assertOk();
        
        $this->assertDatabaseHas('sp_input_entries', [
            'session_id' => $this->session->id,
            'quantity' => 500,
            'pallet_number' => null
        ]);

        $this->session->refresh();
        $this->assertEquals(500, $this->session->total_input);
    }

    public function test_can_finish_session()
    {
        $response = $this->actingAs($this->user)
            ->post(route('app.sp-sessions.finish', $this->session->id), [
                'remarks' => 'Session finished'
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('sp_production_sessions', [
            'id' => $this->session->id,
            'status' => 'completed',
            'remarks' => 'Session finished'
        ]);
        
        $this->session->refresh();
        $this->assertNotNull($this->session->finished_at);
    }
}
