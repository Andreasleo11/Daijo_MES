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
        $today = Carbon::now()->toDateString(); // Get today's date in 'YYYY-MM-DD' format
        $machineName = $request->input('machine_name', '');

        $machineId = User::where('name', $machineName)->pluck('id')->first();
        
        $machineJobs = MachineJob::with([
            'user', // Get user details (machine operator)
            'dailyItemCode' => function ($query) use ($today) {
                $query->where('schedule_date', $today) // Filter only today's records
                    ->with([
                        'scannedData' // Get production scanned data per daily item
                    ]);
            },
            'mouldChangeLogs' // Get mould change logs for the machine job
        ])
        ->when($machineId, function ($query) use ($machineId) {
            // Filter by machine name if it's provided
            return $query->whereHas('mouldChangeLogs', function ($query) use ($machineId) {
                $query->where('user_id', $machineId);
            });
        })
        ->get();

        $structuredData = [];

        foreach ($machineJobs as $machineJob) {
            $userName = $machineJob->user->name ?? 'Unknown User';

            // Initialize user in the array if not exists
            if (!isset($structuredData[$userName])) {
                $structuredData[$userName] = [
                    'mould_change_log' => [], // Mould change log
                    'daily_item_code' => [], // Daily item codes
                    'hourly_production' => [] // Hourly production per user
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
                    'status' => ($actualTime > $setupTimeMinute) ? 'problem' : 'safe',
                ];
            }

            // Process daily item codes
            foreach ($machineJob->dailyItemCode as $dailyItem) {
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
                    'scanned_data' => []
                ];

                // Hourly production array
                $hourlyProduction = [];

                foreach ($dailyItem->scannedData as $scan) {
                    $hour = Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('H:00'); // Convert to Indonesia time
                    $scanUser = $scan->user ?? 'Unknown';

                    // Initialize hourly production entry for this user and hour
                    if (!isset($hourlyProduction[$hour])) {
                        $hourlyProduction[$hour] = [];
                    }
                    if (!isset($hourlyProduction[$hour][$scanUser])) {
                        $hourlyProduction[$hour][$scanUser] = 0;
                    }

                    // Add quantity
                    $hourlyProduction[$hour][$scanUser] += $scan->quantity;

                    // Append scanned data
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

                // Append hourly production to the user
                foreach ($hourlyProduction as $hour => $userData) {
                    $structuredData[$userName]['hourly_production'][] = [
                        'hour' => $hour,
                        'users' => $userData
                    ];
                }

                // Append the formatted daily item
                $structuredData[$userName]['daily_item_code'][] = $formattedDailyItem;
            }
        }
        $machines = User::distinct()
        ->whereIn('id', MachineJob::pluck('user_id')) // Get user IDs (machine IDs) from MachineJob
        ->pluck('name', 'id'); // Get the machine name and user_id (machine ID)
     
        // dd($structuredData);
        return view('dashboards.dashboard-master-production', compact('structuredData', 'machines'));
    }

}