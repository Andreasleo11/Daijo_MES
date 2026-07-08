<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\SecondProcessReport;
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
     * Test storing a report with full nested relationship payload.
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
                [
                    'type' => 'part',
                    'item_name' => 'WIP 1',
                    'lot_number' => 'LOT-WIP-01',
                    'qty' => 800,
                ],
            ],

            // Manpower
            'manpower' => [
                [
                    'role' => 'loading',
                    'no' => 1,
                    'name' => 'John Doe',
                ],
                [
                    'role' => 'sprayer',
                    'no' => 1,
                    'name' => 'Jane Smith',
                ],
            ],

            // Hourly OK logs
            'hourly' => [
                1 => [
                    'hour_ke' => 1,
                    'ok_qty' => 90,
                    'acumulasi_qty' => 90,
                ],
                2 => [
                    'hour_ke' => 2,
                    'ok_qty' => 95,
                    'acumulasi_qty' => 185,
                ],
            ],

            // NG logs
            'ngs' => [
                [
                    'ng_name' => 'SCRATCH',
                    'ng_category' => 'ng_proses',
                    'total_ng' => 5,
                    'hour_1' => 2,
                    'hour_2' => 3,
                    'ng_input_item' => 'Dust defect',
                    'ng_input_qty' => 5,
                ]
            ],

            // Troubles
            'troubles' => [
                [
                    'penyebab' => 'Mesin',
                    'category' => 'Mesin',
                    'masalah' => 'Conveyor belt slip',
                    'penanganan' => 'Tightened belt tensioner',
                    'loss_time_minutes' => 15,
                    'loss_time' => '15 mins',
                ]
            ],

            // Schedule
            'next_production_schedule' => [
                'Production Plan B',
                'Color swap to metallic black',
            ],

            // Approvals
            'created_by_name' => 'Checker Raymond',
        ];

        $response = $this->actingAs($user)->post(route('second-process-reports.store'), $payload);

        $response->assertRedirect();

        // Assert database records
        $this->assertDatabaseHas('second_process_reports', [
            'part_number' => 'PART-XYZ-01',
            'status' => 'draft',
            'created_by_name' => $user->name,
        ]);

        $report = SecondProcessReport::first();
        $this->assertNull($report->created_by_signed_at); // Auto timestamped checker is null on draft

        $this->assertDatabaseHas('second_process_materials', [
            'report_id' => $report->id,
            'item_name' => 'Paint Primer',
            'lot_number' => 'LOT-PRM-99',
            'mixing_ratio' => '1:1.5',
        ]);

        $this->assertDatabaseHas('second_process_manpowers', [
            'report_id' => $report->id,
            'role' => 'loading',
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('second_process_hourly_productions', [
            'report_id' => $report->id,
            'hour_ke' => 1,
            'ok_qty' => 90,
        ]);

        $this->assertDatabaseHas('second_process_ng_records', [
            'report_id' => $report->id,
            'ng_name' => 'SCRATCH',
            'ng_category' => 'ng_proses',
            'total_ng' => 5,
        ]);

        $this->assertDatabaseHas('second_process_troubles', [
            'report_id' => $report->id,
            'category' => 'Mesin',
            'masalah' => 'Conveyor belt slip',
            'loss_time_minutes' => 15,
        ]);
    }

    /**
     * Test updating a report and its signature auto-timestamp logic.
     */
    public function test_can_update_report_in_draft_status(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id]);

        // 1. Create a draft report
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
            'created_by_name' => 'Raymond',
            'created_by_signed_at' => null,
        ]);

        // 2. Submit update keeping status as draft
        $payload = [
            'date' => '2026-07-07',
            'unit_line' => 'Painting Line A',
            'shift' => '1',
            'process_prod' => 'Painting',
            'status' => 'draft',
            'part_number' => 'PART-XYZ-01',
            'part_name' => 'Bumper Cover Updated',
            'model' => 'Sedan 2026',
            'customer' => 'Toyota Corp',
        ];

        $response = $this->actingAs($user)->put(route('second-process-reports.update', $report->id), $payload);

        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('Bumper Cover Updated', $report->part_name);
        $this->assertEquals('draft', $report->status);
    }

    /**
     * Test the digital signature workflow transitions.
     */
    public function test_digital_signature_approval_and_rejection_workflow(): void
    {
        $user = User::factory()->create(['role_id' => $this->adminRole->id, 'name' => 'Approver Admin']);

        // 1. Create a draft report
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

        // 2. Sign as Checker (Submit report)
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'checker']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('submitted', $report->status);
        $this->assertEquals('Approver Admin', $report->created_by_name);
        $this->assertNotNull($report->created_by_signed_at);

        // Operator should not be able to edit submitted report anymore
        $editResponse = $this->actingAs($user)->get(route('second-process-reports.edit', $report->id));
        $editResponse->assertRedirect();

        $updateResponse = $this->actingAs($user)->put(route('second-process-reports.update', $report->id), [
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
        $updateResponse->assertRedirect(); // Fails edit, returns to show with error

        // 3. Sign as PQC
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'pqc']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('pqc_approved', $report->status);
        $this->assertEquals('Approver Admin', $report->pqc_name);
        $this->assertNotNull($report->pqc_signed_at);

        // 4. Sign as Leader
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'leader']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('leader_approved', $report->status);
        $this->assertEquals('Approver Admin', $report->leader_name);
        $this->assertNotNull($report->leader_signed_at);

        // 5. Sign as Acknowledged (Supervisor)
        $response = $this->actingAs($user)->post(route('second-process-reports.sign', [$report->id, 'acknowledged']));
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('acknowledged', $report->status);
        $this->assertEquals('Approver Admin', $report->acknowledged_by_name);
        $this->assertNotNull($report->acknowledged_signed_at);

        // 6. Test Rejection resets to draft and clears signatures
        $response = $this->actingAs($user)->post(route('second-process-reports.reject', $report->id), [
            'rejection_reason' => 'NG quantity mismatch on hour 5'
        ]);
        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('draft', $report->status);
        $this->assertNull($report->created_by_signed_at);
        $this->assertNull($report->pqc_name);
        $this->assertNull($report->pqc_signed_at);
        $this->assertNull($report->leader_name);
        $this->assertNull($report->leader_signed_at);
        $this->assertNull($report->acknowledged_by_name);
        $this->assertNull($report->acknowledged_signed_at);
        $this->assertStringContainsString('Rejected by Approver Admin: NG quantity mismatch on hour 5', $report->ng_remarks);
    }
}
