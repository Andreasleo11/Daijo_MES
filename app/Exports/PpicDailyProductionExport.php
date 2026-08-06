<?php

namespace App\Exports;

use App\Models\DailyItemCode;
use App\Models\HourlyRemark;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PpicDailyProductionExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected string $date;
    protected ?int $machineId;

    public function __construct(string $date, ?int $machineId = null)
    {
        $this->date = $date;
        $this->machineId = $machineId;
    }

    public function title(): string
    {
        $formattedDate = Carbon::parse($this->date)->format('d-M-Y');
        return "Prod_Daily_{$formattedDate}";
    }

    public function view(): View
    {
        $query = DailyItemCode::whereDate('schedule_date', $this->date)
            ->with(['user', 'masterItem.customer', 'hourlyRemarks', 'scannedData']);

        if ($this->machineId) {
            $query->where('user_id', $this->machineId);
        }

        $dailyPlans = $query->get()->sortBy(function($plan) {
            return $plan->user?->name ?? 'ZZZ';
        });

        // Process data grouped by Machine & Part No
        $reportData = [];
        $no = 1;

        foreach ($dailyPlans as $plan) {
            $machineCode = $plan->user?->name ?? 'MC-UNK';
            $masterItem  = $plan->masterItem;
            $customer    = $masterItem?->customer?->customer_name ?? $masterItem?->customer_code ?? '-';
            $partNo      = $masterItem?->part_no ?? $plan->item_code;
            $partName    = $masterItem?->part_name ?? $masterItem?->item_name ?? '-';

            // Extract Machine Tonage (MCT) e.g. "110A" -> "110T", "150C" -> "150T", "300A" -> "300T"
            preg_match('/(\d+)/', $machineCode, $matches);
            $mct = !empty($matches[1]) ? $matches[1] . 'T' : '100T';

            // Calculate shift 1, 2, 3 quantities from hourly remarks
            $shift1Qty = $plan->hourlyRemarks->where('dailyItemCode.shift', 1)->sum('actual_production');
            $shift2Qty = $plan->hourlyRemarks->where('dailyItemCode.shift', 2)->sum('actual_production');
            $shift3Qty = $plan->hourlyRemarks->where('dailyItemCode.shift', 3)->sum('actual_production');

            // If hourlyRemarks actual_production is empty, fallback to scanned data
            if ($shift1Qty + $shift2Qty + $shift3Qty == 0) {
                $totalOkScanned = $plan->scannedData->sum('quantity');
                if ($plan->shift == 1) $shift1Qty = $totalOkScanned;
                elseif ($plan->shift == 2) $shift2Qty = $totalOkScanned;
                elseif ($plan->shift == 3) $shift3Qty = $totalOkScanned;
            }

            $totalShift = $shift1Qty + $shift2Qty + $shift3Qty;

            // Target per hour (from hourly remark target or standard)
            $targetPerHour = (int) ($plan->hourlyRemarks->avg('target') ?: 80);

            // 1. Plan Hours: Default 24 Jam per hari, atau jam slot terencana
            $filledSlotsCount = $plan->hourlyRemarks->count();
            $planHours = $filledSlotsCount > 0 ? $filledSlotsCount : 24;

            // 2. Actual Hours: Jam Actual Berjalan (Running Hours) = Total Qty / Target per Jam
            $actualHours = 0.0;
            if ($targetPerHour > 0 && $totalShift > 0) {
                $actualHours = round($totalShift / $targetPerHour, 2);
            } else {
                // Fallback: hitung jam slot yang memiliki output > 0
                $actualHours = (float) $plan->hourlyRemarks->filter(fn($r) => $r->actual_production > 0)->count();
            }

            // 3. Downtime Hours: Selisih Jam Plan dengan Jam Actual (atau jam kendala)
            $downtimeHours = round(max(0, $planHours - $actualHours), 2);

            $reportData[] = [
                'no'           => $no++,
                'date'         => Carbon::parse($plan->schedule_date)->format('d-M-y'),
                'customer'     => strtoupper($customer),
                'mc'           => $machineCode,
                'part_no'      => $partNo,
                'part_name'    => $partName,
                'shift_1'      => $shift1Qty,
                'shift_2'      => $shift2Qty,
                'shift_3'      => $shift3Qty,
                'total_shift'  => $totalShift,
                'mct'          => $mct,
                'target_h'     => $targetPerHour,
                'plan'         => $planHours,
                'actual'       => $actualHours,
                'downtime'     => $downtimeHours,
            ];
        }

        return view('exports.ppic_daily_production', [
            'date'       => Carbon::parse($this->date)->format('d-M-Y'),
            'reportData' => $reportData,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style table header fonts
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true, 'size' => 10]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }
}
