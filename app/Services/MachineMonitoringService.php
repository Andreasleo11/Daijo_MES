<?php

namespace App\Services;

use App\Models\User;
use App\Models\MachineJob;
use App\Models\DailyItemCode;
use App\Models\AdjustMachineLog;
use App\Models\MouldChangeLog;
use App\Models\RepairMachineLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MachineMonitoringService
{
    /**
     * Get statuses for all machines with zone_id
     *
     * @param string|null $zoneId
     * @param string|null $search
     * @return Collection
     */
    public function getMachineStatuses(?string $zoneId = null, ?string $search = null): Collection
    {
        $query = User::with(['zone', 'jobs'])
            ->whereNotNull('zone_id');

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        }

        $machines = $query->get();

        return $machines->map(function ($machine) {
            $statusData = $this->calculateStatusAndTime($machine);
            $productionData = $this->getProductionDetails($machine);

            return array_merge([
                'id' => $machine->id,
                'zone' => $machine->zone->zone_name ?? '-',
                'machine_code' => $machine->username,
                'name' => $machine->name,
            ], $statusData, $productionData);
        });
    }

    /**
     * Calculate status and non-running time
     *
     * @param User $machine
     * @return array
     */
    private function calculateStatusAndTime(User $machine): array
    {
        $now = Carbon::now();
        $status = 'IDLE';
        $timeNotRunning = 0;
        $startTime = null;

        // 1. Check for SETUP (Adjust or Mould Change)
        $setupLog = AdjustMachineLog::where('user_id', $machine->id)
            ->whereNull('end_time')
            ->orderBy('created_at', 'desc')
            ->first() 
            ?? MouldChangeLog::where('user_id', $machine->id)
            ->whereNull('end_time')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($setupLog) {
            $status = 'SETUP';
            $timeNotRunning = $now->diffInMinutes($setupLog->created_at);
            return [
                'status' => $status,
                'total_time_not_running' => $timeNotRunning,
                'start_running' => '-',
            ];
        }

        // 2. Check for REPAIR (IDLE)
        $repairLog = RepairMachineLog::where('user_id', $machine->id)
            ->whereNull('finish_repair')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($repairLog) {
            $status = 'IDLE';
            $timeNotRunning = $now->diffInMinutes($repairLog->created_at);
            return [
                'status' => $status,
                'total_time_not_running' => $timeNotRunning,
                'start_running' => '-',
            ];
        }

        // 3. Check for RUNNING (MachineJob)
        $job = $machine->jobs; // hasOne relation
        if ($job && !empty($job->item_code)) {
            // Find start time from DailyItemCode
            $dic = DailyItemCode::where('user_id', $machine->id)
                ->where('item_code', $job->item_code)
                ->where('is_done', 0)
                ->orderBy('start_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->first();

            return [
                'status' => 'RUNNING',
                'total_time_not_running' => 0,
                'start_running' => $dic ? Carbon::parse($dic->start_date . ' ' . $dic->start_time)->format('d/m/Y H:i') : '-',
            ];
        }

        // 4. Default to IDLE if no job
        // To calculate time not running for "No Job", we look for the last finished Job or Log
        $lastActivity = $this->getLastActivityTime($machine->id);
        if ($lastActivity) {
            $timeNotRunning = $now->diffInMinutes($lastActivity);
        }

        return [
            'status' => 'IDLE',
            'total_time_not_running' => $timeNotRunning,
            'start_running' => '-',
        ];
    }

    /**
     * Get production details (Part, Target, Achieve, Next)
     *
     * @param User $machine
     * @return array
     */
    private function getProductionDetails(User $machine): array
    {
        $job = $machine->jobs;
        $partRunning = $job->item_code ?? '-';
        $targetQty = '-';
        $targetAchieve = '-';
        $nextPart = '-';

        if ($job && !empty($job->item_code)) {
            $dic = DailyItemCode::where('user_id', $machine->id)
                ->where('item_code', $job->item_code)
                ->where('is_done', 0)
                ->orderBy('start_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->first();

            if ($dic) {
                $targetQty = $dic->quantity;
                $targetAchieve = $dic->actual_quantity ?? 0;
            }
        }

        // Get Next Part
        $nextDic = DailyItemCode::where('user_id', $machine->id)
            ->where('is_done', 0)
            ->where(function($q) use ($partRunning) {
                if ($partRunning !== '-') {
                    $q->where('item_code', '!=', $partRunning);
                }
            })
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        if ($nextDic) {
            $nextPart = $nextDic->item_code;
        }

        return [
            'part_running' => $partRunning,
            'target_qty' => $targetQty,
            'target_achieve' => $targetAchieve,
            'next_part_running' => $nextPart,
        ];
    }

    /**
     * Helper to get last activity time to calculate Idle duration
     */
    private function getLastActivityTime(int $userId): ?Carbon
    {
        $lastDic = DailyItemCode::where('user_id', $userId)
            ->where('is_done', 1)
            ->orderBy('updated_at', 'desc')
            ->first();
            
        $lastAdjust = AdjustMachineLog::where('user_id', $userId)
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->first();
            
        $lastMould = MouldChangeLog::where('user_id', $userId)
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->first();
            
        $lastRepair = RepairMachineLog::where('user_id', $userId)
            ->whereNotNull('finish_repair')
            ->orderBy('created_at', 'desc') // finish_repair might not be cast to date
            ->first();

        // Use finish_repair if it's a valid date string
        $repairTime = null;
        if ($lastRepair && $lastRepair->finish_repair) {
            try {
                $repairTime = Carbon::parse($lastRepair->finish_repair);
            } catch (\Exception $e) {
                $repairTime = $lastRepair->created_at; 
            }
        }

        $times = collect([
            $lastDic ? $lastDic->updated_at : null,
            ($lastAdjust && $lastAdjust->end_time) ? Carbon::parse($lastAdjust->end_time) : null,
            ($lastMould && $lastMould->end_time) ? Carbon::parse($lastMould->end_time) : null,
            $repairTime,
        ])->filter()->sortDesc();

        return $times->first();
    }

    /**
     * Get detailed history and logs for a specific machine
     */
    public function getMachineDetailedHistory(int $userId): array
    {
        $recentDics = DailyItemCode::where('user_id', $userId)
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        $recentLogs = collect();

        $adjustLogs = AdjustMachineLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($l) => array_merge($l->toArray(), ['log_type' => 'ADJUST']));

        $mouldLogs = MouldChangeLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($l) => array_merge($l->toArray(), ['log_type' => 'MOULD']));

        $repairLogs = RepairMachineLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($l) => array_merge($l->toArray(), ['log_type' => 'REPAIR']));

        $recentLogs = $recentLogs->concat($adjustLogs)
            ->concat($mouldLogs)
            ->concat($repairLogs)
            ->sortByDesc('created_at')
            ->values()
            ->take(10);

        return [
            'recent_jobs' => $recentDics,
            'recent_logs' => $recentLogs
        ];
    }
}
