<?php

namespace App\Services;

use App\Models\SecondProcessReport;
use App\Models\SecondProcessNgRecord;
use App\Models\SecondProcessTrouble;
use App\Models\SpProductionSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecondProcessReportSyncBridge
{
    /**
     * Synchronize an approved SpProductionSession into the legacy SecondProcessReport schema.
     */
    public function syncSessionToLegacyReport(SpProductionSession $session): SecondProcessReport
    {
        return DB::transaction(function () use ($session) {
            $session->loadMissing([
                'workOrder',
                'operator',
                'approvedBy',
                'rejectEntries',
                'downtimeEntries',
                'reworkEntries',
                'inputEntries'
            ]);

            $wo = $session->workOrder;

            $dateStr = $session->started_at ? $session->started_at->format('Y-m-d') : now()->format('Y-m-d');
            $unitLine = $session->unit_line ?: ($wo->unit_line ?? 'Line 1');
            $shift = $session->shift ?: ($wo->shift ?? '1');
            $partNumber = $wo->part_number ?? '-';

            // Find or create matching legacy report
            $report = SecondProcessReport::updateOrCreate(
                [
                    'date' => $dateStr,
                    'unit_line' => $unitLine,
                    'shift' => $shift,
                    'part_number' => $partNumber,
                ],
                [
                    'process_prod' => $wo->process_prod ?? 'Second Process',
                    'status' => 'Approved',
                    'model' => $wo->model ?? '-',
                    'part_name' => $wo->part_name ?? '-',
                    'customer' => $wo->customer ?? '-',
                    'target_per_hour' => (int) ceil(($wo->target_qty ?? 0) / 8),
                    'jml_input_wip' => $session->total_input,
                    'repairan' => $session->total_rework_recovered,
                    'jumlah_output' => $session->total_good + $session->total_reject,
                    'jumlah_ok' => $session->total_good,
                    'jumlah_ng' => $session->total_reject,
                    'ng_prosentase' => $session->yield > 0 ? round(100 - $session->yield, 2) : 0,
                    'jml_ng_lebur' => $session->total_scrap,
                    'leader_name' => $session->operator->name ?? null,
                    'leader_signed_at' => $session->finished_at,
                    'acknowledged_by_name' => $session->approvedBy->name ?? null,
                    'acknowledged_signed_at' => $session->approved_at,
                ]
            );

            // Sync NG Defect Records
            $report->ngRecords()->delete();
            $groupedRejects = $session->rejectEntries->groupBy('defect_type');
            foreach ($groupedRejects as $defectType => $entries) {
                $totalQty = $entries->sum('quantity');
                $causes = $entries->pluck('cause')->filter()->implode(', ');

                SecondProcessNgRecord::create([
                    'report_id' => $report->id,
                    'ng_category' => 'Defect',
                    'ng_name' => $defectType,
                    'total_ng' => $totalQty,
                    'remark' => $causes,
                ]);
            }

            // Sync Downtime Troubles
            $report->troubles()->delete();
            foreach ($session->downtimeEntries as $dt) {
                $duration = 0;
                if ($dt->start_time && $dt->resume_time) {
                    $start = Carbon::parse($dt->start_time);
                    $resume = Carbon::parse($dt->resume_time);
                    $duration = $start->diffInMinutes($resume);
                }

                SecondProcessTrouble::create([
                    'report_id' => $report->id,
                    'masalah' => $dt->reason,
                    'penyebab' => $dt->remarks ?: $dt->reason,
                    'loss_time_minutes' => $duration,
                    'category' => 'Downtime',
                ]);
            }

            return $report;
        });
    }

    /**
     * Handle reversion when a supervisor sends a report back for correction.
     */
    public function handleSessionReversion(SpProductionSession $session): void
    {
        $wo = $session->workOrder;
        if (!$wo) return;

        $dateStr = $session->started_at ? $session->started_at->format('Y-m-d') : now()->format('Y-m-d');
        $unitLine = $session->unit_line ?: $wo->unit_line;
        $shift = $session->shift ?: $wo->shift;

        $report = SecondProcessReport::where([
            'date' => $dateStr,
            'unit_line' => $unitLine,
            'shift' => $shift,
            'part_number' => $wo->part_number,
        ])->first();

        if ($report) {
            $report->update(['status' => 'Draft']);
        }
    }
}
