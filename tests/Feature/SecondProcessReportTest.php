<?php

namespace Tests\Feature;

use App\Models\FirstPieceInspection;
use App\Models\Role;
use App\Models\SecondProcessReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecondProcessReportTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRole = Role::create(['name' => 'ADMIN']);
    }

    /**
     * Test index page requires authentication.
     */
    public function test_index_page_requires_authentication(): void
    {
        $response = $this->get(route('second-process-reports.index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test index page is accessible for authenticated users.
     */
    public function test_index_page_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($user)->get(route('second-process-reports.index'));
        $response->assertOk();
    }

    /**
     * Test create page is accessible to authenticated users.
     */
    public function test_create_page_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($user)->get(route('second-process-reports.create'));
        $response->assertOk();
    }

    /**
     * Test storing a report with full nested relationship payload including IPQC.
     */
    public function test_can_store_second_process_report(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        $payload = [
            'date' => '2026-07-07',
            'unit_line' => 'Painting Line A',
            'shift' => '1',
            'process_prod' => 'Painting',
            'status' => 'draft',
            'output_destination' => 'fg',
            'part_number' => 'PART-XYZ-01',
            'part_name' => 'Car Bumper Cover',
            'model' => 'Sedan 2026',
            'customer' => 'Toyota Motor Corp',
            'target_per_hour' => 100,
            'jml_input_wip' => 800,
            'repairan' => 10,
            'jml_ng_lebur' => 5,
            'ng_remarks' => 'Minor paint run observed on hour 4',

            // IPQC Header & Records
            'ipqc_lot_color' => 'LOT-RED-01',
            'ipqc_std_glossy' => '80-90',
            'ipqc_std_viscosity' => '10-12s',
            'ipqc' => [
                [
                    'hour_ke' => 1,
                    'fitting_test' => 'OK',
                    'tape_test_judgement' => 'OK',
                    'output_qty' => 183,
                    'sample_qty' => 50,
                    'reject_sample_qty' => 0,
                    'pass_qty' => 183,
                    'reject_qty' => 0,
                    'judgement' => 'OK',
                ],
            ],

            // Materials
            'materials' => [
                [
                    'type' => 'paint',
                    'item_name' => 'Paint Primer',
                    'lot_number' => 'LOT-PRM-99',
                    'visco' => '14s',
                    'mixing_ratio' => '1:1.5',
                    'qty' => 5,
                ],
            ],

            // Manpower
            'manpower' => [
                [
                    'role' => 'loading',
                    'no' => 1,
                    'name' => 'John Doe',
                ],
            ],

            // Hourly Productions
            'hourly' => [
                [
                    'hour_ke' => 1,
                    'ok_qty' => 90,
                    'ng_qty' => 10,
                    'acumulasi_qty' => 90,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);

        $response->assertRedirect(route('second-process-reports.index'));
        $this->assertDatabaseHas('second_process_reports', [
            'part_number' => 'PART-XYZ-01',
            'ipqc_lot_color' => 'LOT-RED-01',
            'ipqc_total_output' => 183,
        ]);

        $report = SecondProcessReport::with('ipqcRecords')->first();
        $this->assertCount(1, $report->ipqcRecords);
        $this->assertEquals(183, $report->ipqcRecords[0]->output_qty);
    }

    /**
     * Test PQC approval blocks when First Piece is missing or not approved.
     */
    public function test_pqc_approval_blocks_when_first_piece_not_approved(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id, 'name' => 'Approver Admin']);

        $report = SecondProcessReport::create([
            'date' => '2026-07-07',
            'unit_line' => 'Painting Line A',
            'shift' => '1',
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'PART-UNAPPROVED-01',
            'part_name' => 'Bumper Cover',
            'model' => 'Sedan 2026',
            'customer' => 'Toyota Corp',
        ]);

        // Attempt PQC sign without First Piece approval
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'pqc']));
        $response->assertRedirect();
        $response->assertSessionHasErrors('error');

        $report->refresh();
        $this->assertEquals('submitted', $report->status);
    }

    /**
     * Test full signature workflow including First Piece approval check.
     */
    public function test_approval_signature_workflow(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id, 'name' => 'Approver Admin']);

        // Create approved First Piece inspection
        FirstPieceInspection::create([
            'date' => '2026-07-07',
            'model' => 'Sedan 2026',
            'part_name' => 'Bumper Cover',
            'part_number' => 'PART-XYZ-01',
            'overall_judgement' => 'OK',
            'checked_by' => 'QC Inspector',
            'checked_at' => now(),
        ]);

        $report = SecondProcessReport::create([
            'date' => '2026-07-07',
            'unit_line' => 'Painting Line A',
            'shift' => '1',
            'process_prod' => 'Painting',
            'status' => 'draft',
            'part_number' => 'PART-XYZ-01',
            'part_name' => 'Bumper Cover',
            'model' => 'Sedan 2026',
            'customer' => 'Toyota Corp',
        ]);

        // 1. Submit as Checker
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'checker']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('submitted', $report->status);

        // 2. Sign as PQC
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'pqc']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('pqc_approved', $report->status);
        $this->assertEquals('Approver Admin', $report->pqc_name);

        // 3. Sign as Leader
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'leader']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('leader_approved', $report->status);

        // 4. Sign as Supervisor (acknowledged)
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'acknowledged']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('acknowledged', $report->status);
    }

    /**
     * Test search items endpoint returns project_code and customer_name.
     */
    public function test_search_items_returns_project_code_and_customer(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        $cust = \App\Models\MasterCustomerDelivery::create([
            'customer_code' => 'CUST-TEST-01',
            'customer_name' => 'Test Customer Corp',
        ]);

        \App\Models\MasterListItem::create([
            'item_code' => 'ITEM-SEARCH-01',
            'item_name' => 'Test Widget Part',
            'tipe_mesin' => '0',
            'standart_packaging_list' => 10,
            'setup_time_minute' => '0',
            'pair' => '0',
            'cavity' => 1,
            'cycle_time' => 1.0,
            'project_code' => 'MODEL-TEST-99',
            'customer_code' => 'CUST-TEST-01',
        ]);

        $response = $this->actingAs($user)->get(route('second-process-reports.search-items', ['query' => 'ITEM-SEARCH']));

        $response->assertOk()
            ->assertJsonFragment([
                'item_code' => 'ITEM-SEARCH-01',
                'item_name' => 'Test Widget Part',
                'project_code' => 'MODEL-TEST-99',
                'customer_name' => 'Test Customer Corp',
            ]);
    }

    /**
     * Test search customers endpoint returns MasterCustomerDelivery items.
     */
    public function test_search_customers_returns_master_customer(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        \App\Models\MasterCustomerDelivery::create([
            'customer_code' => 'CUST-DEL-88',
            'customer_name' => 'Daijo Motor Supply',
        ]);

        $response = $this->actingAs($user)->get(route('second-process-reports.search-customers', ['query' => 'Daijo']));

        $response->assertOk()
            ->assertJsonFragment([
                'customer_code' => 'CUST-DEL-88',
                'customer_name' => 'Daijo Motor Supply',
            ]);
    }

    /**
     * Test storing freeform materials in second process report.
     */
    public function test_store_freeform_materials_for_painting_process(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        $payload = [
            'date' => '2026-08-03',
            'shift' => '1',
            'unit_line' => 'Painting Line A',
            'process_prod' => 'Painting',
            'part_number' => 'FREEFORM-PART-01',
            'part_name' => 'Custom Painted Cover',
            'model' => 'MODEL-FREEFORM',
            'target_per_hour' => 100,
            'materials' => [
                [
                    'type' => 'paint',
                    'item_name' => 'Custom Epoxy Primer Black',
                    'lot_number' => 'LOT-EP-001',
                    'visco' => '16s',
                    'mixing_ratio' => '2:1',
                    'qty' => 10,
                ],
                [
                    'type' => 'part',
                    'item_name' => 'Custom WIP Sub-assembly',
                    'lot_number' => 'WIP-LOT-99',
                    'qty' => 50,
                ],
            ],
            'hourly' => [
                [
                    'hour_ke' => 1,
                    'ok_qty' => 50,
                    'ng_qty' => 0,
                    'acumulasi_qty' => 50,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);

        $response->assertRedirect(route('second-process-reports.index'));
        $this->assertDatabaseHas('second_process_materials', [
            'type' => 'paint',
            'item_name' => 'Custom Epoxy Primer Black',
            'lot_number' => 'LOT-EP-001',
            'visco' => '16s',
            'mixing_ratio' => '2:1',
            'qty' => 10,
        ]);
        $this->assertDatabaseHas('second_process_materials', [
            'type' => 'part',
            'item_name' => 'Custom WIP Sub-assembly',
            'lot_number' => 'WIP-LOT-99',
            'qty' => 50,
        ]);
    }
}

