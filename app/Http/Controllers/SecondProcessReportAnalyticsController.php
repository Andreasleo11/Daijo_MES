<?php

namespace App\Http\Controllers;

use App\Models\SecondProcessReport;
use App\Models\SecondProcessNgRecord;
use App\Models\SecondProcessTrouble;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecondProcessReportAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');

        // Date range default: Start of current month to today
        $dateFrom = $request->input('date_from', $now->copy()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', $now->format('Y-m-d'));

        // Base query for report header filtering
        $baseQuery = SecondProcessReport::query()
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo);

        if ($request->filled('unit_line')) {
            $baseQuery->where('unit_line', $request->unit_line);
        }
        if ($request->filled('shift')) {
            $baseQuery->where('shift', $request->shift);
        }
        if ($request->filled('process_prod')) {
            $baseQuery->where('process_prod', $request->process_prod);
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }

        // CSV Export if requested
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($baseQuery, $dateFrom, $dateTo);
        }

        // 1. Summary KPIs
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_reports,
            COALESCE(SUM(target_per_hour * 8), 0) as total_target,
            COALESCE(SUM(jumlah_output), 0) as total_output,
            COALESCE(SUM(jumlah_ok), 0) as total_ok,
            COALESCE(SUM(jumlah_ng), 0) as total_ng,
            COALESCE(SUM(jml_input_wip), 0) as total_input_wip,
            COALESCE(SUM(repairan), 0) as total_repairan,
            COALESCE(SUM(jml_ng_lebur), 0) as total_scrap
        ')->first();

        $totalInput = (int) ($summary->total_input_wip + $summary->total_repairan);
        $yieldRate = $summary->total_output > 0
            ? round(($summary->total_ok / $summary->total_output) * 100, 2)
            : 0;

        $avgNgRate = $summary->total_output > 0
            ? round(($summary->total_ng / $summary->total_output) * 100, 2)
            : 0;

        $targetAchievementRate = $summary->total_target > 0
            ? round(($summary->total_output / $summary->total_target) * 100, 1)
            : null;

        $wipVariance = $totalInput - ((int) $summary->total_output + (int) $summary->total_scrap);

        $scrapRate = $totalInput > 0
            ? round(($summary->total_scrap / $totalInput) * 100, 2)
            : 0;

        // 2. Daily Output & NG Trend
        $dailyTrendRaw = (clone $baseQuery)
            ->select(DB::raw("date, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng, SUM(jumlah_output) as total_output"))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $dailyTrend = [
            'labels' => [],
            'ok' => [],
            'ng' => [],
            'ng_rate' => [],
        ];

        foreach ($dailyTrendRaw as $row) {
            $dailyTrend['labels'][] = Carbon::parse($row->date)->format('d M');
            $dailyTrend['ok'][] = (int) $row->total_ok;
            $dailyTrend['ng'][] = (int) $row->total_ng;
            $dailyTrend['ng_rate'][] = $row->total_output > 0
                ? round(($row->total_ng / $row->total_output) * 100, 2)
                : 0;
        }

        // 3. Output & NG by Line
        $byLineRaw = (clone $baseQuery)
            ->select(DB::raw("unit_line, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng, SUM(jumlah_output) as total_output"))
            ->groupBy('unit_line')
            ->orderByDesc('total_output')
            ->get();

        $byLine = [
            'labels' => [],
            'ok' => [],
            'ng' => [],
            'ng_rate' => [],
        ];

        foreach ($byLineRaw as $row) {
            $byLine['labels'][] = $row->unit_line ?? 'Unknown';
            $byLine['ok'][] = (int) $row->total_ok;
            $byLine['ng'][] = (int) $row->total_ng;
            $byLine['ng_rate'][] = $row->total_output > 0
                ? round(($row->total_ng / $row->total_output) * 100, 2)
                : 0;
        }

        // Subquery for child table aggregations (avoids memory bloat and MySQL parameter limits)
        $reportIdsSubquery = (clone $baseQuery)->select('id');

        // 4. Top NG Defects / Categories (Pareto Chart Data)
        $topNgRaw = SecondProcessNgRecord::whereIn('report_id', $reportIdsSubquery)
            ->select(DB::raw("COALESCE(NULLIF(ng_name, ''), ng_category, 'Uncategorized') as ng_label, SUM(total_ng) as total"))
            ->groupBy('ng_label')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $totalNgSum = $topNgRaw->sum('total');
        $topNg = [
            'labels' => [],
            'values' => [],
            'cumulative_pct' => [],
        ];

        $runningSum = 0;
        foreach ($topNgRaw as $row) {
            $runningSum += $row->total;
            $topNg['labels'][] = $row->ng_label;
            $topNg['values'][] = (int) $row->total;
            $topNg['cumulative_pct'][] = $totalNgSum > 0 ? round(($runningSum / $totalNgSum) * 100, 1) : 0;
        }

        // 5. Output by Shift
        $byShiftRaw = (clone $baseQuery)
            ->select(DB::raw("shift, SUM(jumlah_output) as total_output, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng"))
            ->groupBy('shift')
            ->orderBy('shift', 'asc')
            ->get();

        $byShift = [
            'labels' => [],
            'output' => [],
            'ok' => [],
            'ng' => [],
        ];

        foreach ($byShiftRaw as $row) {
            $byShift['labels'][] = 'Shift ' . $row->shift;
            $byShift['output'][] = (int) $row->total_output;
            $byShift['ok'][] = (int) $row->total_ok;
            $byShift['ng'][] = (int) $row->total_ng;
        }

        // 6. Downtime by Category & Total Loss Time
        $totalDowntimeMinutes = (int) SecondProcessTrouble::whereIn('report_id', $reportIdsSubquery)->sum('loss_time_minutes');
        $totalDowntimeHours = round($totalDowntimeMinutes / 60, 1);

        $downtimeRaw = SecondProcessTrouble::whereIn('report_id', $reportIdsSubquery)
            ->select(DB::raw("COALESCE(NULLIF(category, ''), NULLIF(penyebab, ''), 'Other') as cat_name, SUM(loss_time_minutes) as total_minutes"))
            ->groupBy('cat_name')
            ->orderByDesc('total_minutes')
            ->limit(10)
            ->get();

        $downtime = [
            'labels' => [],
            'minutes' => [],
        ];

        foreach ($downtimeRaw as $row) {
            $downtime['labels'][] = $row->cat_name;
            $downtime['minutes'][] = (int) $row->total_minutes;
        }

        // 7a. Top 5 Products by Output Volume
        $topProductsRaw = (clone $baseQuery)
            ->select(DB::raw("part_number, part_name, customer, SUM(jumlah_output) as total_output, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng"))
            ->groupBy('part_number', 'part_name', 'customer')
            ->orderByDesc('total_output')
            ->limit(5)
            ->get();

        // 7b. Top 5 Products by NG Defects (High Risk / Quality Issues)
        $topDefectProductsRaw = (clone $baseQuery)
            ->select(DB::raw("part_number, part_name, customer, SUM(jumlah_output) as total_output, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng, SUM(jml_ng_lebur) as total_scrap"))
            ->groupBy('part_number', 'part_name', 'customer')
            ->having('total_ng', '>', 0)
            ->orderByDesc('total_ng')
            ->limit(5)
            ->get();

        // 8. Top Downtime Incidents & Countermeasures
        $topTroubles = SecondProcessTrouble::whereIn('report_id', $reportIdsSubquery)
            ->where(function ($q) {
                $q->whereNotNull('masalah')->where('masalah', '!=', '')
                  ->orWhereNotNull('penyebab')->where('penyebab', '!=', '')
                  ->orWhere('loss_time_minutes', '>', 0);
            })
            ->with(['report' => function ($q) {
                $q->select('id', 'date', 'unit_line', 'shift', 'part_number', 'part_name');
            }])
            ->orderByDesc('loss_time_minutes')
            ->limit(8)
            ->get();

        // Target NG Rate from configuration
        $targetNgRate = (float) config('mes.sp_target_ng_rate', 2.0);

        // Dropdown selection lists
        $lines = array_values(config('mes.sp_lines', []));
        $processes = config('mes.sp_processes', ['Painting', 'Buffing', 'Amplas', 'Treatment', 'Packing', 'Rework', 'Repair', 'Assy']);

        return view('second_process.report_analytics', compact(
            'summary',
            'avgNgRate',
            'yieldRate',
            'targetAchievementRate',
            'wipVariance',
            'scrapRate',
            'targetNgRate',
            'totalInput',
            'totalDowntimeMinutes',
            'totalDowntimeHours',
            'dailyTrend',
            'byLine',
            'topNg',
            'byShift',
            'downtime',
            'topProductsRaw',
            'topDefectProductsRaw',
            'topTroubles',
            'dateFrom',
            'dateTo',
            'lines',
            'processes'
        ));
    }

    /**
     * Stream filtered Second Process reports data to CSV.
     */
    protected function exportCsv($query, string $dateFrom, string $dateTo)
    {
        $filename = 'second-process-analytics-' . $dateFrom . '-to-' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Column Headers
            fputcsv($handle, [
                'ID',
                'Date',
                'Unit / Line',
                'Shift',
                'Process',
                'Status',
                'Part Number',
                'Part Name',
                'Customer',
                'Target / Hour',
                'Shift Target',
                'Input WIP',
                'Repairan',
                'Total Input',
                'Output Qty',
                'OK Qty',
                'NG Qty',
                'Yield (%)',
                'NG Rate (%)',
                'Scrap (Lebur)',
                'WIP Variance',
            ]);

            $query->orderBy('date', 'desc')
                ->orderBy('unit_line', 'asc')
                ->chunk(200, function ($reports) use ($handle) {
                    foreach ($reports as $r) {
                        $shiftTarget = ($r->target_per_hour ?? 0) * 8;
                        $totIn = (int) ($r->jml_input_wip + $r->repairan);
                        $totOut = (int) $r->jumlah_output;
                        $totScrap = (int) $r->jml_ng_lebur;
                        $wipVar = $totIn - ($totOut + $totScrap);
                        $yield = $totOut > 0 ? round(($r->jumlah_ok / $totOut) * 100, 2) : 0;
                        $ngRate = $totOut > 0 ? round(($r->jumlah_ng / $totOut) * 100, 2) : 0;

                        fputcsv($handle, [
                            $r->id,
                            $r->date,
                            $this->sanitizeCsvCell($r->unit_line),
                            $r->shift,
                            $this->sanitizeCsvCell($r->process_prod),
                            $this->sanitizeCsvCell($r->status),
                            $this->sanitizeCsvCell($r->part_number),
                            $this->sanitizeCsvCell($r->part_name),
                            $this->sanitizeCsvCell($r->customer),
                            $r->target_per_hour,
                            $shiftTarget,
                            $r->jml_input_wip,
                            $r->repairan,
                            $totIn,
                            $totOut,
                            $r->jumlah_ok,
                            $r->jumlah_ng,
                            $yield . '%',
                            $ngRate . '%',
                            $totScrap,
                            $wipVar,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Prevent CSV Formula Injection by prefixing formula characters.
     */
    protected function sanitizeCsvCell($value): string
    {
        $str = (string) $value;
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        return $str;
    }
}
