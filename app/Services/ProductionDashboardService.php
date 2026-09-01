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
     * @param string|null $plant
     * @return array
     */
    public function getProductionData(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null, ?string $plant = null): array
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

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
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
        $chartData = [];
        $summary = [
            'total_target' => 0,
            'total_actual' => 0,
            'total_ng' => 0,
            'ng_rate' => 0,
            'achievement_rate' => 0,
        ];

        if ($startDate->isSameDay($endDate)) {
            // Daily View: Breakdown by 24 hours (00:00 to 23:00)
            $dateStr = $startDate->format('Y-m-d');
            for ($hour = 0; $hour < 24; $hour++) {
                $target = 0;
                $actual = 0;
                $ng = 0;
                $hourUniqueMachines = [];

                foreach ($dailyData as $daily) {
                    $machineId = $daily->user_id;

                    foreach ($daily->hourlyRemarks as $hourly) {
                        $rawTime = $hourly->start_time ?? '0';
                        if (is_numeric($rawTime)) {
                            $hourSlot = (int)$rawTime;
                        } else {
                            try {
                                $hourSlot = (int) Carbon::parse($rawTime)->format('H');
                            } catch (\Exception $e) {
                                $hourSlot = (int)$rawTime;
                            }
                        }

                        if ($hourSlot === $hour) {
                            $target += $hourly->target ?? 0;
                            $actual += $hourly->actual_production ?? 0;

                            if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
                                foreach ($hourly->ngDetails as $ngDetail) {
                                    $ng += $ngDetail->ng_quantity ?? 0;
                                }
                            }

                            if ($machineId && !in_array($machineId, $hourUniqueMachines)) {
                                $hourUniqueMachines[] = $machineId;
                            }
                        }
                    }
                }

                $chartData[] = [
                    'date' => sprintf('%02d:00', $hour),
                    'full_date' => sprintf('%s %02d:00', $dateStr, $hour),
                    'target' => $target,
                    'actual' => $actual,
                    'ng' => $ng,
                    'ng_rate' => ($actual + $ng) > 0 ? round(($ng / ($actual + $ng)) * 100, 2) : 0,
                    'achievement' => $target > 0 ? round(($actual / $target) * 100, 2) : 0,
                    'working_hours' => count($hourUniqueMachines),
                ];

                $summary['total_target'] += $target;
                $summary['total_actual'] += $actual;
                $summary['total_ng'] += $ng;
            }
        } else {
            // Monthly / Weekly View: Breakdown by days
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $dayData = $dailyData->where('start_date', $dateStr);

                $dayUniqueSlots = [];
                $target = 0;
                $actual = 0;
                $ng = 0;
                foreach ($dayData as $daily) {
                    $machineId = $daily->user_id;
                    if (!$machineId) continue;

                    foreach ($daily->hourlyRemarks as $hourly) {
                        $rawTime = $hourly->start_time ?? '0';
                        if (is_numeric($rawTime)) {
                            $hourSlot = (int)$rawTime;
                        } else {
                            try {
                                $hourSlot = (int) Carbon::parse($rawTime)->format('H');
                            } catch (\Exception $e) {
                                $hourSlot = (int)$rawTime;
                            }
                        }
                        $slotKey = $machineId . '_' . $hourSlot;
                        if (!in_array($slotKey, $dayUniqueSlots)) {
                            $dayUniqueSlots[] = $slotKey;
                        }
                    }

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
                    'working_hours' => count($dayUniqueSlots),
                ];

                $summary['total_target'] += $target;
                $summary['total_actual'] += $actual;
                $summary['total_ng'] += $ng;
            }
        }

        // Calculate summary rates
        $totalProduction = $summary['total_actual'] + $summary['total_ng'];
        $summary['ng_rate'] = $totalProduction > 0 
            ? round(($summary['total_ng'] / $totalProduction) * 100, 2) 
            : 0;
        
        $summary['achievement_rate'] = $summary['total_target'] > 0 
            ? round(($summary['total_actual'] / $summary['total_target']) * 100, 2) 
            : 0;

        $summary['total_purging'] = (float)$dailyData->sum('resin_usage');

        $purgingDetails = $dailyData->filter(function($dic) {
            return $dic->resin_usage !== null && $dic->resin_usage > 0;
        })->map(function($dic) {
            return [
                'dic_id' => $dic->id,
                'date' => $dic->start_date ?? $dic->schedule_date,
                'shift' => $dic->shift,
                'machine_name' => $dic->user->name ?? 'Unknown',
                'item_code' => $dic->item_code,
                'resin_usage' => (float)$dic->resin_usage,
            ];
        })->values()->toArray();

        return [
            'chart_data' => $chartData,
            'summary' => $summary,
            'purging_details' => $purgingDetails,
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

    public function getDowntimeAnalysis(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null, ?string $plant = null): array
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

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
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
    public function getTopProblematicRemarks(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null, ?string $plant = null): array
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

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
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
    public function getNgBreakdown(Carbon $startDate, Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null, ?string $plant = null): array
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

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
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
     * Get total working hours per machine
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @return array
     */
    public function getMachineWorkingHours(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, ?string $itemCode = null, ?string $machineUserId = null, ?string $plant = null): array
    {
        $query = DailyItemCode::query()
            ->with(['hourlyRemarks', 'user:id,name'])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $query->where('item_code', $itemCode);
        }

        if ($machineUserId) {
            $query->where('user_id', $machineUserId);
        }

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }

        $dailyData = $query->get();
        $machineHours = [];

        foreach ($dailyData as $daily) {
            $machineId = $daily->user_id;
            $machineName = $daily->user->name ?? 'Unknown';

            if (!$machineId) continue;

            if (!isset($machineHours[$machineId])) {
                $machineHours[$machineId] = [
                    'id' => $machineId,
                    'name' => $machineName,
                    'hours' => 0,
                    'unique_slots' => [],
                ];
            }

            foreach ($daily->hourlyRemarks as $hourly) {
                $rawTime = $hourly->start_time ?? '0';
                
                if (is_numeric($rawTime)) {
                    $hourSlot = (int)$rawTime;
                } else {
                    try {
                        $hourSlot = (int) \Carbon\Carbon::parse($rawTime)->format('H');
                    } catch (\Exception $e) {
                        $hourSlot = (int)$rawTime;
                    }
                }
                
                $slotKey = $daily->start_date . '_' . $hourSlot;
                
                if (!in_array($slotKey, $machineHours[$machineId]['unique_slots'])) {
                    $machineHours[$machineId]['unique_slots'][] = $slotKey;
                    $machineHours[$machineId]['hours'] += 1;
                }
            }
        }

        // Sort by hours descending
        uasort($machineHours, function($a, $b) {
            return $b['hours'] <=> $a['hours'];
        });

        return array_map(function($item) {
            unset($item['unique_slots']);
            return $item;
        }, array_values($machineHours));
    }

    /**
     * Get unique item codes for filter
     * Optionally filtered by year and month
     * 
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function getItemCodes(?int $year = null, ?int $month = null, ?string $plant = null, ?string $date = null): array
    {
        $query = DailyItemCode::select('item_code')
            ->distinct();

        // Apply date or year/month filter if provided
        if ($date) {
            $query->whereDate('start_date', $date);
        } else {
            if ($year) {
                $query->whereYear('start_date', $year);
            }

            if ($month && $year) {
                $query->whereMonth('start_date', $month);
            }
        }

        if ($plant === 'karawang') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
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
     * Get Adjuster, Change Mould, and Shift NG performance analysis
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @param string|null $plant
     * @return array
     */
    public function getShiftPersonnelAndNgAnalysis(
        Carbon $startDate,
        Carbon $endDate,
        ?string $itemCode = null,
        ?string $machineUserId = null,
        ?string $plant = null
    ): array {
        $shiftDefs = [
            1 => ['name' => 'Shift 1 (Pagi)', 'time' => '07:30 - 15:30', 'theme' => 'amber'],
            2 => ['name' => 'Shift 2 (Sore)', 'time' => '15:30 - 23:30', 'theme' => 'emerald'],
            3 => ['name' => 'Shift 3 (Malam)', 'time' => '23:30 - 07:30', 'theme' => 'indigo'],
        ];

        // Date window for logs:
        // Shift 1 begins at startDate 07:30
        // Shift 3 ends at (endDate + 1 day) 07:30
        $windowStart = $startDate->copy()->startOfDay()->setTime(7, 30, 0);
        $windowEnd = $endDate->copy()->addDay()->startOfDay()->setTime(7, 30, 0);

        // Preload master items for setup time
        $masterItems = MasterListItem::pluck('setup_time_minute', 'item_code');

        // 1. Query Adjust Machine Logs
        $adjustQuery = \App\Models\AdjustMachineLog::query()
            ->with(['user:id,name'])
            ->where('created_at', '>=', $windowStart->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $windowEnd->format('Y-m-d H:i:s'));

        if ($itemCode) {
            $adjustQuery->where('item_code', $itemCode);
        }
        if ($machineUserId) {
            $adjustQuery->where('user_id', $machineUserId);
        }
        if ($plant === 'karawang') {
            $adjustQuery->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $adjustQuery->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }
        $adjustLogsRaw = $adjustQuery->get();

        // 2. Query Mould Change Logs
        $mouldQuery = \App\Models\MouldChangeLog::query()
            ->with(['user:id,name'])
            ->where('created_at', '>=', $windowStart->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $windowEnd->format('Y-m-d H:i:s'));

        if ($itemCode) {
            $mouldQuery->where('item_code', $itemCode);
        }
        if ($machineUserId) {
            $mouldQuery->where('user_id', $machineUserId);
        }
        if ($plant === 'karawang') {
            $mouldQuery->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $mouldQuery->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }
        $mouldLogsRaw = $mouldQuery->get();

        // Helper function to determine shift number from a timestamp
        $getShiftFromTimestamp = function ($timestamp) {
            $carbon = Carbon::parse($timestamp);
            $timeStr = $carbon->format('H:i:s');
            if ($timeStr >= '07:30:00' && $timeStr < '15:30:00') {
                return 1;
            } elseif ($timeStr >= '15:30:00' && $timeStr < '23:30:00') {
                return 2;
            } else {
                return 3;
            }
        };

        // Format and categorize Adjust Logs
        $allActivityLogs = [];
        $processedAdjustLogs = [];
        foreach ($adjustLogsRaw as $log) {
            $shiftNum = $getShiftFromTimestamp($log->created_at);
            $start = Carbon::parse($log->created_at);
            $end = $log->end_time ? Carbon::parse($log->end_time) : null;
            $durationMin = $end ? round($start->diffInMinutes($end, true), 1) : 0;
            $targetSetupMin = $masterItems->get($log->item_code) ?? 30;

            $entry = [
                'id' => $log->id,
                'type' => 'adjust',
                'type_label' => 'Adjust Machine',
                'shift' => $shiftNum,
                'machine_name' => $log->user->name ?? 'Unknown',
                'item_code' => $log->item_code ?? '-',
                'pic' => trim($log->pic ?? '') ?: 'Unknown',
                'start_time' => $start->format('d M H:i'),
                'end_time' => $end ? $end->format('H:i') : 'In Progress',
                'duration_minutes' => $durationMin,
                'target_minutes' => $targetSetupMin,
                'is_overtime' => ($durationMin > $targetSetupMin && $targetSetupMin > 0),
                'remark' => $log->remark ?? '',
                'created_at' => $log->created_at,
            ];
            $processedAdjustLogs[] = $entry;
            $allActivityLogs[] = $entry;
        }

        // Format and categorize Mould Change Logs
        $processedMouldLogs = [];
        foreach ($mouldLogsRaw as $log) {
            $shiftNum = $getShiftFromTimestamp($log->created_at);
            $start = Carbon::parse($log->created_at);
            $end = $log->end_time ? Carbon::parse($log->end_time) : null;
            $durationMin = $end ? round($start->diffInMinutes($end, true), 1) : 0;
            $targetSetupMin = $masterItems->get($log->item_code) ?? 60;

            $entry = [
                'id' => $log->id,
                'type' => 'mould_change',
                'type_label' => 'Mould Change',
                'shift' => $shiftNum,
                'machine_name' => $log->user->name ?? 'Unknown',
                'item_code' => $log->item_code ?? '-',
                'pic' => trim($log->pic ?? '') ?: 'Unknown',
                'start_time' => $start->format('d M H:i'),
                'end_time' => $end ? $end->format('H:i') : 'In Progress',
                'duration_minutes' => $durationMin,
                'target_minutes' => $targetSetupMin,
                'is_overtime' => ($durationMin > $targetSetupMin && $targetSetupMin > 0),
                'remark' => $log->remark ?? '',
                'created_at' => $log->created_at,
            ];
            $processedMouldLogs[] = $entry;
            $allActivityLogs[] = $entry;
        }

        // Sort all activity logs descending by timestamp
        usort($allActivityLogs, function ($a, $b) {
            return Carbon::parse($b['created_at'])->timestamp <=> Carbon::parse($a['created_at'])->timestamp;
        });

        // 3. Query Daily Item Codes for Shift Production & NG
        $dicQuery = DailyItemCode::query()
            ->with([
                'hourlyRemarks.ngDetails.ngType',
                'user:id,name',
            ])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $dicQuery->where('item_code', $itemCode);
        }
        if ($machineUserId) {
            $dicQuery->where('user_id', $machineUserId);
        }
        if ($plant === 'karawang') {
            $dicQuery->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $dicQuery->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }
        $dailyData = $dicQuery->get();

        // 4. Aggregate by Shift (1, 2, 3)
        $shiftResults = [];

        for ($s = 1; $s <= 3; $s++) {
            $shiftDics = $dailyData->where('shift', $s);
            $shiftAdjusts = array_values(array_filter($processedAdjustLogs, fn($l) => $l['shift'] === $s));
            $shiftMoulds = array_values(array_filter($processedMouldLogs, fn($l) => $l['shift'] === $s));

            $target = 0;
            $actual = 0;
            $ng = 0;
            $ngBreakdownShift = [];

            foreach ($shiftDics as $daily) {
                foreach ($daily->hourlyRemarks as $hourly) {
                    $target += $hourly->target ?? 0;
                    $actual += $hourly->actual_production ?? 0;

                    if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
                        foreach ($hourly->ngDetails as $ngDetail) {
                            $ngQty = $ngDetail->ng_quantity ?? 0;
                            $ng += $ngQty;
                            $typeName = $ngDetail->ngType->ng_type ?? 'Unknown';
                            if (!isset($ngBreakdownShift[$typeName])) {
                                $ngBreakdownShift[$typeName] = 0;
                            }
                            $ngBreakdownShift[$typeName] += $ngQty;
                        }
                    }
                }
            }

            // Sort top defect types
            arsort($ngBreakdownShift);
            $topNgTypes = [];
            foreach (array_slice($ngBreakdownShift, 0, 4, true) as $tName => $tQty) {
                $topNgTypes[] = [
                    'name' => $tName,
                    'quantity' => $tQty,
                    'percent' => $ng > 0 ? round(($tQty / $ng) * 100, 1) : 0,
                ];
            }

            $totalProduction = $actual + $ng;
            $ngRate = $totalProduction > 0 ? round(($ng / $totalProduction) * 100, 2) : 0;
            $achievementRate = $target > 0 ? round(($actual / $target) * 100, 1) : 0;

            // Distinct PICs for Adjuster & Mould Change
            $distinctAdjusters = array_values(array_unique(array_filter(array_column($shiftAdjusts, 'pic'))));
            $distinctMouldChangers = array_values(array_unique(array_filter(array_column($shiftMoulds, 'pic'))));

            $totalAdjustDuration = array_sum(array_column($shiftAdjusts, 'duration_minutes'));
            $totalMouldDuration = array_sum(array_column($shiftMoulds, 'duration_minutes'));

            $shiftResults[$s] = [
                'shift_number' => $s,
                'name' => $shiftDefs[$s]['name'],
                'time_range' => $shiftDefs[$s]['time'],
                'theme' => $shiftDefs[$s]['theme'],
                'adjusters' => $distinctAdjusters,
                'adjusters_str' => !empty($distinctAdjusters) ? implode(', ', $distinctAdjusters) : 'No Adjuster Logged',
                'mould_changers' => $distinctMouldChangers,
                'mould_changers_str' => !empty($distinctMouldChangers) ? implode(', ', $distinctMouldChangers) : 'No Mould Changer Logged',
                'adjust_count' => count($shiftAdjusts),
                'adjust_duration_minutes' => $totalAdjustDuration,
                'mould_change_count' => count($shiftMoulds),
                'mould_change_duration_minutes' => $totalMouldDuration,
                'total_setup_minutes' => $totalAdjustDuration + $totalMouldDuration,
                'total_target' => $target,
                'total_actual' => $actual,
                'total_ng' => $ng,
                'ng_rate' => $ngRate,
                'achievement_rate' => $achievementRate,
                'top_ng_types' => $topNgTypes,
                'adjust_logs' => $shiftAdjusts,
                'mould_change_logs' => $shiftMoulds,
            ];
        }

        return [
            'shifts' => $shiftResults,
            'all_logs' => $allActivityLogs,
            'total_adjust_count' => count($processedAdjustLogs),
            'total_mould_change_count' => count($processedMouldLogs),
            'total_setup_time_minutes' => array_sum(array_column($processedAdjustLogs, 'duration_minutes')) + array_sum(array_column($processedMouldLogs, 'duration_minutes')),
        ];
    }

    /**
     * Get Daily NG Trend per Adjuster Line Chart Data
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $itemCode
     * @param string|null $machineUserId
     * @param string|null $plant
     * @return array
     */
    public function getAdjusterNgTrendChartData(
        Carbon $startDate,
        Carbon $endDate,
        ?string $itemCode = null,
        ?string $machineUserId = null,
        ?string $plant = null
    ): array {
        $period = CarbonPeriod::create($startDate, $endDate);
        $dateLabels = [];
        $dateStrings = [];

        foreach ($period as $date) {
            $dateLabels[] = $date->format('d M');
            $dateStrings[] = $date->format('Y-m-d');
        }

        $windowStart = $startDate->copy()->startOfDay()->setTime(7, 30, 0);
        $windowEnd = $endDate->copy()->addDay()->startOfDay()->setTime(7, 30, 0);

        // 1. Query Adjust Machine Logs
        $adjustQuery = \App\Models\AdjustMachineLog::query()
            ->with(['user:id,name'])
            ->where('created_at', '>=', $windowStart->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $windowEnd->format('Y-m-d H:i:s'));

        if ($itemCode) {
            $adjustQuery->where('item_code', $itemCode);
        }
        if ($machineUserId) {
            $adjustQuery->where('user_id', $machineUserId);
        }
        if ($plant === 'karawang') {
            $adjustQuery->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $adjustQuery->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }
        $adjustLogs = $adjustQuery->get();

        // Map shift adjusters: $shiftAdjustersMap[date][shift] = [pic1, pic2]
        $shiftAdjustersMap = [];
        $allAdjusterNames = [];
        $adjusterStats = [];

        foreach ($adjustLogs as $log) {
            $pic = trim($log->pic ?? '');
            if (empty($pic)) continue;

            $time = Carbon::parse($log->created_at);
            $timeStr = $time->format('H:i:s');
            if ($timeStr >= '07:30:00' && $timeStr < '15:30:00') {
                $shift = 1;
                $prodDate = $time->format('Y-m-d');
            } elseif ($timeStr >= '15:30:00' && $timeStr < '23:30:00') {
                $shift = 2;
                $prodDate = $time->format('Y-m-d');
            } else {
                $shift = 3;
                $prodDate = ($timeStr < '07:30:00') ? $time->copy()->subDay()->format('Y-m-d') : $time->format('Y-m-d');
            }

            if (!isset($shiftAdjustersMap[$prodDate][$shift])) {
                $shiftAdjustersMap[$prodDate][$shift] = [];
            }
            if (!in_array($pic, $shiftAdjustersMap[$prodDate][$shift])) {
                $shiftAdjustersMap[$prodDate][$shift][] = $pic;
            }

            if (!in_array($pic, $allAdjusterNames)) {
                $allAdjusterNames[] = $pic;
            }

            if (!isset($adjusterStats[$pic])) {
                $adjusterStats[$pic] = [
                    'adjust_count' => 0,
                    'adjust_minutes' => 0,
                ];
            }
            $adjusterStats[$pic]['adjust_count']++;
            if ($log->end_time) {
                $adjusterStats[$pic]['adjust_minutes'] += Carbon::parse($log->created_at)->diffInMinutes(Carbon::parse($log->end_time), true);
            }
        }

        // 2. Query Daily Item Codes
        $dicQuery = DailyItemCode::query()
            ->with(['hourlyRemarks.ngDetails'])
            ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($itemCode) {
            $dicQuery->where('item_code', $itemCode);
        }
        if ($machineUserId) {
            $dicQuery->where('user_id', $machineUserId);
        }
        if ($plant === 'karawang') {
            $dicQuery->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', 'K%');
            });
        } elseif ($plant === 'kbn') {
            $dicQuery->whereHas('user', function ($q) {
                $q->where('name', 'NOT LIKE', 'K%');
            });
        }
        $dailyItemCodes = $dicQuery->get();

        // Group daily item codes by date & shift
        $shiftProductionMap = [];
        foreach ($dailyItemCodes as $dic) {
            $dStr = $dic->start_date;
            $s = (int)$dic->shift;
            if (!$s) $s = 1;

            if (!isset($shiftProductionMap[$dStr][$s])) {
                $shiftProductionMap[$dStr][$s] = ['actual' => 0, 'ng' => 0];
            }

            foreach ($dic->hourlyRemarks as $hourly) {
                $shiftProductionMap[$dStr][$s]['actual'] += $hourly->actual_production ?? 0;
                if ($hourly->ngDetails && $hourly->ngDetails->count() > 0) {
                    foreach ($hourly->ngDetails as $ngDetail) {
                        $shiftProductionMap[$dStr][$s]['ng'] += $ngDetail->ng_quantity ?? 0;
                    }
                }
            }
        }

        // 3. Calculate daily NG per Adjuster
        $adjusterDailyNg = [];
        $adjusterDailyActual = [];

        foreach ($allAdjusterNames as $adj) {
            $adjusterDailyNg[$adj] = array_fill_keys($dateStrings, 0);
            $adjusterDailyActual[$adj] = array_fill_keys($dateStrings, 0);
        }

        foreach ($shiftProductionMap as $dStr => $shifts) {
            if (!in_array($dStr, $dateStrings)) continue;

            foreach ($shifts as $sNum => $prod) {
                $shiftNg = $prod['ng'];
                $shiftActual = $prod['actual'];
                $adjustersInShift = $shiftAdjustersMap[$dStr][$sNum] ?? [];

                if (!empty($adjustersInShift)) {
                    $count = count($adjustersInShift);
                    $splitNg = round($shiftNg / $count);
                    $splitActual = round($shiftActual / $count);

                    foreach ($adjustersInShift as $adj) {
                        if (isset($adjusterDailyNg[$adj][$dStr])) {
                            $adjusterDailyNg[$adj][$dStr] += $splitNg;
                            $adjusterDailyActual[$adj][$dStr] += $splitActual;
                        }
                    }
                }
            }
        }

        // 4. Build Datasets for Chart.js
        $palette = [
            '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', 
            '#06b6d4', '#f97316', '#14b8a6', '#6366f1', '#84cc16'
        ];

        $datasets = [];
        $adjusterSummaries = [];
        $colorIdx = 0;

        foreach ($allAdjusterNames as $adj) {
            $color = $palette[$colorIdx % count($palette)];
            $ngValues = array_values($adjusterDailyNg[$adj]);
            $actualValues = array_values($adjusterDailyActual[$adj]);

            $totalNg = array_sum($ngValues);
            $totalActual = array_sum($actualValues);
            $totalProd = $totalActual + $totalNg;
            $ngRate = $totalProd > 0 ? round(($totalNg / $totalProd) * 100, 2) : 0;

            $datasets[] = [
                'label' => $adj,
                'data' => $ngValues,
                'borderColor' => $color,
                'backgroundColor' => $color,
                'fill' => false,
                'tension' => 0.3,
                'borderWidth' => 2.5,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,
            ];

            $adjusterSummaries[] = [
                'name' => $adj,
                'color' => $color,
                'total_ng' => $totalNg,
                'total_actual' => $totalActual,
                'ng_rate' => $ngRate,
                'adjust_count' => $adjusterStats[$adj]['adjust_count'] ?? 0,
                'adjust_minutes' => round($adjusterStats[$adj]['adjust_minutes'] ?? 0, 1),
            ];

            $colorIdx++;
        }

        // Sort adjuster summaries by total NG descending
        usort($adjusterSummaries, function ($a, $b) {
            return $b['total_ng'] <=> $a['total_ng'];
        });

        return [
            'labels' => $dateLabels,
            'datasets' => $datasets,
            'adjuster_summaries' => $adjusterSummaries,
            'has_data' => count($datasets) > 0,
        ];
    }
}