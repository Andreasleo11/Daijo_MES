<?php

namespace App\Services;

use App\Models\DailyItemCode;
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