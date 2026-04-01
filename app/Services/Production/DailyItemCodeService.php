<?php

namespace App\Services\Production;

use App\Models\DailyItemCode;
use App\Models\MasterListItem;
use App\Models\SpkMaster;
use App\Models\User;
use Illuminate\Support\Collection;

class DailyItemCodeService
{
    /**
     * Mengambil daftar mesin yang bertugas sebagai OPERATOR
     * beserta task harian mereka yang belum selesai (is_done = 0 / null).
     */
    public function getOperatorMachines(): Collection
    {
        return User::whereHas('role', function ($query) {
            $query->where('name', 'OPERATOR');
        })->with([
            'dailyItemCode' => function ($query) {
                $query->where(function ($query) {
                    $query->where('is_done', 0)->orWhereNull('is_done');
                });
            },
        ])->get();
    }

    /**
     * Transform username mesin sesuai pola (menghapus leading zero tapi mempertahankan abjad)
     */
    public function transformUsername(string $username): string
    {
        if (preg_match('/^0*(\d+)([A-Z])$/', $username, $matches)) {
            return $matches[1] . $matches[2];
        }
        return $username;
    }

    /**
     * Hitung sisa stok SPK, planned, completed, dan estimasi loss package.
     */
    public function calculateItemStats(string $itemCode, int $quantity): array
    {
        $datas = SpkMaster::where('item_code', $itemCode)->get();
        $master = MasterListItem::where('item_code', $itemCode)->first();

        if (!$master) {
            throw new \InvalidArgumentException('Invalid item code');
        }

        $stanpack = $master->standart_packaging_list;

        $totalPlannedQuantity = $datas->sum('planned_quantity');
        $totalCompletedQuantity = $datas->sum('completed_quantity');

        // Kalkulasi efisien pakai modulo (pastikan stanpack > 0 untuk mencegah division by zero)
        $lossPackageQuantity = ($stanpack > 0 && ($quantity % $stanpack) !== 0) 
            ? ($quantity % $stanpack) 
            : 0;

        $maxQuantity = $totalPlannedQuantity - $totalCompletedQuantity;

        return [
            'total_planned_quantity'   => $totalPlannedQuantity,
            'total_completed_quantity' => $totalCompletedQuantity,
            'loss_package_quantity'    => $lossPackageQuantity,
            'max_quantity'             => $maxQuantity,
        ];
    }

    /**
     * Validasi waktu shift (berurutan dan tidak nabrak)
     * Mengembalikan array error key-value jika ada invalidasi, null jika valid.
     */
    public function validateShiftsSequence(array $validatedData): ?array
    {
        foreach ($validatedData['shifts'] as $index => $shift) {
            $startDate = $validatedData['start_dates'][$shift][0]; // First entry for shift
            $endDate   = $validatedData['end_dates'][$shift][0];
            $startTime = $validatedData['start_times'][$shift][0];
            $endTime   = $validatedData['end_times'][$shift][0];

            // 1. End time must be after start time on the same date
            if ($startDate == $endDate && strtotime($endTime) <= strtotime($startTime)) {
                return [
                    'field' => "end_times.$shift",
                    'message' => 'End time must be after the start time when the start and end dates are the same for shift ' . $shift
                ];
            }

            // 2. Shifts must be sequential
            if ($index > 0) {
                $previousShift   = $validatedData['shifts'][$index - 1];
                $previousEndTime = strtotime($validatedData['end_times'][$previousShift][0]);
                $previousEndDate = strtotime($validatedData['end_dates'][$previousShift][0]);
                $currentStartTime = strtotime($startTime);
                $currentStartDate = strtotime($startDate);

                // Check if the current shift starts after the previous shift ends
                if ($previousEndDate > $currentStartDate || ($previousEndDate == $currentStartDate && $previousEndTime >= $currentStartTime)) {
                    return [
                        'field' => "start_times.$shift",
                        'message' => 'Start time for shift ' . $shift . ' must be after the end time of shift ' . $previousShift
                    ];
                }
            }
        }
        return null; // Valid
    }

    /**
     * Iterasi insert item codes sekaligus potong loss package dan limit qty.
     */
    public function assignItemCodes(array $validatedData): void
    {
        foreach ($validatedData['shifts'] as $shift) {
            foreach ($validatedData['item_codes'][$shift] as $key => $itemCode) {
                $quantity  = $validatedData['quantities'][$shift][$key];
                $startDate = $validatedData['start_dates'][$shift][$key];
                $endDate   = $validatedData['end_dates'][$shift][$key];
                $startTime = $validatedData['start_times'][$shift][$key];
                $endTime   = $validatedData['end_times'][$shift][$key];
                $remark    = $validatedData['remarks'][$shift][$key] ?? null;

                $datas = SpkMaster::where('item_code', $itemCode)->get();
                $master = MasterListItem::where('item_code', $itemCode)->first();
                
                // Enhanced Logic: optimasi untuk mencegah error DB kalau master tidak ditemukan
                if (!$master) continue;

                if ($master->pair !== null && $master->pair != 0) {
                    $quantity *= 2;
                }
                
                $stanpack = (int) $master->standart_packaging_list;

                $totalPlannedQuantity = $datas->sum('planned_quantity');
                $totalCompletedQuantity = $datas->sum('completed_quantity');

                $loss_package_quantity = ($stanpack > 0 && ($quantity % $stanpack) !== 0) 
                    ? ($quantity % $stanpack) 
                    : 0;

                $max_quantity = $totalPlannedQuantity - $totalCompletedQuantity;

                if ($quantity > $max_quantity) {
                    throw new \InvalidArgumentException("Quantity of $itemCode exceeds SPK with a maximum of $max_quantity.");
                }

                $adjustedQuantity = $quantity;

                // Cek unresolved loss package dari data sebelumnya
                $previousDailyItemCode = DailyItemCode::where('user_id', $validatedData['machine_id'])
                    ->where('item_code', $itemCode)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($previousDailyItemCode && $previousDailyItemCode->loss_package_quantity > 0) {
                    $adjustedQuantity = $quantity - $previousDailyItemCode->loss_package_quantity;
                }

                DailyItemCode::create([
                    'schedule_date'         => $validatedData['schedule_date'],
                    'user_id'               => $validatedData['machine_id'],
                    'item_code'             => $itemCode,
                    'quantity'              => $quantity,
                    'loss_package_quantity' => $loss_package_quantity,
                    'final_quantity'        => $quantity,
                    'actual_quantity'       => $adjustedQuantity,
                    'start_time'            => $startTime,
                    'end_time'              => $endTime,
                    'start_date'            => $startDate,
                    'end_date'              => $endDate,
                    'shift'                 => $shift,
                    'remark'                => $remark,
                ]);
            }
        }
    }
}
