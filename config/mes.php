<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Factory Shift Schedules
    |--------------------------------------------------------------------------
    |
    | Define the start and end times for each shift.
    | This is used by dashboards to auto-detect the current active shift.
    | Times should be in 'H:i' format. The timezone used is defined in app.timezone (Asia/Jakarta).
    |
    */
    'shifts' => [
        1 => [
            'name' => 'Shift 1',
            'start' => '07:30',
            'end' => '15:30',
        ],
        2 => [
            'name' => 'Shift 2',
            'start' => '15:30',
            'end' => '23:30',
        ],
        3 => [
            'name' => 'Shift 3',
            'start' => '23:30',
            'end' => '07:30',
        ],
    ],
];
