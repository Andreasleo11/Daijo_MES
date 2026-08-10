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

    /*
    |--------------------------------------------------------------------------
    | Second Process Production Lines
    |--------------------------------------------------------------------------
    |
    | Centralized mapping of URL slugs to display names for SP shop floor lines.
    |
    */
    'sp_lines' => [
        'line-a'   => 'Line A',
        'line-b'   => 'Line B',
        'line-c'   => 'Line C',
        'line-d'   => 'Line D',
        'buffing'  => 'Area Buffing',
        'amplas'   => 'Area Amplas/Treatment',
        'packing'  => 'Area Packing',
        'assy'     => 'Area Assy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Chemical / Material Parameter Processes
    |--------------------------------------------------------------------------
    |
    | Processes that require Paint Code, Thinner Code, Ink Code, and Viscosity
    | fields in First Piece Inspection forms.
    |
    */
    'chemical_processes' => [
        'Painting',
        'Printing',
        'Silk Screen',
        'Tampoprint',
        'Cat',
    ],
];
