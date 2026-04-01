<?php

namespace App\Services\Production;

use App\Models\DailyItemCode;
use App\Models\MasterListItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use App\Models\MachineJob;
use App\Models\User;
use App\Models\MasterZone;
use App\Models\ZoneLog;
use App\Models\OperatorUser;
use App\Models\Delivery\sapInventoryFg;

class ProductionDashboardService
{
    /**
     * Get production data for dashboard
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @return array
     */
    public function getProductionData(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null): array
    {
        $query = DailyItemCode::query()
            ->with([
                'hourlyRemarks.ngDetails.ngType', // ✅ Load ngDetails relation
                'user:id,name'
            ])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $query->where('item_code', $itemCode);
        }

        if ($machineUserId) {
            $query->where('user_id', $machineUserId);
        }

        $dailyData = $query->get();

        return $this->processProductionData($dailyData, $startDate, $endDate);
    }

    /**
     * Process raw data into chart-ready format
     * 
     * @param \Illuminate\Database\Eloquent\Collection $dailyData
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function processProductionData($dailyData, Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $chartData = [];
        $summary = [
            'total_target' => 0,
            'total_actual' => 0,
            'total_ng' => 0,
            'ng_rate' => 0,
            'achievement_rate' => 0,
        ];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $dailyData->where('start_date', $dateStr);

            $target = 0;
            $actual = 0;
            $ng = 0;
            

            foreach ($dayData as $daily) {
                foreach ($daily->hourlyRemarks as $hourly) {
                    // Aggregate target and actual from hourly remarks
                    $target += $hourly->target ?? 0;
                    $actual += $hourly->actual_production ?? 0;
                    
                    // ✅ Calculate NG from ngDetails table
                    if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
                        foreach ($hourly->ngDetails as $ngDetail) {
                            $ng += $ngDetail->ng_quantity ?? 0; // ✅ Correct field name
                        }
                    }
                }
            }

            $chartData[] = [
                'date' => $date->format('d M'),
                'full_date' => $dateStr,
                'target' => $target,
                'actual' => $actual,
                'ng' => $ng,
                'ng_rate' => ($actual + $ng) > 0 ? round(($ng / ($actual + $ng)) * 100, 2) : 0,
                'achievement' => $target > 0 ? round(($actual / $target) * 100, 2) : 0,
            ];

            $summary['total_target'] += $target;
            $summary['total_actual'] += $actual;
            $summary['total_ng'] += $ng;
        }

        // Calculate summary rates
        $totalProduction = $summary['total_actual'] + $summary['total_ng'];
        $summary['ng_rate'] = $totalProduction > 0 
            ? round(($summary['total_ng'] / $totalProduction) * 100, 2) 
            : 0;
        
        $summary['achievement_rate'] = $summary['total_target'] > 0 
            ? round(($summary['total_actual'] / $summary['total_target']) * 100, 2) 
            : 0;

        return [
            'chart_data' => $chartData,
            'summary' => $summary,
        ];
    }

    public function getDowntimeAnalysis(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null): array
    {
        $query = DailyItemCode::query()
            ->with(['hourlyRemarks.ngDetails'])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $query->where('item_code', $itemCode);
        }

        if ($machineUserId) {
            $query->where('user_id', $machineUserId);
        }

        $dailyData = $query->get();

        // Preload cycle time semua item sekaligus
        $masterItems = MasterListItem::whereIn('item_code', $dailyData->pluck('item_code')->unique())
            ->pluck('cycle_time', 'item_code');

        $totalDowntime  = 0;
        $downtimeByHour = [];
        $problemHours   = [];
        $mergedHours    = [];

        foreach ($dailyData as $daily) {
            $hourlyByHour = [];

            foreach ($daily->hourlyRemarks as $hourly) {
                $rawTime = $hourly->start_time ?? '0';

                // Ambil integer hour untuk key (07:30:00 → 7)
                $hour = is_numeric($rawTime)
                    ? (int) $rawTime
                    : (int) Carbon::parse($rawTime)->format('H');

                // Simpan label original (07:30:00 → "07:30")
                $hourly->_hour_label = is_numeric($rawTime)
                    ? str_pad((int) $rawTime, 2, '0', STR_PAD_LEFT) . ':00'
                    : Carbon::parse($rawTime)->format('H:i');

                if (isset($hourlyByHour[$hour])) {
                    $existingActual = $hourlyByHour[$hour]->actual_production ?? 0;
                    $newActual      = $hourly->actual_production ?? 0;
                    if ($newActual > $existingActual) {
                        $hourlyByHour[$hour] = $hourly;
                    }
                } else {
                    $hourlyByHour[$hour] = $hourly;
                }
            }

            foreach ($hourlyByHour as $hour => $hourly) {
                $target = $hourly->target            ?? 0;
                $actual = $hourly->actual_production ?? 0;

                // Skip kalau target 0 atau actual >= target
                if ($target <= 0 || $actual >= $target) continue;

                $cycleTimeSec = ($daily->temporal_cycle_time && $daily->temporal_cycle_time > 0)
                    ? $daily->temporal_cycle_time
                    : $masterItems->get($daily->item_code);

                if (!$cycleTimeSec || $cycleTimeSec <= 0) continue;

                $cycleTimeMinutes = $cycleTimeSec / 60;
                $actualMinutes    = $actual * $cycleTimeMinutes;

                // Key unik: mesin + tanggal + jam
                $key = ($daily->user_id ?? 'unknown') . '_' . $daily->start_date . '_' . $hour;

                if (!isset($mergedHours[$key])) {
                    // Buat label "07:30 - 08:30" dari original start_time
                    $startLabel = $hourly->_hour_label ?? str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    $endLabel   = Carbon::parse($daily->start_date . ' ' . $startLabel)
                        ->addHour()
                        ->format('H:i');

                    $mergedHours[$key] = [
                        'machine'        => $daily->user_id ?? 'unknown',
                        'date'           => $daily->start_date,
                        'hour'           => $hour,
                        'hour_label'     => $startLabel . ' - ' . $endLabel,
                        'total_prod_min' => 0,
                        'remarks'        => [],
                        'items'          => [],
                    ];
                }

                $mergedHours[$key]['total_prod_min'] += $actualMinutes;
                $mergedHours[$key]['remarks'][]       = $hourly->remark ?? '';
                $mergedHours[$key]['items'][]         = [
                    'id'           => $hourly->id,
                    'item_code'    => $daily->item_code,
                    'target'       => $target,
                    'actual'       => $actual,
                    'cycle_time'   => $cycleTimeSec,
                    'prod_minutes' => round($actualMinutes, 2),
                ];
            }
        }

        // Hitung downtime dari merged hours
        foreach ($mergedHours as $merged) {
            $hour      = $merged['hour'];
            $hourLabel = $merged['hour_label'];

            // Downtime = 60 menit - total menit produksi aktual
            $finalDowntime = max(0, 60 - $merged['total_prod_min']);

            if ($finalDowntime <= 0) continue;

            $totalDowntime += $finalDowntime;

            if (!isset($downtimeByHour[$hour])) {
                $downtimeByHour[$hour] = [
                    'hour'           => $hourLabel,
                    'total_downtime' => 0,
                    'occurrences'    => 0,
                ];
            }

            $downtimeByHour[$hour]['total_downtime'] += $finalDowntime;
            $downtimeByHour[$hour]['occurrences']++;

            $problemHours[] = [
                'id'             => $merged['items'][0]['id'] ?? null,
                'date'           => $merged['date'],
                'hour'           => $hourLabel,
                'total_prod_min' => round($merged['total_prod_min'], 2),
                'downtime'       => round($finalDowntime, 2),
                'remark'         => implode(' | ', array_filter($merged['remarks'])),
                'items'          => $merged['items'],
            ];
        }

        // Sort berdasarkan occurrences
        uasort($downtimeByHour, fn($a, $b) => $b['occurrences'] <=> $a['occurrences']);

        return [
            'total_downtime_minutes' => round($totalDowntime, 2),
            'total_downtime_hours'   => round($totalDowntime / 60, 2),
            'downtime_by_hour'       => array_values($downtimeByHour),
            'problem_hours_count'    => count($problemHours),
        ];
    }

    /**
     * Get top 10 remarks from problem hours (where actual < target)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @return array
     */
    public function getTopProblematicRemarks(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null): array
    {
        $query = DailyItemCode::query()
            ->with(['hourlyRemarks.ngDetails', 'user'])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $query->where('item_code', $itemCode);
        }

        if ($machineUserId) {
            $query->where('user_id', $machineUserId);
        }

        $dailyData = $query->get();

        // Preload cycle time — sama seperti getDowntimeAnalysis
        $masterItems = MasterListItem::whereIn('item_code', $dailyData->pluck('item_code')->unique())
            ->pluck('cycle_time', 'item_code');

        $mergedHours = [];

        foreach ($dailyData as $daily) {
            $hourlyByHour = [];

            foreach ($daily->hourlyRemarks as $hourly) {
                $rawTime = $hourly->start_time ?? '0';

                $hour = is_numeric($rawTime)
                    ? (int) $rawTime
                    : (int) Carbon::parse($rawTime)->format('H');

                $hourly->_hour_label = is_numeric($rawTime)
                    ? str_pad((int) $rawTime, 2, '0', STR_PAD_LEFT) . ':00'
                    : Carbon::parse($rawTime)->format('H:i');

                if (isset($hourlyByHour[$hour])) {
                    $existingActual = $hourlyByHour[$hour]->actual_production ?? 0;
                    $newActual      = $hourly->actual_production ?? 0;
                    if ($newActual > $existingActual) {
                        $hourlyByHour[$hour] = $hourly;
                    }
                } else {
                    $hourlyByHour[$hour] = $hourly;
                }
            }

            foreach ($hourlyByHour as $hour => $hourly) {
                $target = $hourly->target            ?? 0;
                $actual = $hourly->actual_production ?? 0;

                // Skip kalau target 0 atau actual >= target
                if ($target <= 0 || $actual >= $target) continue;

                // Skip kalau tidak ada remark
                if (empty($hourly->remark)) continue;

                $cycleTimeSec = ($daily->temporal_cycle_time && $daily->temporal_cycle_time > 0)
                    ? $daily->temporal_cycle_time
                    : $masterItems->get($daily->item_code);

                if (!$cycleTimeSec || $cycleTimeSec <= 0) continue;

                $cycleTimeMinutes = $cycleTimeSec / 60;
                $actualMinutes    = $actual * $cycleTimeMinutes;

                $key = ($daily->user_id ?? 'unknown') . '_' . $daily->start_date . '_' . $hour;

                if (!isset($mergedHours[$key])) {
                    $startLabel = $hourly->_hour_label ?? str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    $endLabel   = Carbon::parse($daily->start_date . ' ' . $startLabel)
                        ->addHour()
                        ->format('H:i');

                    $mergedHours[$key] = [
                        'date'           => $daily->start_date,
                        'hour'           => $hour,
                        'hour_label'     => $startLabel . ' - ' . $endLabel,
                        'machine'        => $daily->user->name ?? 'Unknown',
                        'total_prod_min' => 0,
                        'remarks'        => [],
                        'items'          => [],
                    ];
                }

                $mergedHours[$key]['total_prod_min'] += $actualMinutes;
                $mergedHours[$key]['remarks'][]       = $hourly->remark;
                $mergedHours[$key]['items'][]         = [
                    'item_code' => $daily->item_code,
                    'target'    => $target,
                    'actual'    => $actual,
                    'cycle_time'=> $cycleTimeSec,
                ];
            }
        }

        // Build problem remarks dari merged hours
        $problemRemarks = [];

        foreach ($mergedHours as $merged) {
            $finalDowntime = max(0, 60 - $merged['total_prod_min']);

            if ($finalDowntime <= 0) continue;

            // Gabungkan semua target & actual dari items untuk hitung gap
            $totalTarget = collect($merged['items'])->sum('target');
            $totalActual = collect($merged['items'])->sum('actual');
            $gap         = $totalTarget - $totalActual;

            $problemRemarks[] = [
                'date'             => $merged['date'],
                'hour'             => $merged['hour_label'],
                'machine'          => $merged['machine'],
                'item_code'        => collect($merged['items'])->pluck('item_code')->unique()->implode(', '),
                'target'           => $totalTarget,
                'actual'           => $totalActual,
                'gap'              => $gap,
                'downtime_minutes' => round($finalDowntime, 2),
                'remark'           => implode(' | ', array_filter($merged['remarks'])),
                'severity'         => $this->calculateSeverity($gap, $totalTarget),
            ];
        }

        // Sort by gap descending — worst problems first
        usort($problemRemarks, fn($a, $b) => $b['gap'] - $a['gap']);

        return array_slice($problemRemarks, 0, 20);
    }

    /**
     * Calculate severity level based on gap percentage
     * 
     * @param int $gap
     * @param int $target
     * @return string
     */
    private function calculateSeverity(int $gap, int $target): string
    {
        if ($target == 0) return 'low';
        
        $percentage = ($gap / $target) * 100;
        
        if ($percentage >= 50) return 'critical';
        if ($percentage >= 30) return 'high';
        if ($percentage >= 15) return 'medium';
        return 'low';
    }

    /**
     * Get NG breakdown by type
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @return array
     */
    public function getNgBreakdown(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null): array
    {
        $query = DailyItemCode::query()
            ->with([
                'hourlyRemarks.ngDetails.ngType' // ✅ Load relations
            ])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $query->where('item_code', $itemCode);
        }

        if ($machineUserId) {
            $query->where('user_id', $machineUserId);
        }

        $dailyData = $query->get();
        $ngBreakdown = [];

        foreach ($dailyData as $daily) {
            foreach ($daily->hourlyRemarks as $hourly) {
                if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
                    foreach ($hourly->ngDetails as $ngDetail) {
                        $ngTypeName = $ngDetail->ngType->ng_type ?? 'Unknown';
                        
                        if (!isset($ngBreakdown[$ngTypeName])) {
                            $ngBreakdown[$ngTypeName] = [
                                'name' => $ngTypeName,
                                'total' => 0,
                            ];
                        }
                        
                        $ngBreakdown[$ngTypeName]['total'] += $ngDetail->ng_quantity ?? 0; // ✅ Correct field
                    }
                }
            }
        }

        // Sort by total descending
        usort($ngBreakdown, function($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $ngBreakdown;
    }

    /**
     * Get unique item codes for filter
     * Optionally filtered by year and month
     * 
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function getItemCodes(?int $year = null, ?int $month = null): array
    {
        $query = DailyItemCode::select('item_code')
            ->distinct();

        // Apply year filter if provided
        if ($year) {
            $query->whereYear('start_date', $year);
        }

        // Apply month filter if provided
        if ($month && $year) {
            $query->whereMonth('start_date', $month);
        }

        return $query->orderBy('item_code')
            ->pluck('item_code')
            ->toArray();
    }

    /**
     * Get unique machine names for filter
     * 
     * @return array
     */
    public function getMachineNames(): array
    {
        return DailyItemCode::with('user:id,name')
            ->whereNotNull('user_id')
            ->get()
            ->pluck('user.name')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get weeks in a month
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getWeeksInMonth(int $year, int $month): array
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $endOfMonth = $date->copy()->endOfMonth();
        
        $weeks = [];
        $weekNumber = 1;
        
        while ($date <= $endOfMonth) {
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $date->copy()->endOfWeek();
            
            // Adjust to month boundaries
            if ($weekStart->month != $month) {
                $weekStart = $date->copy()->startOfMonth();
            }
            
            if ($weekEnd->month != $month) {
                $weekEnd = $date->copy()->endOfMonth();
            }
            
            $weeks[] = [
                'number' => $weekNumber,
                'label' => "Week {$weekNumber} ({$weekStart->format('d')} - {$weekEnd->format('d')})",
                'start' => $weekStart->format('Y-m-d'),
                'end' => $weekEnd->format('Y-m-d'),
            ];
            
            $date->addWeek()->startOfWeek();
            $weekNumber++;
        }
        
        return $weeks;
    }

    /**
     * Get main dashboard data (replacing the Fat Controller logic)
     * 
     * @param string $selectedDate
     * @param string|null $machineName
     * @return array
     */
    public function getDashboardData(string $selectedDate, ?string $machineName = ''): array
    {
        $machineId = User::where('name', $machineName)->pluck('id')->first();

        $machineJobs = MachineJob::with([
            'user',
            'dailyItemCode' => function ($query) use ($selectedDate) {
                $query->where('schedule_date', $selectedDate)->with(['scannedData', 'hourlyRemarks','masterItem','delsched']);
            },
            'mouldChangeLogs' => function ($query) use ($selectedDate) {
                $query->whereDate('created_at', $selectedDate);
            },
            'adjustMachineLogs' => function ($query) use ($selectedDate) {
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
            $zoneId = $user->zone_id ?? 'A';
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
                    'hourly_production' => [],
                    'hourly_remarks' => [] 
                ];
            }

            // Process mould change logs
            foreach ($machineJob->mouldChangeLogs as $mouldChange) {
                $setupTimeMinute = $mouldChange->masterListItem->setup_time_minute ?? 0;
                $startTime = Carbon::parse($mouldChange->created_at);
                $endTime = Carbon::parse($mouldChange->end_time);
                $actualTime = $startTime->diffInMinutes($endTime);

                $operatorUser = OperatorUser::where('name', $mouldChange->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg');

                $structuredData[$userName]['mould_change_log'][] = [
                    'id' => $mouldChange->id,
                    'machine_name' => $mouldChange->user->name,
                    'item_code' => $mouldChange->item_code,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'predicted_time' => $setupTimeMinute,
                    'actual_time' => $actualTime,
                    'pic' => $mouldChange->pic,
                    'pic_profile_path' => $operatorProfilePath,
                    'status' => ($actualTime > $setupTimeMinute) ? 'problem' : 'safe',
                    'remark' => $mouldChange->remark,
                ];
            }

            // Process adjust machine logs
            foreach ($machineJob->adjustMachineLogs as $adjustLog) {
                $setupTimeMinute = $adjustLog->masterListItem->setup_time_minute ?? 0;
                $startTime = Carbon::parse($adjustLog->created_at);
                $endTime = Carbon::parse($adjustLog->end_time);
                $actualTime = $startTime->diffInMinutes($endTime);

                $operatorUser = OperatorUser::where('name', $adjustLog->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg');
                
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
                    'remark' => $adjustLog->remark,
                ];
            }

            // Process repair machine logs
            foreach ($machineJob->repairMachineLogs as $repairLog) {
                $startTime = Carbon::parse($repairLog->created_at);
                $endTime = Carbon::parse($repairLog->finish_repair);
                $actualTime = $startTime->diffInMinutes($endTime);
                
                $operatorUser = OperatorUser::where('name', $repairLog->pic)->first();
                $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                    ? asset('storage/' . $operatorUser->profile_picture) 
                    : asset('images/default_profile.jpg');

                $structuredData[$userName]['repair_machine_logs'][] = [
                    'id' => $repairLog->id,
                    'machine_name' => $repairLog->user->name,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'problem' => $repairLog->problem,
                    'remark' => $repairLog->remark,
                    'actual_time' => $actualTime,
                    'pic' => $repairLog->pic,
                    'pic_profile_path' => $operatorProfilePath,
                    'status' => ($actualTime > 30) ? 'problem' : 'safe',
                ];
            }

            // Process daily item codes
            foreach ($machineJob->dailyItemCode as $dailyItem) {
                $today = now()->startOfDay();
                $fiveDaysLater = now()->addDays(5)->endOfDay();

                $delschedData = $dailyItem->delsched()
                    ->where(function ($query) use ($today, $fiveDaysLater) {
                        $query->whereBetween('delivery_date', [$today->format('Y-m-d'), $fiveDaysLater->format('Y-m-d')])
                            ->orWhere(function ($q) use ($today) {
                                $q->where('status', 'open')
                                    ->where('delivery_date', '<=', $today->format('Y-m-d'));
                            });
                    })
                    ->orderBy('delivery_date', 'asc')
                    ->get();

                $totalScannedQuantity = $dailyItem->hourlyRemarks->sum(function ($remark) {
                    return $remark->actual_production ?? 0;
                });
                
                if ($dailyItem->temporal_cycle_time) {
                    $cycleTimeInSeconds = $dailyItem->temporal_cycle_time;
                } else {
                    $cycleTime = sapInventoryFg::where('item_code', $dailyItem->item_code)->value('cycle_time');
                    $cycleTimeInSeconds = $cycleTime ? $cycleTime * 60 : null;
                }

                $sapCycleTime = MasterListItem::where('item_code', $dailyItem->item_code)
                ->value('cycle_time');

                $formattedDailyItem = [
                    'id' => $dailyItem->id,
                    'item_code' => $dailyItem->item_code,
                    'item_name' => $dailyItem->masterItem?->item_name ?? '',
                    'quantity' => $dailyItem->quantity,
                    'final_quantity' => $dailyItem->final_quantity,
                    'loss_package_quantity' => $dailyItem->loss_package_quantity,
                    'actual_quantity' => $dailyItem->actual_quantity,
                    'shift' => $dailyItem->shift,
                    'remark' => $dailyItem->remark,
                    'start_date' => Carbon::parse($dailyItem->start_date)->format('Y-m-d'),
                    'start_time' => Carbon::parse($dailyItem->start_time)->timezone('Asia/Jakarta')->format('H:i:s'),
                    'end_date' => Carbon::parse($dailyItem->end_date)->format('Y-m-d'),
                    'end_time' => Carbon::parse($dailyItem->end_time)->timezone('Asia/Jakarta')->format('H:i:s'),
                    'total_scanned_quantity' => $totalScannedQuantity,
                    'cycle_time_seconds' => $cycleTimeInSeconds,
                    'sap_cycle_time' => $sapCycleTime,  
                    'scanned_data' => [],
                    'delsched' => $delschedData
                ];

                // Hourly production
                $hourlyProduction = [];

                foreach ($dailyItem->scannedData as $scan) {
                    $hour = Carbon::parse($scan->created_at)->timezone('Asia/Jakarta')->format('H:00');
                    $scanUser = $scan->user ?? 'Unknown';
                
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
                        'item_code' => $scan->item_code,
                        'warehouse' => $scan->warehouse,
                        'quantity' => $scan->quantity,
                        'label' => $scan->label,
                        'user' => $scanUser,
                        'user_profile_path' => $scannedUserProfilePath,
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
                
                // Process hourly remarks for this daily item
                foreach ($dailyItem->hourlyRemarks->sortBy('start_time') as $hourlyRemark) {
                    $operatorUser = OperatorUser::where('name', $hourlyRemark->pic)->first();
                    $operatorProfilePath = $operatorUser && $operatorUser->profile_picture 
                        ? asset('storage/' . $operatorUser->profile_picture) 
                        : asset('images/default_profile.jpg');

                    // Calculate achievement percentage
                    $achievementPercentage = 0;
                    if ($hourlyRemark->target > 0) {
                         $achievementPercentage = round(
                            min(($hourlyRemark->actual_production / $hourlyRemark->target) * 100, 100),
                            2
                        );
                    }

                    $status = $hourlyRemark->is_achieve;

                    // Determine status based on achievement
                    if ($status === 1) {
                        $status = 'achieved';
                    } else {
                        $status = 'Not Achieved';
                    }

                    $structuredData[$userName]['hourly_remarks'][] = [
                        'id' => $hourlyRemark->id,
                        'machine_name' => $userName,
                        'dic_id' => $dailyItem->id,
                        'item_code' => $dailyItem->item_code,
                        'start_time' => Carbon::parse($hourlyRemark->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($hourlyRemark->end_time)->format('H:i'),
                        'time_range' => Carbon::parse($hourlyRemark->start_time)->format('H:i') . ' - ' . Carbon::parse($hourlyRemark->end_time)->format('H:i'),
                        'target' => $hourlyRemark->target,
                        'actual' => $hourlyRemark->actual,
                        'achievement_percentage' => $achievementPercentage,
                        'actual_production' => $hourlyRemark->actual_production,
                        'ng' =>$hourlyRemark->NG,
                        'remark' => $hourlyRemark->remark ?: '-',
                        'is_achieve' => $hourlyRemark->is_achieve,
                        'status' => $status,
                        'shift' => $dailyItem->shift,
                        'pic' => $hourlyRemark->pic,
                        'pic_profile_path' => $operatorProfilePath,
                        'created_at' => Carbon::parse($hourlyRemark->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($hourlyRemark->updated_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'ng_details' => $hourlyRemark->ngDetails->map(function ($ng) {
                            return [
                                'id' => $ng->id,
                                'ng_type_id' => $ng->ng_type_id,
                                'ng_type' => $ng->ngType->ng_type ?? 'Unknown',
                                'ng_quantity' => $ng->ng_quantity,
                                'ng_remarks' => $ng->ng_remarks,
                                'created_at' => $ng->created_at?->format('Y-m-d H:i:s'),
                                'updated_at' => $ng->updated_at?->format('Y-m-d H:i:s')
                            ];
                        }),
                    ];
                }

                $structuredData[$userName]['daily_item_code'][] = $formattedDailyItem;
            }

            $structuredData[$userName]['hourly_remarks'] = collect($structuredData[$userName]['hourly_remarks'])
            ->sort(function ($a, $b) {
                if ($a['shift'] !== $b['shift']) {
                    return $a['shift'] <=> $b['shift'];
                }
                $createdAtA = \Carbon\Carbon::parse($a['created_at']);
                $createdAtB = \Carbon\Carbon::parse($b['created_at']);
                return $createdAtA <=> $createdAtB;
            })
            ->values()
            ->all();

            $itemCodesInRemarks = collect($structuredData[$userName]['hourly_remarks'])
                ->pluck('item_code')->unique()->values()->toArray();

            $cycleTimeMap = MasterListItem::whereIn('item_code', $itemCodesInRemarks)
                ->pluck('cycle_time', 'item_code');

            $groupedByTimeRange = collect($structuredData[$userName]['hourly_remarks'])
                ->groupBy('time_range');

           foreach ($groupedByTimeRange as $timeRange => $remarks) {
                $totalProdSeconds = 0;
                $singleItem       = $remarks->count() === 1;

                foreach ($remarks as $remark) {
                    $cycleTimeSec = $cycleTimeMap->get($remark['item_code']);
                    if ($cycleTimeSec && $cycleTimeSec > 0) {
                        $totalProdSeconds += $remark['actual_production'] * $cycleTimeSec;
                    }
                }

                if ($singleItem) {
                    $combinedAchieved = $remarks->first()['actual_production'] >= $remarks->first()['target'];
                } elseif ($totalProdSeconds > 0) {
                    $combinedAchieved = $totalProdSeconds >= 3600;
                } else {
                    $combinedAchieved = $remarks->sum('actual_production') >= $remarks->max('target');
                }

                foreach ($structuredData[$userName]['hourly_remarks'] as &$remark) {
                    if ($remark['time_range'] === $timeRange) {
                        $remark['combined_actual_seconds'] = round($totalProdSeconds);
                        $remark['combined_achieved']       = $combinedAchieved;
                        $remark['is_multi_item']           = !$singleItem;
                    }
                }
                unset($remark);
            }

            foreach ($structuredData as $mName => $machineData) {
                $remarks = collect($machineData['hourly_remarks'] ?? []);

                $itemCodes    = $remarks->pluck('item_code')->unique()->values()->toArray();
                $cycleTimeMap = MasterListItem::whereIn('item_code', $itemCodes)
                    ->pluck('cycle_time', 'item_code');

                $dicIds          = $remarks->pluck('dic_id')->filter()->unique()->values()->toArray();
                $temporalCycleMap = DailyItemCode::whereIn('id', $dicIds)
                    ->pluck('temporal_cycle_time', 'id');

                $groupedByHour  = $remarks->groupBy('time_range');
                $totalJamAktif  = $groupedByHour->count();
                $totalProdDetik = 0;

                foreach ($groupedByHour as $timeRange => $hourRemarks) {
                    $jamProdDetik = 0;
                    foreach ($hourRemarks as $remark) {
                        $dicId        = $remark['dic_id'] ?? null;
                        $temporal     = $dicId ? $temporalCycleMap->get($dicId) : null;
                        $cycleTimeSec = ($temporal && $temporal > 0)
                            ? $temporal
                            : $cycleTimeMap->get($remark['item_code']);

                        if ($cycleTimeSec && $cycleTimeSec > 0) {
                            $jamProdDetik += $remark['actual_production'] * $cycleTimeSec;
                        }
                    }
                    $totalProdDetik += min($jamProdDetik, 3600);
                }

                $activeShifts = $remarks->pluck('shift')->unique()->count();
                $patokanJam   = $activeShifts * 8;
                $maxDetik     = $patokanJam * 3600;

                $efficiency = $maxDetik > 0 ? ($totalProdDetik / $maxDetik) * 100 : 0;

                $structuredData[$mName]['machine_efficiency']   = round(min($efficiency, 100), 2);
                $structuredData[$mName]['total_jam_aktif']      = $totalJamAktif;
                $structuredData[$mName]['patokan_jam']          = $patokanJam;
                $structuredData[$mName]['total_prod_menit']     = round($totalProdDetik / 60, 1);
                $structuredData[$mName]['total_downtime_menit'] = round(($maxDetik - $totalProdDetik) / 60, 1);
                
                $average = $remarks->avg(function ($remark) {
                    $achieved = $remark['combined_achieved'] ?? $remark['is_achieve'];
                    return $achieved ? 100 : ($remark['achievement_percentage'] ?? 0);
                });

                $structuredData[$mName]['average_achievement'] = round($average ?? 0, 2);
            }
        }

        return $structuredData;
    }
}
