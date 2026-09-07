<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MES Operating Timezone (UTC+7 / WIB)
    |--------------------------------------------------------------------------
    |
    | Define the primary operational timezone for shop floor logs and shifts.
    |
    */
    'timezone' => env('MES_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Factory Shift Schedules
    |--------------------------------------------------------------------------
    |
    | Define the start and end times for each shift.
    | This is used by dashboards to auto-detect the current active shift.
    | Times should be in 'H:i' format.
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
    | Second Process Active Shifts
    |--------------------------------------------------------------------------
    |
    | Define active operating shifts for Second Process (1 to 2 shifts).
    |
    */
    'sp_shifts' => [
        1 => [
            'name'  => 'Shift 1',
            'start' => '07:30',
            'end'   => '15:30',
        ],
        2 => [
            'name'  => 'Shift 2',
            'start' => '15:30',
            'end'   => '23:30',
        ]
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

    /*
    |--------------------------------------------------------------------------
    | Second Process Manpower Roles
    |--------------------------------------------------------------------------
    |
    | Standard worker roles/positions for Second Process production lines,
    | aligned with legacy SecondProcessReport manpower schema.
    |
    */
    'sp_manpower_roles' => [
        'loading'  => 'Loading / Input',
        'sprayer'  => 'Sprayer',
        'checker'  => 'Checker',
        'qc'       => 'QC',
        'packing'  => 'Packing',
        'operator' => 'Operator',
        'leader'   => 'Leader',
    ],
];
