<?php

namespace App\Http\Controllers;

use App\Models\FirstPieceInspection;
use App\Models\SpDowntimeEntry;
use App\Models\SpProductionEntry;
use App\Models\SpProductionSession;
use App\Models\SpRejectEntry;
use App\Models\SpSessionManpower;
use App\Models\SpWorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SecondProcessDashboardController extends Controller
{
    /**
     * Display the real-time floor dashboard.
     */
    public function index(Request $request)
    {
        // 1. Determine active date and shift
        $now = Carbon::now('Asia/Jakarta');

        $date = $request->input('date', $now->format('Y-m-d'));
        $shift = (int) $request->input('shift', $this->getCurrentShift($now));

        // Define all valid lines from config
        $lines = array_values(config('mes.sp_lines', []));

        // 2. Fetch sessions for this date and shift
        $sessions = SpProductionSession::with(['workOrder', 'productionEntries', 'manpowerEntries'])
            ->whereDate('started_at', $date)
            ->where('shift', $shift)
            ->get();

        $reports = $sessions->keyBy('unit_line');

        // 3. Fetch planned/active Work Orders for today or in progress
        $activeWorkOrders = SpWorkOrder::with(['sessions'])
            ->where(function ($query) use ($date) {
                $query->whereDate('planned_date', $date)
                    ->where(function($query) {
                        $query->orWhereIn('status', ['planned', 'draft', 'approved', 'in_progress']);
                    });
            })
            ->orderBy('id', 'desc')
            ->get();

        $workOrdersByLine = $activeWorkOrders->keyBy('unit_line');

        // 4. Fetch First Piece Inspections for this date
        $firstPieceInspections = FirstPieceInspection::whereDate('date', $date)->get();
        $firstPieceMap = $firstPieceInspections->keyBy('part_number');

        // 5. Calculate real-time Shift KPIs
        $runningSessionsCount = $sessions->where('status', 'running')->count();
        $pendingWoCount = $activeWorkOrders->whereIn('status', ['planned', 'draft', 'approved'])->count();
        $totalShiftGood = $sessions->sum('total_good');
        $totalShiftReject = $sessions->sum('total_reject');
        $totalShiftTotal = $totalShiftGood + $totalShiftReject;
        $overallNgRate = $totalShiftTotal > 0 ? round(($totalShiftReject / $totalShiftTotal) * 100, 1) : 0;
        $approvedFirstPieceCount = $firstPieceInspections->filter(fn($fp) => $fp->isApproved())->count();

        return view('second_process.dashboard', compact(
            'lines',
            'reports',
            'workOrdersByLine',
            'activeWorkOrders',
            'firstPieceMap',
            'date',
            'shift',
            'runningSessionsCount',
            'pendingWoCount',
            'totalShiftGood',
            'totalShiftReject',
            'overallNgRate',
            'approvedFirstPieceCount'
        ));
    }

    /**
     * Display a dedicated single-line shop floor dashboard.
     */
    public function lineDashboard(Request $request, string $line)
    {
        $now = Carbon::now('Asia/Jakarta');
        $date = $request->input('date', $now->format('Y-m-d'));
        $shift = (int) $request->input('shift', $this->getCurrentShift($now));

        // Resolve slug -> display name from config (e.g. 'amplas' -> 'Area Amplas/Treatment')
        $spLines = config('mes.sp_lines', []);
        if (isset($spLines[$line])) {
            $line = $spLines[$line];
        } else {
            foreach ($spLines as $slug => $displayName) {
                if (strcasecmp($slug, $line) === 0 || strcasecmp($displayName, $line) === 0) {
                    $line = $displayName;
                    break;
                }
            }
        }

        $defaultLines = collect($spLines)->values();

        $dbLines = SpProductionSession::select('unit_line')
            ->distinct()->pluck('unit_line')
            ->merge(SpWorkOrder::select('unit_line')->distinct()->pluck('unit_line'));

        $knownLines = $defaultLines->merge($dbLines)->filter()->unique()->sort()->values();

        // 2. Fetch sessions for this line/date/shift
        $sessions = SpProductionSession::with([
                'workOrder', 'operator', 'productionEntries', 'rejectEntries',
                'reworkEntries', 'downtimeEntries', 'inputEntries', 'manpowerEntries',
            ])
            ->where('unit_line', $line)
            ->whereDate('started_at', $date)
            ->where('shift', $shift)
            ->get();

        $sessionIds = $sessions->pluck('id');

        // === KPIs ===
        $totalGood = $sessions->sum('total_good');
        $totalReject = $sessions->sum('total_reject');
        $totalInput = $sessions->sum('total_input');
        $totalOutput = $totalGood + $totalReject;
        $yieldPct = $totalOutput > 0 ? round(($totalGood / $totalOutput) * 100, 1) : 0;
        $ngRate = $totalOutput > 0 ? round(($totalReject / $totalOutput) * 100, 1) : 0;
        $totalDowntimeMinutes = $sessions->flatMap->downtimeEntries->sum('duration_minutes');
        $totalReworkIn = $sessions->sum('total_rework_in');
        $totalReworkRecovered = $sessions->sum('total_rework_recovered');
        $totalScrap = $sessions->sum('total_scrap');

        $activeSession = $sessions->where('status', 'running')->first();
        $targetQty = $activeSession?->workOrder?->target_qty ?? $sessions->first()?->workOrder?->target_qty ?? 0;

        // === Hourly Output (grouped by H:00) ===
        $hourlyRaw = SpProductionEntry::whereIn('session_id', $sessionIds)
            ->get()
            ->groupBy(fn($e) => $e->recorded_at ? $e->recorded_at->format('H:00') : '00:00');

        $hourlyOutput = [];
        foreach ($hourlyRaw as $hour => $entries) {
            $hourlyOutput[$hour] = [
                'good' => $entries->sum('good_qty'),
                'reject' => $entries->sum('reject_qty'),
            ];
        }
        ksort($hourlyOutput);

        // === Defect Pareto ===
        $defectPareto = SpRejectEntry::whereIn('session_id', $sessionIds)
            ->selectRaw('defect_type, SUM(quantity) as total')
            ->groupBy('defect_type')
            ->orderByDesc('total')
            ->get();

        // === Downtime Breakdown ===
        $downtimeBreakdown = SpDowntimeEntry::whereIn('session_id', $sessionIds)
            ->selectRaw('reason, SUM(duration_minutes) as total_minutes, COUNT(*) as occurrences')
            ->groupBy('reason')
            ->orderByDesc('total_minutes')
            ->get();

        // === Downtime Log (individual entries) ===
        $downtimeLog = SpDowntimeEntry::whereIn('session_id', $sessionIds)
            ->orderByDesc('start_time')
            ->get();

        // === Manpower Roster ===
        $manpower = SpSessionManpower::whereIn('session_id', $sessionIds)->get();

        // === Session History (last 10 for this line) ===
        $sessionHistory = SpProductionSession::with(['workOrder', 'operator'])
            ->where('unit_line', $line)
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        // === Active WO + First Piece ===
        $activeWo = SpWorkOrder::where('unit_line', $line)
            ->whereIn('status', ['draft', 'approved', 'in_progress'])
            ->orderByDesc('id')
            ->first();

        $firstPiece = null;
        if ($activeWo) {
            $firstPiece = FirstPieceInspection::where('part_number', $activeWo->part_number)
                ->whereDate('date', $date)
                ->orderByDesc('id')
                ->first();
        }

        return view('second_process.line_dashboard', compact(
            'line', 'date', 'shift', 'knownLines', 'sessions',
            'totalGood', 'totalReject', 'totalInput', 'yieldPct', 'ngRate',
            'totalDowntimeMinutes', 'totalReworkIn', 'totalReworkRecovered', 'totalScrap',
            'activeSession', 'targetQty',
            'hourlyOutput', 'defectPareto', 'downtimeBreakdown', 'downtimeLog',
            'manpower', 'sessionHistory', 'activeWo', 'firstPiece'
        ));
    }

    /**
     * Determine current shift based on configured schedule.
     */
    private function getCurrentShift(Carbon $now)
    {
        $shifts = config('mes.shifts', []);
        $currentTime = $now->format('H:i');

        foreach ($shifts as $shiftId => $schedule) {
            $start = $schedule['start'];
            $end = $schedule['end'];

            if ($start > $end) {
                // Crosses midnight (e.g. 23:30 to 07:30)
                if ($currentTime >= $start || $currentTime <= $end) {
                    return $shiftId;
                }
            } else {
                if ($currentTime >= $start && $currentTime <= $end) {
                    return $shiftId;
                }
            }
        }

        return 1; // Default to 1 if outside any schedule
    }
}
