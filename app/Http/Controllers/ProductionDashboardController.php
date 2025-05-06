<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionReport;
use App\Models\ProductionScannedData;
use App\Models\SpkMaster;
use App\Models\Delivery\sapInventoryFg;
use App\Models\User;
use App\Models\MachineJob;
use App\Models\OperatorUser;
use App\Models\DailyItemCode;
use App\Models\MouldChangeLog;
use App\Models\AdjustMachineLog; // Make sure the model is imported
use App\Models\MasterZone;
use App\Models\ZoneLog;
use App\Models\ZonePengawas;
use App\Models\RepairMachineLog;
use Carbon\Carbon;

class ProductionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->toDateString());
        $machineName = $request->input('machine_name', '');
        $machineId = User::where('name', $machineName)->pluck('id')->first();

        $machineJobs = MachineJob::with([
            'user',
            'dailyItemCode' => function ($query) use ($selectedDate) {
                $query->where('schedule_date', $selectedDate)->with(['scannedData']);
            },
            'mouldChangeLogs' => function ($query) use ($selectedDate) {
                $query->whereDate('created_at', $selectedDate);
            },
            'adjustMachineLogs' => function ($query) use ($selectedDate) { // 🔹 New Relation
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

            $user = $machineJob->user;
            $zoneId = $user->zone_id;
            $zoneData = MasterZone::find($zoneId);

            $pengawas = []; // Initialize as array

            for ($shift = 1; $shift <= 3; $shift++) {
                $latestZoneLog = ZoneLog::where('zone_id', $zoneId)
                    ->where('shift', $shift)
                    ->whereDate('start_date', '<=', $selectedDate)
                    ->whereDate('end_date', '>=', $selectedDate)
                    ->orderByDesc('updated_at')
                    ->first();

                $pengawasName = $latestZoneLog->pengawas ?? 'Unknown';

                $pengawasUser = OperatorUser::where('name', $pengawasName)->first();

                $pengawasProfilePath = $pengawasUser && $pengawasUser->profile_picture
                    ? asset('storage/' . $pengawasUser->profile_picture)
                    : asset('images/default_profile.jpg');

                $pengawas[$shift] = [
                    'name' => $pengawasName,
                    'profile_path' => $pengawasProfilePath,
                    'zone_name' => $zoneData->zone_name ?? 'Unknown',
                ];
            }

          
          
            if (!isset($structuredData[$userName])) {
                $structuredData[$userName] = [
                    'pengawas' => $pengawas,
                    'mould_change_log' => [],
                    'adjust_machine_logs' => [],
                    'repair_machine_logs' => [], 
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

                // 🔹 Fetch operator user details (for `pic`)
                $operatorUser = OperatorUser::where('name', $mouldChange->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg'); // Default profile image

                $structuredData[$userName]['mould_change_log'][] = [
                    'id' => $mouldChange->id,
                    'machine_name' => $mouldChange->user->name,
                    'item_code' => $mouldChange->item_code,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'predicted_time' => $setupTimeMinute,
                    'actual_time' => $actualTime,
                    'pic' => $mouldChange->pic,
                    'pic_profile_path' => $operatorProfilePath, // 🔹 Added profile picture
                    'status' => ($actualTime > $setupTimeMinute) ? 'problem' : 'safe',
                ];
            }


            foreach ($machineJob->adjustMachineLogs as $adjustLog) {
                $setupTimeMinute = $mouldChange->masterListItem->setup_time_minute ?? 0;
                $startTime = Carbon::parse($adjustLog->created_at);
                $endTime = Carbon::parse($adjustLog->end_time);
                $actualTime = $startTime->diffInMinutes($endTime);

                $operatorUser = OperatorUser::where('name', $adjustLog->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg'); // Default profile image
                $structuredData[$userName]['adjust_machine_logs'][] = [
                    'id' => $adjustLog->id,
                    'machine_name' => $adjustLog->user->name,
                    'item_code' => $adjustLog->item_code,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'predicted_time' => $setupTimeMinute,
                    'actual_time' => $actualTime,
                    'pic' => $adjustLog->pic,
                    'pic_profile_path' => $operatorProfilePath,
                    'status' => ($actualTime > $setupTimeMinute) ? 'problem' : 'safe',
                    'start_time' => Carbon::parse($adjustLog->created_at)->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($adjustLog->end_time)->format('Y-m-d H:i:s'),
                ];
            }

             // Process repair machine logs
            foreach ($machineJob->repairMachineLogs as $repairLog) {
                $startTime = Carbon::parse($repairLog->created_at);
                $endTime = Carbon::parse($repairLog->finish_repair);
                $actualTime = $startTime->diffInMinutes($endTime);

                // Fetch operator user details (for `pic`)
                $operatorUser = OperatorUser::where('name', $repairLog->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg'); // Default profile image

                $structuredData[$userName]['repair_machine_logs'][] = [
                    'id' => $repairLog->id,
                    'machine_name' => $repairLog->user->name,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'problem' => $repairLog->problem,
                    'remark' => $repairLog->remark,
                    'actual_time' => $actualTime,
                    'pic' => $repairLog->pic,
                    'pic_profile_path' => $operatorProfilePath, // Added profile picture
                    'status' => ($actualTime > 30) ? 'problem' : 'safe', // Example condition for problem
                ];
            }


            // Process daily item codes
            foreach ($machineJob->dailyItemCode as $dailyItem) {
                $totalScannedQuantity = collect($dailyItem->scannedData)->sum('quantity');

                
                $cycleTime = sapInventoryFg::where('item_code', $dailyItem->item_code)->value('cycle_time');
                $cycleTimeInSeconds = $cycleTime ? $cycleTime * 60 : null;

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
                    'total_scanned_quantity' => $totalScannedQuantity,
                    'cycle_time_seconds' => $cycleTimeInSeconds,
                    'scanned_data' => []
                ];

                // Hourly production
                $hourlyProduction = [];

                foreach ($dailyItem->scannedData as $scan) {
                    $hour = Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('H:00');
                    $scanUser = $scan->user ?? 'Unknown';
                
                    // 🔹 Fetch profile picture of scanned user
                    $scannedUser = OperatorUser::where('name', $scanUser)->first();
                    $scannedUserProfilePath = $scannedUser && $scannedUser->profile_picture 
                        ? asset('storage/' . $scannedUser->profile_picture) 
                        : asset('images/default_profile.jpg');
                
                        $itemCode = $dailyItem->item_code;

                        if (!isset($hourlyProduction[$hour])) {
                            $hourlyProduction[$hour] = [];
                        }
                        if (!isset($hourlyProduction[$hour][$itemCode])) {
                            $hourlyProduction[$hour][$itemCode] = [];
                        }
                        if (!isset($hourlyProduction[$hour][$itemCode][$scanUser])) {
                            $hourlyProduction[$hour][$itemCode][$scanUser] = [
                                'quantity' => 0,
                                'user_profile_path' => $scannedUserProfilePath
                            ];
                        }
                        $hourlyProduction[$hour][$itemCode][$scanUser]['quantity'] += $scan->quantity;
                
                    $formattedDailyItem['scanned_data'][] = [
                        'id' => $scan->id,
                        'spk_code' => $scan->spk_code,
                        'warehouse' => $scan->warehouse,
                        'quantity' => $scan->quantity,
                        'label' => $scan->label,
                        'user' => $scanUser,
                        'user_profile_path' => $scannedUserProfilePath, // 🔹 Added profile picture
                        'scanned_at' => Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    ];
                }
                
                foreach ($hourlyProduction as $hour => $items) {
                    foreach ($items as $itemCode => $userData) {
                        $structuredData[$userName]['hourly_production'][] = [
                            'hour' => $hour,
                            'item_code' => $itemCode,
                            'users' => $userData
                        ];
                    }
                }
                
                $structuredData[$userName]['daily_item_code'][] = $formattedDailyItem;
            }
        }

        // dd($structuredData); // Debug output

        $machines = User::distinct()
            ->whereIn('id', MachineJob::pluck('user_id'))
            ->pluck('name', 'id');
        // dd($structuredData);
        return view('dashboards.dashboard-master-production', compact('structuredData', 'machines', 'selectedDate'));
    }

    

}