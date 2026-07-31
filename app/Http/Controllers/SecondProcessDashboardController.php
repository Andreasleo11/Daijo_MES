<?php

namespace App\Http\Controllers;

use App\Models\SpProductionSession;
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
        $shift = $request->input('shift', $this->getCurrentShift($now));
        
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

        // 2. Fetch running reports from SpProductionSession for this date and shift
        $reports = SpProductionSession::with(['workOrder'])
            ->whereDate('started_at', $date)
            ->where('shift', $shift)
            ->whereIn('status', ['running', 'completed'])
            ->get()
            ->keyBy('unit_line');

        return view('second_process.dashboard', compact('lines', 'reports', 'date', 'shift'));
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
