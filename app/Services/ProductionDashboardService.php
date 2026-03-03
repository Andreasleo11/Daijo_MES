<?php

namespace App\Services;

use App\Models\DailyItemCode;
use App\Models\MasterListItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get downtime analysis
     * Calculate downtime for hours that didn't meet target
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @return array
     */
    // public function getDowntimeAnalysis(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null): array
    // {
    //     $query = DailyItemCode::query()
    //         ->with(['hourlyRemarks.ngDetails'])
    //         ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

    //     if ($itemCode) {
    //         $query->where('item_code', $itemCode);
    //     }

    //     if ($machineUserId) {
    //         $query->where('user_id', $machineUserId);
    //     }

    //     $dailyData = $query->get();
        
    //     $totalDowntime = 0; // in minutes
    //     $downtimeByHour = [];
    //     $problemHours = [];

    //     foreach ($dailyData as $daily) {
    //         // Group hourly remarks by hour, keep only highest actual_production if duplicate
    //         $hourlyByHour = [];
            
    //         foreach ($daily->hourlyRemarks as $hourly) {
    //             $hour = $hourly->start_time ?? 0;
                
    //             // If hour exists, keep the one with higher actual_production
    //             if (isset($hourlyByHour[$hour])) {
    //                 $existingActual = $hourlyByHour[$hour]->actual_production ?? 0;
    //                 $newActual = $hourly->actual_production ?? 0;
                    
    //                 if ($newActual > $existingActual) {
    //                     $hourlyByHour[$hour] = $hourly;
    //                 }
    //             } else {
    //                 $hourlyByHour[$hour] = $hourly;
    //             }
    //         }

    //         // Calculate downtime for each hour
    //         foreach ($hourlyByHour as $hour => $hourly) {
    //             $target = $hourly->target ?? 0;
    //             $actualProduction = $hourly->actual_production ?? 0;
                
    //             // Calculate total NG for this hour
    //             // $ng = 0;
    //             // if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
    //             //     foreach ($hourly->ngDetails as $ngDetail) {
    //             //         $ng += $ngDetail->ng_quantity ?? 0;
    //             //     }
    //             // }
                
    //             // $actual = $actualProduction + $ng;
    //             $actual = $actualProduction;
                
    //             // If actual < target, calculate downtime
    //             if ($actual < $target && $target > 0) {
    //                 // Formula: Downtime (minutes) = (Target - Actual) × (60 / Target)
    //                 $downtime = ($target - $actual) * (60 / $target);
    //                 $totalDowntime += $downtime;
                    
    //                 // Group by hour (0-23)
    //                 if (!isset($downtimeByHour[$hour])) {
    //                     $downtimeByHour[$hour] = [
    //                         'hour' => str_pad($hour, 2, '0', STR_PAD_LEFT),
    //                         'total_downtime' => 0,
    //                         'occurrences' => 0,
    //                     ];
    //                 }
                    
    //                 $downtimeByHour[$hour]['total_downtime'] += $downtime;
    //                 $downtimeByHour[$hour]['occurrences']++;
                    
    //                 // Collect problem hours for remarks
    //                 $problemHours[] = [
    //                     'id' => $hourly->id,
    //                     'date' => $daily->start_date,
    //                     'hour' => $hour,
    //                     'target' => $target,
    //                     'actual' => $actual,
    //                     'downtime' => $downtime,
    //                     'remark' => $hourly->remark ?? '',
    //                 ];
    //             }
    //         }
    //     }

    //     // Sort downtime by hour (sort berdasarkan jam)
    //     // ksort($downtimeByHour);

    //     //sort berdasarkan occurence 
    //     uasort($downtimeByHour, function ($a, $b) {
    //         return $b['occurrences'] <=> $a['occurrences'];
    //     });
    //     // dd($downtimeByHour);
        
    //     return [
    //         'total_downtime_minutes' => round($totalDowntime, 2),
    //         'total_downtime_hours' => round($totalDowntime / 60, 2),
    //         'downtime_by_hour' => array_values($downtimeByHour),
    //         'problem_hours_count' => count($problemHours),
    //     ];
    // }

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
}