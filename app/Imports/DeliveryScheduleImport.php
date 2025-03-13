<?php

namespace App\Imports;

use App\Models\Delivery\DeliveryScheduleNew;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;


class DeliveryScheduleImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new DeliveryScheduleNew([
            'code'              => $this->generateUniqueCode(),
            'so_number'         => $row['so_number'] ?? null,
            'customer_code'     => $row['customer_code'] ?? null,
            'delivery_date'     => $this->convertExcelDate($row['delivery_date']),
            'item_code'         => $row['item_code'],
            'delivery_quantity' => $row['delivery_quantity'],
        ]);
        
    }

    private function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(6)); // Generates a 6-character random uppercase string
        } while (DeliveryScheduleNew::where('code', $code)->exists());

        return $code;
    }

    private function convertExcelDate($excelDate)
    {
        // Check if date is numeric (Excel stores dates as numbers)
        if (is_numeric($excelDate)) {
            return Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate)->format('Y-m-d'));
        }

        // Otherwise, assume it's a valid date string and return it
        return Carbon::parse($excelDate)->format('Y-m-d');
    }
    
}

