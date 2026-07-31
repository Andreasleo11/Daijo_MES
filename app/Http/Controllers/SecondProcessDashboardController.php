<?php

namespace App\Http\Controllers;

use App\Models\FirstPieceInspection;
use App\Models\SpProductionSession;
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

        // Define all valid lines
        $lines = [
            'Line A',
            'Line B',
            'Line C',
            'Line D',
            'Area Buffing',
            'Area Amplas/Treatment',
            'Area Packing',
            'Area Assy',
        ];

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
                    ->orWhereIn('status', ['draft', 'approved', 'in_progress']);
            })
            ->orderBy('id', 'desc')
            ->get();

        $workOrdersByLine = $activeWorkOrders->keyBy('unit_line');

        // 4. Fetch First Piece Inspections for this date
        $firstPieceInspections = FirstPieceInspection::whereDate('date', $date)->get();
        $firstPieceMap = $firstPieceInspections->keyBy('part_number');

        // 5. Calculate real-time Shift KPIs
        $runningSessionsCount = $sessions->where('status', 'running')->count();
        $pendingWoCount = $activeWorkOrders->whereIn('status', ['draft', 'approved'])->count();
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
