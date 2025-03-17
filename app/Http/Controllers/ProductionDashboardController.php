<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionReport;
use App\Models\ProductionScannedData;
use App\Models\SpkMaster;
use App\Models\User;
use App\Models\MachineJob;
use App\Models\DailyItemCode;
use App\Models\MouldChangeLog; // Make sure the model is imported
use Carbon\Carbon;

class ProductionDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get the selected date from the request (default to today if not provided)
        $selectedDate = $request->input('date', Carbon::now()->toDateString());
    
        $machineName = $request->input('machine_name', '');
        $machineId = User::where('name', $machineName)->pluck('id')->first();
    
        $machineJobs = MachineJob::with([
            'user',
            'dailyItemCode' => function ($query) use ($selectedDate) {
                $query->where('schedule_date', $selectedDate)
                    ->with(['scannedData']);
            },
            'mouldChangeLogs' => function ($query) use ($selectedDate) {
                $query->whereDate('created_at', $selectedDate);
            }
        ])
        ->when($machineId, function ($query) use ($machineId) {
            return $query->whereHas('user', function ($query) use ($machineId) {
                $query->where('id', $machineId);
            });
        })
        ->get();
    
        $structuredData = [];
    
        foreach ($machineJobs as $machineJob) {
            $userName = $machineJob->user->name ?? 'Unknown User';
    
            if (!isset($structuredData[$userName])) {
                $structuredData[$userName] = [
                    'mould_change_log' => [],
                    'daily_item_code' => [],
                    'hourly_production' => []
                ];
            }
    
            // Process mould change logs
            foreach ($machineJob->mouldChangeLogs as $mouldChange) {
                $setupTimeMinute = $mouldChange->masterListItem->setup_time_minute ?? 0;
                $startTime = Carbon::parse($mouldChange->created_at);
                $endTime = Carbon::parse($mouldChange->end_time);
                $actualTime = $startTime->diffInMinutes($endTime);
    
                $structuredData[$userName]['mould_change_log'][] = [
                    'id' => $mouldChange->id,
                    'machine_name' => $mouldChange->user->name,
                    'item_code' => $mouldChange->item_code,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'predicted_time' => $setupTimeMinute,
                    'actual_time' => $actualTime,
                    'pic' => $mouldChange->pic,
                    'status' => ($actualTime > $setupTimeMinute) ? 'problem' : 'safe',
                ];
            }
    
            // Process daily item codes
            foreach ($machineJob->dailyItemCode as $dailyItem) {
                // Calculate total scanned quantity
                $totalScannedQuantity = collect($dailyItem->scannedData)->sum('quantity');
    
                $formattedDailyItem = [
                    'id' => $dailyItem->id,
                    'item_code' => $dailyItem->item_code,
                    'quantity' => $dailyItem->quantity,
                    'final_quantity' => $dailyItem->final_quantity,
                    'loss_package_quantity' => $dailyItem->loss_package_quantity,
                    'actual_quantity' => $dailyItem->actual_quantity,
                    'shift' => $dailyItem->shift,
                    'start_date' => Carbon::parse($dailyItem->start_date)->format('Y-m-d'),
                    'start_time' => Carbon::parse($dailyItem->start_time)->timezone('Asia/Jakarta')->format('H:i:s'),
                    'end_date' => Carbon::parse($dailyItem->end_date)->format('Y-m-d'),
                    'end_time' => Carbon::parse($dailyItem->end_time)->timezone('Asia/Jakarta')->format('H:i:s'),
                    'total_scanned_quantity' => $totalScannedQuantity, // <-- Added this
                    'scanned_data' => []
                ];
    
                // Hourly production
                $hourlyProduction = [];
    
                foreach ($dailyItem->scannedData as $scan) {
                    $hour = Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('H:00');
                    $scanUser = $scan->user ?? 'Unknown';
    
                    if (!isset($hourlyProduction[$hour])) {
                        $hourlyProduction[$hour] = [];
                    }
                    if (!isset($hourlyProduction[$hour][$scanUser])) {
                        $hourlyProduction[$hour][$scanUser] = 0;
                    }
    
                    $hourlyProduction[$hour][$scanUser] += $scan->quantity;
    
                    $formattedDailyItem['scanned_data'][] = [
                        'id' => $scan->id,
                        'spk_code' => $scan->spk_code,
                        'warehouse' => $scan->warehouse,
                        'quantity' => $scan->quantity,
                        'label' => $scan->label,
                        'user' => $scanUser,
                        'scanned_at' => Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    ];
                }
    
                foreach ($hourlyProduction as $hour => $userData) {
                    $structuredData[$userName]['hourly_production'][] = [
                        'hour' => $hour,
                        'users' => $userData
                    ];
                }
    
                $structuredData[$userName]['daily_item_code'][] = $formattedDailyItem;
            }
        }
    
        // dd($structuredData);
    
        // Fetch the machine names
        $machines = User::distinct()
            ->whereIn('id', MachineJob::pluck('user_id'))
            ->pluck('name', 'id');
    
        return view('dashboards.dashboard-master-production', compact('structuredData', 'machines', 'selectedDate'));
    }
    

}