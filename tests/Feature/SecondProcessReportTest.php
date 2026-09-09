<?php

namespace Tests\Feature;

use App\Models\FirstPieceInspection;
use App\Models\Role;
use App\Models\SecondProcessReport;
use App\Models\SecondProcessTrouble;
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
        ]);
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

    public function test_store_legacy_report_stores_null_when_schedule_is_empty_or_all_blank()
    {
        $user = User::factory()->create();

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-SCHED-01',
            'next_production_schedule' => ['', '   ', null, ''],
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-SCHED-01')->first();
        $this->assertNotNull($report);
        $this->assertNull($report->next_production_schedule);
    }

    public function test_store_legacy_report_stores_trimmed_array_when_valid_schedule_items_provided()
    {
        $user = User::factory()->create();

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-SCHED-02',
            'next_production_schedule' => ['Part X Shift 1', '', '  Part Y Shift 2  ', ''],
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-SCHED-02')->first();
        $this->assertNotNull($report);
        $this->assertIsArray($report->next_production_schedule);
        $this->assertEquals(['Part X Shift 1', 'Part Y Shift 2'], $report->next_production_schedule);
    }

    public function test_store_legacy_report_does_not_save_untouched_material_presets()
    {
        $user = User::factory()->create();

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-MAT-PRESET',
            'materials' => [
                ['type' => 'paint', 'item_name' => 'Paint Primer', 'lot_number' => '', 'visco' => '', 'mixing_ratio' => '', 'qty' => '', 'uom' => ''],
                ['type' => 'paint', 'item_name' => 'Hardener', 'lot_number' => '', 'visco' => '', 'mixing_ratio' => '', 'qty' => '', 'uom' => ''],
                ['type' => 'paint', 'item_name' => 'Paint Basecoat', 'lot_number' => '', 'visco' => '', 'mixing_ratio' => '', 'qty' => '', 'uom' => ''],
            ],
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-MAT-PRESET')->first();
        $this->assertNotNull($report);
        $this->assertCount(0, $report->materials);
    }

    public function test_store_legacy_report_validates_output_destination_in_list()
    {
        $user = User::factory()->create();

        // Invalid output destination fails
        $failResponse = $this->actingAs($user)->post(route('second-process-reports.store'), [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-DEST-01',
            'output_destination' => 'warehouse_unknown',
        ]);
        $failResponse->assertSessionHasErrors('output_destination');

        // Valid output destination succeeds
        $successResponse = $this->actingAs($user)->post(route('second-process-reports.store'), [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-DEST-02',
            'output_destination' => 'next_process',
        ]);
        $successResponse->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-DEST-02')->first();
        $this->assertNotNull($report);
        $this->assertEquals('next_process', $report->output_destination);
    }

    public function test_show_legacy_report_renders_material_empty_states_when_no_materials_recorded()
    {
        $user = User::factory()->create();

        $report = SecondProcessReport::create([
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-EMPTY-MAT',
        ]);

        $response = $this->actingAs($user)->get(route('second-process-reports.show', $report->id));

        $response->assertOk();
        $response->assertSee('No paint materials recorded');
        $response->assertSee('No item parts recorded');
    }

    public function test_store_legacy_report_omits_empty_trouble_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        FirstPieceInspection::create([
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-TROUBLE-NONE',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'overall_judgement' => 'OK',
            'checked_by' => 'QC Tester',
            'checked_at' => now(),
        ]);

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-TROUBLE-NONE',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'target_qty' => 100,
            'output_destination' => 'fg',
            'troubles' => [
                ['penyebab' => 'Man', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'Mesin', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'Part', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'PPS', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'Lingkungan', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-TROUBLE-NONE')->first();
        $this->assertNotNull($report);
        $this->assertEquals(0, SecondProcessTrouble::where('report_id', $report->id)->count());
    }

    public function test_store_legacy_report_persists_populated_troubles_with_auto_generated_loss_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        FirstPieceInspection::create([
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-TROUBLE-POP',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'overall_judgement' => 'OK',
            'checked_by' => 'QC Tester',
            'checked_at' => now(),
        ]);

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-TROUBLE-POP',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'target_qty' => 100,
            'output_destination' => 'fg',
            'troubles' => [
                ['penyebab' => 'Man', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'Mesin', 'masalah' => 'Nozzle clogged', 'penanganan' => 'Cleaned nozzle', 'loss_time_minutes' => '30'],
                ['penyebab' => 'Part', 'masalah' => 'Waiting raw materials', 'penanganan' => 'Contacted warehouse', 'loss_time_minutes' => '15'],
                ['penyebab' => 'PPS', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
                ['penyebab' => 'Lingkungan', 'masalah' => '', 'penanganan' => '', 'loss_time_minutes' => ''],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-TROUBLE-POP')->first();
        $this->assertNotNull($report);
        
        $troubles = SecondProcessTrouble::where('report_id', $report->id)->get();
        $this->assertCount(2, $troubles);

        $mesinTrouble = $troubles->where('penyebab', 'Mesin')->first();
        $this->assertNotNull($mesinTrouble);
        $this->assertEquals('Nozzle clogged', $mesinTrouble->masalah);
        $this->assertEquals('Cleaned nozzle', $mesinTrouble->penanganan);
        $this->assertEquals(30, $mesinTrouble->loss_time_minutes);
        $this->assertEquals('30 mins', $mesinTrouble->loss_time);

        $partTrouble = $troubles->where('penyebab', 'Part')->first();
        $this->assertNotNull($partTrouble);
        $this->assertEquals('Waiting raw materials', $partTrouble->masalah);
        $this->assertEquals('Contacted warehouse', $partTrouble->penanganan);
        $this->assertEquals(15, $partTrouble->loss_time_minutes);
        $this->assertEquals('15 mins', $partTrouble->loss_time);

        // Show page check
        $showResponse = $this->get(route('second-process-reports.show', $report->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Nozzle clogged');
        $showResponse->assertSee('Waiting raw materials');
        $showResponse->assertSee('Total Loss Time:');
        $showResponse->assertSee('45 mins');
    }

    public function test_store_legacy_report_with_problem_approach_repeater()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        FirstPieceInspection::create([
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-PROB-APP',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'overall_judgement' => 'OK',
            'checked_by' => 'QC Tester',
            'checked_at' => now(),
        ]);

        $payload = [
            'date' => now()->toDateString(),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-PROB-APP',
            'part_name' => 'Test Part',
            'model' => 'Model X',
            'target_qty' => 100,
            'output_destination' => 'fg',
            'troubles' => [
                [
                    'masalah' => 'Problem A: Operator misplacement',
                    'penyebab' => 'Man',
                    'penanganan' => 'Retrained operator on jigs',
                    'loss_time_minutes' => '10',
                ],
                [
                    'masalah' => 'Problem B: Heater thermocouple failure',
                    'penyebab' => 'Mesin',
                    'penanganan' => 'Replaced sensor probe',
                    'loss_time_minutes' => '25',
                ],
                [
                    'masalah' => 'Problem C: Viscosity parameter off-spec',
                    'penyebab' => 'PPS',
                    'penanganan' => 'Adjusted thinner ratio',
                    'loss_time_minutes' => '15',
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-PROB-APP')->first();
        $this->assertNotNull($report);

        $troubles = SecondProcessTrouble::where('report_id', $report->id)->orderBy('id')->get();
        $this->assertCount(3, $troubles);

        $this->assertEquals('Problem A: Operator misplacement', $troubles[0]->masalah);
        $this->assertEquals('Man', $troubles[0]->penyebab);
        $this->assertEquals(10, $troubles[0]->loss_time_minutes);
        $this->assertEquals('10 mins', $troubles[0]->loss_time);

        $this->assertEquals('Problem B: Heater thermocouple failure', $troubles[1]->masalah);
        $this->assertEquals('Mesin', $troubles[1]->penyebab);
        $this->assertEquals(25, $troubles[1]->loss_time_minutes);
        $this->assertEquals('25 mins', $troubles[1]->loss_time);

        $this->assertEquals('Problem C: Viscosity parameter off-spec', $troubles[2]->masalah);
        $this->assertEquals('PPS', $troubles[2]->penyebab);
        $this->assertEquals(15, $troubles[2]->loss_time_minutes);
        $this->assertEquals('15 mins', $troubles[2]->loss_time);

        // Show page verification
        $showResponse = $this->get(route('second-process-reports.show', $report->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Problem A: Operator misplacement');
        $showResponse->assertSee('Problem B: Heater thermocouple failure');
        $showResponse->assertSee('Problem C: Viscosity parameter off-spec');
        $showResponse->assertSee('50 mins');
    }

    public function test_part_materials_breakdown_automatically_calculates_jml_input_wip_and_repairan()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        $payload = [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Line A',
            'shift' => '1',
            'process_prod' => 'Painting',
            'status' => 'draft',
            'part_number' => 'PART-BREAKDOWN-01',
            'part_name' => 'Door Panel',
            'target_per_hour' => 100,

            // Part materials breakdown
            'materials' => [
                [
                    'type' => 'part',
                    'item_name' => 'WIP 1',
                    'lot_number' => 'LOT-WIP-A',
                    'qty' => 450,
                    'uom' => 'Pcs',
                ],
                [
                    'type' => 'part',
                    'item_name' => 'WIP 2',
                    'lot_number' => 'LOT-WIP-B',
                    'qty' => 350,
                    'uom' => 'Pcs',
                ],
                [
                    'type' => 'part',
                    'item_name' => 'Repairan 1',
                    'lot_number' => 'LOT-REP-01',
                    'qty' => 80,
                    'uom' => 'Pcs',
                ],
                [
                    'type' => 'paint',
                    'item_name' => 'Paint Primer',
                    'lot_number' => 'LOT-P-01',
                    'qty' => 5,
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-BREAKDOWN-01')->first();
        $this->assertNotNull($report);

        // Assert that jml_input_wip was automatically calculated from WIP 1 (450) + WIP 2 (350) = 800
        $this->assertEquals(800, $report->jml_input_wip);

        // Assert that repairan was automatically calculated from Repairan 1 = 80
        $this->assertEquals(80, $report->repairan);

        // Verify part materials are properly stored
        $this->assertCount(3, $report->materials->where('type', 'part'));
        $this->assertCount(1, $report->materials->where('type', 'paint'));
    }

    public function test_item_paint_materials_ignored_when_process_prod_is_not_painting()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        $payload = [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Area Buffing',
            'shift' => '1',
            'process_prod' => 'Buffing',
            'status' => 'draft',
            'part_number' => 'PART-BUFFING-01',
            'part_name' => 'Side Mirror',
            'target_per_hour' => 100,

            // Mixed materials (both paint and part)
            'materials' => [
                [
                    'type' => 'part',
                    'item_name' => 'WIP 1',
                    'lot_number' => 'LOT-WIP-BUF',
                    'qty' => 300,
                    'uom' => 'Pcs',
                ],
                [
                    'type' => 'paint',
                    'item_name' => 'Paint Primer',
                    'lot_number' => 'LOT-P-ERR',
                    'visco' => '14s',
                    'mixing_ratio' => '1:1',
                    'qty' => 10,
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-BUFFING-01')->first();
        $this->assertNotNull($report);

        // Verify part materials are stored, but paint materials are ignored because process_prod is Buffing
        $this->assertCount(1, $report->materials->where('type', 'part'));
        $this->assertCount(0, $report->materials->where('type', 'paint'));
    }

    public function test_store_cleans_empty_material_rows_and_does_not_fail_validation()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        $payload = [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-EMPTY-MAT-ROW',
            'materials' => [
                // Valid material row
                [
                    'type' => 'paint',
                    'item_name' => 'Paint Primer',
                    'lot_number' => 'LOT-01',
                    'qty' => 5,
                ],
                // Blank dynamic row (e.g. operator added row then left empty)
                [
                    'type' => 'paint',
                    'item_name' => '',
                    'lot_number' => '',
                    'visco' => '',
                    'mixing_ratio' => '',
                    'qty' => '',
                    'uom' => '',
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-EMPTY-MAT-ROW')->first();
        $this->assertNotNull($report);
        $this->assertCount(1, $report->materials);
    }

    public function test_store_cleans_empty_trouble_rows_and_does_not_fail_validation()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        $payload = [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-EMPTY-TR-ROW',
            'troubles' => [
                // Blank trouble row
                [
                    'masalah' => '',
                    'penanganan' => '',
                    'loss_time_minutes' => '',
                    'loss_time' => '',
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('second-process-reports.index'));

        $report = SecondProcessReport::where('part_number', 'PART-EMPTY-TR-ROW')->first();
        $this->assertNotNull($report);
        $this->assertCount(0, $report->troubles);
    }

    public function test_store_validates_materials_with_missing_name_when_other_fields_are_filled()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        $payload = [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            'part_number' => 'PART-INVALID-MAT',
            'materials' => [
                // Qty entered but item_name is missing
                [
                    'type' => 'paint',
                    'item_name' => '',
                    'lot_number' => 'LOT-ERR',
                    'qty' => 10,
                ],
            ],
        ];

        $response = $this->post(route('second-process-reports.store'), $payload);
        $response->assertSessionHasErrors(['materials.0.item_name']);
    }

    public function test_create_form_view_renders_error_banner_and_tab_badges_when_validation_fails()
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actingAs($user);

        // Attempting store with missing part_number
        $response = $this->post(route('second-process-reports.store'), [
            'date' => now()->format('Y-m-d'),
            'unit_line' => 'Line 1',
            'shift' => '1',
            'process_prod' => 'Painting',
            // part_number is omitted
        ]);

        $response->assertSessionHasErrors(['part_number']);

        // Now follow redirect to create page with session errors
        $viewResponse = $this->get(route('second-process-reports.create'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('form-error-summary');
        $viewResponse->assertSee('Part Number wajib diisi.');
    }
}


