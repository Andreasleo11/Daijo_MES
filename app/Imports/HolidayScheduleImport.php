<?php

namespace App\Imports;

use App\Models\Setting\HolidaySchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HolidayScheduleImport implements ToModel, WithHeadingRow
{
    /**
     * Define the logic for each row being imported.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new HolidaySchedule([
            'date' => \Carbon\Carbon::createFromFormat('Y-m-d', $row['date'])->format('Y-m-d'), // Formatting date if needed
            'description' => $row['description'],
            'injection' => $row['injection'],
            'second_process' => $row['second_process'],
            'assembly' => $row['assembly'],
            'moulding' => $row['moulding'],
        ]);
    }
}
