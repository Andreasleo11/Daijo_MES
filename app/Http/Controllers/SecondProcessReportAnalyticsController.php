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

        // 1. Summary KPIs
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_reports,
            COALESCE(SUM(jumlah_output), 0) as total_output,
            COALESCE(SUM(jumlah_ok), 0) as total_ok,
            COALESCE(SUM(jumlah_ng), 0) as total_ng
        ')->first();

        $avgNgRate = $summary->total_output > 0
            ? round(($summary->total_ng / $summary->total_output) * 100, 2)
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

        // Fetch matched report IDs for child table aggregations
        $reportIds = (clone $baseQuery)->pluck('id');

        // 4. Top NG Defects / Categories (Pareto Chart Data)
        $topNgRaw = SecondProcessNgRecord::whereIn('report_id', $reportIds)
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

        // 6. Downtime by Category
        $downtimeRaw = SecondProcessTrouble::whereIn('report_id', $reportIds)
            ->select(DB::raw("COALESCE(NULLIF(category, ''), 'Other') as cat_name, SUM(loss_time_minutes) as total_minutes"))
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

        // 7. Top 5 Products by Output Volume
        $topProductsRaw = (clone $baseQuery)
            ->select(DB::raw("part_number, part_name, customer, SUM(jumlah_output) as total_output, SUM(jumlah_ok) as total_ok, SUM(jumlah_ng) as total_ng"))
            ->groupBy('part_number', 'part_name', 'customer')
            ->orderByDesc('total_output')
            ->limit(5)
            ->get();

        // Dropdown selection lists
        $lines = ['Line A', 'Line B', 'Line C', 'Line D', 'Area Buffing', 'Area Amplas/Treatment', 'Area Packing', 'Area Assy'];
        $processes = ['Painting', 'Buffing', 'Amplas', 'Treatment', 'Packing', 'Rework', 'Repair'];

        return view('second_process.report_analytics', compact(
            'summary',
            'avgNgRate',
            'dailyTrend',
            'byLine',
            'topNg',
            'byShift',
            'downtime',
            'topProductsRaw',
            'dateFrom',
            'dateTo',
            'lines',
            'processes'
        ));
    }
}
