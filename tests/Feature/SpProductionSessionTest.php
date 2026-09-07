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

        $this->session->inputEntries()->create([
            'quantity' => 1000,
            'source' => 'wip',
        ]);
        $this->session->recalculateTotals();
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
            'inspector' => 'Inspector A',
            'checked_at' => now(),
            'checked_by' => 'QC Inspector',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('sp-sessions.start', $newWorkOrder->id), [
                'shift' => '2',
                'remarks' => 'Starting shift 2',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('sp_production_sessions', [
            'work_order_id' => $newWorkOrder->id,
            'operator_id' => $this->user->id,
            'status' => 'running',
        ]);

        $newWorkOrder->refresh();
        $this->assertEquals('in_progress', $newWorkOrder->status);
    }

    public function test_can_add_production_output()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-production', $this->session->id), [
                'good_qty' => 100,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sp_production_entries', [
            'session_id' => $this->session->id,
            'good_qty' => 100,
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
        $initialInput = $this->session->total_input;

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
        $this->assertEquals($initialInput + 500, $this->session->total_input);
    }

    public function test_can_log_input_reworkable()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 200,
                'source' => 'reworkable',
                'pallet_number' => 'BOX-REWORK-01'
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sp_input_entries', [
            'session_id' => $this->session->id,
            'quantity' => 200,
            'source' => 'reworkable',
            'pallet_number' => 'BOX-REWORK-01'
        ]);

        // Verify auto-pending rework entry was created
        $this->assertDatabaseHas('sp_rework_entries', [
            'session_id' => $this->session->id,
            'input_qty' => 200,
            'recovered_qty' => 0,
            'scrapped_qty' => 0,
        ]);

        $this->session->refresh();
        $this->assertEquals(200, $this->session->total_rework_in);
    }

    public function test_deleting_reworkable_input_removes_linked_rework_entry()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 150,
                'source' => 'reworkable',
                'pallet_number' => 'BOX-DEL-01'
            ]);

        $response->assertOk();
        $inputId = $response->json('entry.id');

        $this->assertDatabaseHas('sp_rework_entries', [
            'session_id' => $this->session->id,
            'input_qty' => 150,
        ]);

        // Delete the input entry
        $delResponse = $this->actingAs($this->user)
            ->deleteJson("/app/sp-sessions/{$this->session->id}/input/{$inputId}");

        $delResponse->assertOk();

        $this->assertDatabaseMissing('sp_input_entries', ['id' => $inputId]);
        $this->assertDatabaseMissing('sp_rework_entries', [
            'session_id' => $this->session->id,
            'input_qty' => 150,
        ]);
    }

    public function test_deleting_rework_entry_linked_to_reworkable_input_removes_input_entry()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 80,
                'source' => 'reworkable',
                'pallet_number' => 'BOX-DEL-02'
            ]);

        $response->assertOk();
        $inputId = $response->json('entry.id');
        $reworkId = $response->json('rework_entry.id');

        $this->assertNotNull($reworkId);

        // Delete the rework entry directly
        $delResponse = $this->actingAs($this->user)
            ->deleteJson("/app/sp-sessions/{$this->session->id}/rework/{$reworkId}");

        $delResponse->assertOk();
        $delResponse->assertJson(['deleted_input_id' => $inputId]);

        $this->assertDatabaseMissing('sp_rework_entries', ['id' => $reworkId]);
        $this->assertDatabaseMissing('sp_input_entries', ['id' => $inputId]);
    }

    public function test_cannot_delete_input_if_consumed_by_output()
    {
        // Session currently has 1000 input from setUp and 0 output.
        // Add 500 good output
        $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-production', $this->session->id), [
                'good_qty' => 500,
                'reject_qty' => 0,
            ]);

        // Add a small input of 100 Pcs
        $response = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 100,
                'source' => 'wip',
            ]);
        $inputId = $response->json('entry.id');

        // Total input is now 1100, total good is 500. Deleting 100 input leaves 1000 >= 500 (allowed)
        $delResponse = $this->actingAs($this->user)
            ->deleteJson("/app/sp-sessions/{$this->session->id}/input/{$inputId}");
        $delResponse->assertOk();

        // Now if we try to delete the original 1000 input (remaining would be 0 < 500)
        $initialInputEntry = $this->session->inputEntries()->first();
        $failResponse = $this->actingAs($this->user)
            ->deleteJson("/app/sp-sessions/{$this->session->id}/input/{$initialInputEntry->id}");

        $failResponse->assertStatus(422);
    }

    public function test_can_finish_session()
    {
        $response = $this->actingAs($this->user)
            ->post(route('app.sp-sessions.finish', $this->session->id), [
                'remarks' => 'Session finished'
            ]);

        $response->assertRedirect(route('app.sp-sessions.closeout', $this->session->id));

        $closeoutResponse = $this->actingAs($this->user)
            ->post(route('app.sp-sessions.submit-closeout', $this->session->id), [
                'remarks' => 'Session finished'
            ]);

        $closeoutResponse->assertRedirect();
        
        $this->assertDatabaseHas('sp_production_sessions', [
            'id' => $this->session->id,
            'status' => 'completed',
            'remarks' => 'Session finished'
        ]);
        
        $this->session->refresh();
        $this->assertNotNull($this->session->finished_at);
    }

    public function test_rework_scrap_from_reworkable_input_updates_total_reject_and_final_scrap_in_closeout()
    {
        // Operator logs 100 Pcs reworkable input
        $inputResponse = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $this->session->id), [
                'quantity' => 100,
                'source' => 'reworkable',
            ]);
        $inputResponse->assertOk();

        // Find the auto-created rework entry from reworkable input
        $reworkEntry = $this->session->reworkEntries()->latest('id')->first();
        $this->assertNotNull($reworkEntry);
        $this->assertEquals(100, $reworkEntry->input_qty);

        // Operator logs rework outcome: 2 recovered, 95 scrapped (3 pending)
        $reworkResponse = $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-rework', $this->session->id), [
                'entry_id' => $reworkEntry->id,
                'recovered_qty' => 2,
                'scrapped_qty' => 95,
            ]);
        $reworkResponse->assertOk();

        $this->session->refresh();

        $this->assertEquals(2, $this->session->total_good);
        $this->assertEquals(95, $this->session->total_scrap);
        $this->assertEquals(95, $this->session->total_reject);
        $this->assertEquals(2.06, $this->session->yield);

        // Verify closeout screen renders the correct Final Scrap and prefilled input parts
        $closeoutViewResponse = $this->actingAs($this->user)
            ->get(route('app.sp-sessions.closeout', $this->session->id));

        $closeoutViewResponse->assertOk();
        $closeoutViewResponse->assertSee('95 Pcs');
        $closeoutViewResponse->assertSee('Scrapped on Bench');
        $closeoutViewResponse->assertSee('Repairan 1');
        $closeoutViewResponse->assertSee('Pre-filled from 2 Input Logs');
    }

    public function test_closeout_default_parts_derived_from_input_logs_with_pallets()
    {
        $newWorkOrder = SpWorkOrder::create([
            'wo_number' => 'WO-TEST-INPUTS',
            'part_number' => 'PN-INP-01',
            'part_name' => 'Input Test Part',
            'customer' => 'Customer Test',
            'process_prod' => 'Second Process',
            'target_qty' => 1000,
            'unit_line' => 'Line 1',
            'status' => 'in_progress',
            'planned_date' => now()->toDateString(),
        ]);

        $session = SpProductionSession::create([
            'work_order_id' => $newWorkOrder->id,
            'operator_id' => $this->user->id,
            'unit_line' => 'Line 1',
            'shift' => '1',
            'status' => 'running',
            'started_at' => now(),
            'total_input' => 0,
            'total_good' => 0,
            'total_reject' => 0,
        ]);

        // 1. Add first WIP input
        $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $session->id), [
                'quantity' => 500,
                'source' => 'wip',
                'pallet_number' => 'PALLET-WIP-01',
            ])->assertOk();

        // 2. Add second WIP input
        $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $session->id), [
                'quantity' => 300,
                'source' => 'wip',
                'pallet_number' => 'PALLET-WIP-02',
            ])->assertOk();

        // 3. Add Reworkable input
        $this->actingAs($this->user)
            ->postJson(route('app.sp-sessions.add-input', $session->id), [
                'quantity' => 80,
                'source' => 'reworkable',
                'pallet_number' => 'BOX-REP-01',
            ])->assertOk();

        // Load closeout view
        $response = $this->actingAs($this->user)
            ->get(route('app.sp-sessions.closeout', $session->id));

        $response->assertOk();
        $response->assertSee('Pre-filled from 3 Input Logs');
        $response->assertSee('"item_name":"WIP 1","lot_number":"PALLET-WIP-01","qty":500', false);
        $response->assertSee('"item_name":"WIP 2","lot_number":"PALLET-WIP-02","qty":300', false);
        $response->assertSee('"item_name":"Repairan 1","lot_number":"BOX-REP-01","qty":80', false);
    }
}

