<?php

namespace App\Exports;

use App\Models\Setting\HolidaySchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HolidayScheduleExport implements FromCollection, WithHeadings
{
    /**
     * Return the collection of data to export
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            [
                'date' => '01/12/2024`',
                'description' => 'Example Holiday 1',
                'injection' => 'Full',
                'second_process' => 'Full',
                'assembly' => 'Full',
                'moulding' => 'Full',
            ],
        ]);
    }

    /**
     * Define the headings for the Excel file
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Injection',
            'Second Process',
            'Assembly',
            'Moulding',
        ];
    }
}
