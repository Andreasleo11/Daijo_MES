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
    public function index()
    {
        $today = Carbon::now()->toDateString(); // Get today's date in 'YYYY-MM-DD' format

        $machineJobs = MachineJob::with([
            'user', // Get user details (machine operator)
            'dailyItemCode' => function ($query) use ($today) {
                $query->where('schedule_date', $today) // Filter only today's records
                    ->with([
                        'scannedData' // Get production scanned data per daily item
                    ]);
            },
            'mouldChangeLogs' // Get mould change logs for the machine job
        ])->get();

        $structuredData = [];

        foreach ($machineJobs as $machineJob) {
            $userName = $machineJob->user->name ?? 'Unknown User';

            // Initialize user in the array if not exists with "mould_change_log" and "daily_item_code" keys
            if (!isset($structuredData[$userName])) {
                $structuredData[$userName] = [
                    'mould_change_log' => [], // Add mould change log key
                    'daily_item_code' => []  // Ensure daily item codes are structured properly
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
                    'start_time' => Carbon::parse($mouldChange->created_at)->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($mouldChange->end_time)->format('Y-m-d H:i:s'),
                    'predicted_time' => $setupTimeMinute,
                    'actual_time' => $actualTime,
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
                    'start_time' => Carbon::parse($dailyItem->start_time)->format('H:i:s'),
                    'end_date' => Carbon::parse($dailyItem->end_date)->format('Y-m-d'),
                    'end_time' => Carbon::parse($dailyItem->end_time)->format('H:i:s'),
                    'scanned_data' => []
                ];

                foreach ($dailyItem->scannedData as $scan) {
                    $formattedDailyItem['scanned_data'][] = [
                        'id' => $scan->id,
                        'spk_code' => $scan->spk_code,
                        'warehouse' => $scan->warehouse,
                        'quantity' => $scan->quantity,
                        'label' => $scan->label,
                        'scanned_at' => Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    ];
                }

                // Append the formatted daily item to the "daily_item_code" array
                $structuredData[$userName]['daily_item_code'][] = $formattedDailyItem;
            }
        }

        dd($structuredData);
    }
}