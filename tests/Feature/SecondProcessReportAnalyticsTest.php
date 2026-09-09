<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SecondProcessNgRecord;
use App\Models\SecondProcessReport;
use App\Models\SecondProcessTrouble;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecondProcessReportAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRole = Role::create(['name' => 'ADMIN']);
        $this->user = User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    public function test_analytics_requires_authentication(): void
    {
        $response = $this->get(route('second-process.report-analytics'));
        $response->assertRedirect('/login');
    }

    public function test_analytics_page_renders_with_kpis_and_filters(): void
    {
        $today = now()->format('Y-m-d');

        // Create Report 1
        $report1 = SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'PART-001',
            'part_name' => 'Side Cover Left',
            'customer' => 'Customer A',
            'jml_input_wip' => 1000,
            'repairan' => 50,
            'jumlah_output' => 950,
            'jumlah_ok' => 900,
            'jumlah_ng' => 50,
            'jml_ng_lebur' => 10,
        ]);

        // Create Report 2
        $report2 = SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 2',
            'shift' => 2,
            'process_prod' => 'Painting',
            'status' => 'pqc_approved',
            'part_number' => 'PART-002',
            'part_name' => 'Side Cover Right',
            'customer' => 'Customer B',
            'jml_input_wip' => 500,
            'repairan' => 20,
            'jumlah_output' => 500,
            'jumlah_ok' => 480,
            'jumlah_ng' => 20,
            'jml_ng_lebur' => 5,
        ]);

        // Add troubles to Report 1
        SecondProcessTrouble::create([
            'report_id' => $report1->id,
            'category' => 'Mesin',
            'penyebab' => 'Mesin',
            'masalah' => 'Nozzle clogged',
            'loss_time_minutes' => 30,
            'penanganan' => 'Cleaned nozzle tip',
        ]);

        $response = $this->actingAs($this->user)->get(route('second-process.report-analytics', [
            'date_from' => $today,
            'date_to' => $today,
        ]));

        $response->assertOk();
        $response->assertViewIs('second_process.report_analytics');

        // Verify KPI totals passed to view
        $summary = $response->viewData('summary');
        $this->assertEquals(2, $summary->total_reports);
        $this->assertEquals(1450, $summary->total_output);
        $this->assertEquals(1380, $summary->total_ok);
        $this->assertEquals(70, $summary->total_ng);
        $this->assertEquals(1500, $summary->total_input_wip);
        $this->assertEquals(70, $summary->total_repairan);
        $this->assertEquals(15, $summary->total_scrap);

        $this->assertEquals(1570, $response->viewData('totalInput'));
        $this->assertEquals(95.17, $response->viewData('yieldRate')); // 1380 / 1450 * 100
        $this->assertEquals(4.83, $response->viewData('avgNgRate')); // 70 / 1450 * 100
        $this->assertEquals(105, $response->viewData('wipVariance')); // 1570 - (1450 + 15)
        $this->assertEquals(0.96, $response->viewData('scrapRate')); // 15 / 1570 * 100
        $this->assertEquals(2.0, $response->viewData('targetNgRate'));
        $this->assertNull($response->viewData('targetAchievementRate'));
        $this->assertEquals(30, $response->viewData('totalDowntimeMinutes'));
        $this->assertEquals(0.5, $response->viewData('totalDowntimeHours'));

        // Verify HTML contents
        $response->assertSee('Total Input WIP');
        $response->assertSee('Total Repairan');
        $response->assertSee('Total Scrap (Lebur)');
        $response->assertSee('WIP Reconciliation');
        $response->assertSee('Export CSV');
        $response->assertSee('Print Report');
        $response->assertSee('Top Defects / High Risk');
        $response->assertSee('Top Downtime Incidents');
        $response->assertSee('Nozzle clogged');
        $response->assertSee('Cleaned nozzle tip');
    }

    public function test_analytics_filters_by_line_shift_process_and_status(): void
    {
        $today = now()->format('Y-m-d');

        // Target report matching all criteria
        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'MATCH-001',
            'part_name' => 'Matching Part',
            'customer' => 'Customer A',
            'jml_input_wip' => 600,
            'repairan' => 10,
            'jumlah_output' => 600,
            'jumlah_ok' => 590,
            'jumlah_ng' => 10,
            'jml_ng_lebur' => 2,
        ]);

        // Non-matching reports
        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 2',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'DIFF-LINE',
            'part_name' => 'Different Line',
            'customer' => 'Customer A',
            'jumlah_output' => 200,
            'jumlah_ok' => 200,
            'jumlah_ng' => 0,
        ]);

        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 2,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'DIFF-SHIFT',
            'part_name' => 'Different Shift',
            'customer' => 'Customer A',
            'jumlah_output' => 300,
            'jumlah_ok' => 300,
            'jumlah_ng' => 0,
        ]);

        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Buffing',
            'status' => 'submitted',
            'part_number' => 'DIFF-PROC',
            'part_name' => 'Different Process',
            'customer' => 'Customer A',
            'jumlah_output' => 150,
            'jumlah_ok' => 150,
            'jumlah_ng' => 0,
        ]);

        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'draft',
            'part_number' => 'DIFF-STATUS',
            'part_name' => 'Draft Status',
            'customer' => 'Customer A',
            'jumlah_output' => 100,
            'jumlah_ok' => 100,
            'jumlah_ng' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('second-process.report-analytics', [
            'date_from' => $today,
            'date_to' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
        ]));

        $response->assertOk();
        $summary = $response->viewData('summary');
        $this->assertEquals(1, $summary->total_reports);
        $this->assertEquals(600, $summary->total_output);
        $this->assertEquals(590, $summary->total_ok);
        $this->assertEquals(10, $summary->total_ng);

        $response->assertSee('MATCH-001');
        $response->assertDontSee('DIFF-LINE');
        $response->assertDontSee('DIFF-SHIFT');
        $response->assertDontSee('DIFF-PROC');
        $response->assertDontSee('DIFF-STATUS');
    }

    public function test_analytics_aggregates_downtime_with_category_and_penyebab_fallback(): void
    {
        $today = now()->format('Y-m-d');

        $report = SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'acknowledged',
            'part_number' => 'DOWN-001',
            'part_name' => 'Downtime Test Part',
            'customer' => 'Customer A',
            'jumlah_output' => 500,
            'jumlah_ok' => 450,
            'jumlah_ng' => 50,
        ]);

        // Trouble 1: standard category (category set, penyebab set)
        SecondProcessTrouble::create([
            'report_id' => $report->id,
            'category' => 'Mesin',
            'penyebab' => 'Mesin',
            'masalah' => 'Sensor conveyor fault',
            'loss_time_minutes' => 45,
            'penanganan' => 'Sensor alignment & calibration',
        ]);

        // Trouble 2: legacy record where category is null/empty, but penyebab holds the legacy category 'Man'
        SecondProcessTrouble::create([
            'report_id' => $report->id,
            'category' => null,
            'penyebab' => 'Man',
            'masalah' => 'New operator setup',
            'loss_time_minutes' => 30,
            'penanganan' => 'Supervisor guidance provided',
        ]);

        // Trouble 3: neither category nor valid penyebab (empty strings) -> fallback to 'Other'
        SecondProcessTrouble::create([
            'report_id' => $report->id,
            'category' => '',
            'penyebab' => '',
            'masalah' => 'Unexpected stoppage',
            'loss_time_minutes' => 15,
            'penanganan' => 'Line restarted',
        ]);

        $response = $this->actingAs($this->user)->get(route('second-process.report-analytics', [
            'date_from' => $today,
            'date_to' => $today,
        ]));

        $response->assertOk();
        $this->assertEquals(90, $response->viewData('totalDowntimeMinutes'));
        $this->assertEquals(1.5, $response->viewData('totalDowntimeHours'));

        $downtime = $response->viewData('downtime');
        $this->assertContains('Mesin', $downtime['labels']);
        $this->assertContains('Man', $downtime['labels']);
        $this->assertContains('Other', $downtime['labels']);

        $topTroubles = $response->viewData('topTroubles');
        $this->assertCount(3, $topTroubles);
        $this->assertEquals('Sensor conveyor fault', $topTroubles->first()->masalah);
        $this->assertEquals(45, $topTroubles->first()->loss_time_minutes);

        $response->assertSee('Sensor conveyor fault');
        $response->assertSee('Sensor alignment &amp; calibration', false);
        $response->assertSee('Supervisor guidance provided');
    }

    public function test_analytics_calculates_target_achievement_and_wip_variance(): void
    {
        $today = now()->format('Y-m-d');

        // Target: 100/hr * 8 hrs = 800 units planned
        // Output: 720 units (Achievement = 720 / 800 = 90.0%)
        // Input: 800 WIP + 50 Repairan = 850 total fed
        // Output: 720 (700 OK + 20 NG), Scrap: 10
        // Variance: 850 - (720 + 10) = 120 units
        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'ACHIEVE-01',
            'part_name' => 'Achievement Test Part',
            'customer' => 'Customer Plan',
            'target_per_hour' => 100,
            'jml_input_wip' => 800,
            'repairan' => 50,
            'jumlah_output' => 720,
            'jumlah_ok' => 700,
            'jumlah_ng' => 20,
            'jml_ng_lebur' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('second-process.report-analytics', [
            'date_from' => $today,
            'date_to' => $today,
        ]));

        $response->assertOk();
        $summary = $response->viewData('summary');
        $this->assertEquals(800, $summary->total_target);
        $this->assertEquals(90.0, $response->viewData('targetAchievementRate'));
        $this->assertEquals(120, $response->viewData('wipVariance'));
        $this->assertEquals(1.18, $response->viewData('scrapRate')); // 10 / 850 * 100 = 1.18%

        $topDefects = $response->viewData('topDefectProductsRaw');
        $this->assertCount(1, $topDefects);
        $this->assertEquals('ACHIEVE-01', $topDefects->first()->part_number);
        $this->assertEquals(20, $topDefects->first()->total_ng);

        $response->assertSee('90% Plan');
        $response->assertSee('+120');
        $response->assertSee('In-Line WIP');
    }

    public function test_analytics_can_export_csv(): void
    {
        $today = now()->format('Y-m-d');

        SecondProcessReport::create([
            'date' => $today,
            'unit_line' => 'Line 1',
            'shift' => 1,
            'process_prod' => 'Painting',
            'status' => 'submitted',
            'part_number' => 'EXPORT-001',
            'part_name' => 'Export Part',
            'customer' => 'Customer CSV',
            'target_per_hour' => 50,
            'jml_input_wip' => 400,
            'repairan' => 20,
            'jumlah_output' => 380,
            'jumlah_ok' => 370,
            'jumlah_ng' => 10,
            'jml_ng_lebur' => 5,
        ]);

        $response = $this->actingAs($this->user)->get(route('second-process.report-analytics', [
            'date_from' => $today,
            'date_to' => $today,
            'export' => 'csv',
        ]));

        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment; filename='));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Part Number', $content);
        $this->assertStringContainsString('Shift Target', $content);
        $this->assertStringContainsString('WIP Variance', $content);
        $this->assertStringContainsString('EXPORT-001', $content);
        $this->assertStringContainsString('Export Part', $content);
        $this->assertStringContainsString('Customer CSV', $content);
    }
}
