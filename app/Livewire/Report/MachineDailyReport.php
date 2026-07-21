<?php

namespace App\Livewire\Report;

use App\Models\DailyItemCode;
use App\Models\HourlyRemark;
use App\Models\ProductionNgDetail;
use App\Models\ProductionNgType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class MachineDailyReport extends Component
{
    public $selectedDate;
    public $selectedMachineId;
    public bool $isPpicView = false;
    
    // Query string support for easy bookmarking and state preservation
    protected $queryString = [
        'selectedDate' => ['except' => ''],
        'selectedMachineId' => ['except' => ''],
    ];

    public static $shiftSlots = [
        1 => [
            1 => ['start' => '07:30:00', 'end' => '08:30:00'],
            2 => ['start' => '08:30:00', 'end' => '09:30:00'],
            3 => ['start' => '09:30:00', 'end' => '10:30:00'],
            4 => ['start' => '10:30:00', 'end' => '11:30:00'],
            5 => ['start' => '11:30:00', 'end' => '12:30:00'],
            6 => ['start' => '12:30:00', 'end' => '13:30:00'],
            7 => ['start' => '13:30:00', 'end' => '14:30:00'],
            8 => ['start' => '14:30:00', 'end' => '15:30:00'],
        ],
        2 => [
            1 => ['start' => '15:30:00', 'end' => '16:30:00'],
            2 => ['start' => '16:30:00', 'end' => '17:30:00'],
            3 => ['start' => '17:30:00', 'end' => '18:30:00'],
            4 => ['start' => '18:30:00', 'end' => '19:30:00'],
            5 => ['start' => '19:30:00', 'end' => '20:30:00'],
            6 => ['start' => '20:30:00', 'end' => '21:30:00'],
            7 => ['start' => '21:30:00', 'end' => '22:30:00'],
            8 => ['start' => '22:30:00', 'end' => '23:30:00'],
        ],
        3 => [
            1 => ['start' => '23:30:00', 'end' => '00:30:00'],
            2 => ['start' => '00:30:00', 'end' => '01:30:00'],
            3 => ['start' => '01:30:00', 'end' => '02:30:00'],
            4 => ['start' => '02:30:00', 'end' => '03:30:00'],
            5 => ['start' => '03:30:00', 'end' => '04:30:00'],
            6 => ['start' => '04:30:00', 'end' => '05:30:00'],
            7 => ['start' => '05:30:00', 'end' => '06:30:00'],
            8 => ['start' => '06:30:00', 'end' => '07:30:00'],
        ],
    ];

    public function mount()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Determine mode based on route name
        $this->isPpicView = request()->routeIs('ppic.machine-daily-report');

        if ($this->isPpicView) {
            // Access control for PPIC / Admin view
            if (!$user->hasRole('PPIC') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER-ADMIN')) {
                abort(403, 'Unauthorized. Access is restricted to PPIC and Administrators.');
            }
            
            // Set default selected date if empty
            if (!$this->selectedDate) {
                $this->selectedDate = Carbon::today('Asia/Jakarta')->format('Y-m-d');
            }

            // Load default machine if empty
            if (!$this->selectedMachineId) {
                $firstMachine = User::whereHas('role', function($q) {
                    $q->where('name', 'OPERATOR');
                })->orderBy('name')->first();
                $this->selectedMachineId = $firstMachine?->id;
            }
        } else {
            // Access control for Operator view
            if (!$user->hasRole('OPERATOR') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER-ADMIN')) {
                abort(403, 'Unauthorized. Access is restricted to Operators.');
            }

            // Operators are locked to their own user/machine record
            $this->selectedMachineId = $user->id;
            
            if (!$this->selectedDate) {
                $this->selectedDate = Carbon::today('Asia/Jakarta')->format('Y-m-d');
            }
        }
    }

    public function render()
    {
        // 1. Get Machines list (only needed for dropdown in PPIC view)
        $machines = $this->isPpicView
            ? User::whereHas('role', fn($q) => $q->where('name', 'OPERATOR'))->orderBy('name')->get()
            : collect();

        $selectedMachine = User::find($this->selectedMachineId);

        // 2. Fetch daily plans (DailyItemCodes) for that date and machine
        $dailyPlans = [];
        $dicIds = collect();
        if ($this->selectedMachineId) {
            $dailyPlans = DailyItemCode::where('user_id', $this->selectedMachineId)
                ->whereDate('schedule_date', $this->selectedDate)
                ->whereHas('hourlyRemarks')
                ->with(['masterItem', 'scannedData'])
                ->orderBy('shift', 'asc')
                ->get();
                
            $dicIds = $dailyPlans->pluck('id');
        }

        // 3. Fetch hourly remarks
        $hourlyRemarks = $dicIds->isNotEmpty()
            ? HourlyRemark::whereIn('dic_id', $dicIds)->with('dailyItemCode')->get()
            : collect();

        // 4. Map Hourly Output per Shift (I, II, III) matching paper form layout
        $hourlyOutput = [1 => [], 2 => [], 3 => []];
        foreach ([1, 2, 3] as $shift) {
            foreach (self::$shiftSlots[$shift] as $hourIdx => $slot) {
                // Find matching remark in DB
                $remark = $hourlyRemarks->first(function($r) use ($shift, $slot) {
                    return $r->dailyItemCode->shift == $shift 
                        && $r->start_time == $slot['start'] 
                        && $r->end_time == $slot['end'];
                });

                $hourlyOutput[$shift][$hourIdx] = [
                    'time' => substr($slot['start'], 0, 5) . ' - ' . substr($slot['end'], 0, 5),
                    'target' => $remark?->target ?? 0,
                    'actual' => $remark?->actual_production ?? 0,
                    'ng' => $remark?->NG ?? 0,
                    'pic' => $remark?->pic ?? '-',
                    'remark' => $remark?->remark ?? '',
                ];
            }
        }

        // 5. Operator names per shift (derived from unique HourlyRemark PICs)
        $operatorNames = [1 => '-', 2 => '-', 3 => '-'];
        foreach ([1, 2, 3] as $shift) {
            $pics = $hourlyRemarks->filter(fn($r) => $r->dailyItemCode->shift == $shift)
                ->pluck('pic')
                ->filter(fn($p) => !empty($p) && $p !== '-')
                ->unique();
            if ($pics->isNotEmpty()) {
                $operatorNames[$shift] = $pics->join(', ');
            }
        }

        // 6. Plastic Part Defective Analysis Matrix
        $ngTypes = ProductionNgType::all();
        $defectMatrix = [1 => [], 2 => [], 3 => []];
        foreach ([1, 2, 3] as $shift) {
            foreach ($ngTypes as $type) {
                $defectMatrix[$shift][$type->id] = 0;
            }
        }

        if ($dicIds->isNotEmpty()) {
            $ngDetails = ProductionNgDetail::whereIn('hourly_remark_id', $hourlyRemarks->pluck('id'))
                ->with('hourlyRemark.dailyItemCode')
                ->get();

            foreach ($ngDetails as $detail) {
                $shift = (int)($detail->hourlyRemark->dailyItemCode->shift ?? 1);
                if (isset($defectMatrix[$shift][$detail->ng_type_id])) {
                    $defectMatrix[$shift][$detail->ng_type_id] += $detail->ng_quantity;
                }
            }
        }

        // 7. Calculate aggregate actual metrics for OK and NG
        $totals = [
            'planned_target' => $dailyPlans->sum('quantity'),
            'actual_ok' => $dailyPlans->flatMap(fn($p) => $p->scannedData)->sum('quantity'),
            'actual_ng' => $hourlyRemarks->sum('NG'),
        ];
        $totals['total_produced'] = $totals['actual_ok'] + $totals['actual_ng'];

        return view('livewire.report.machine-daily-report', [
            'machines' => $machines,
            'selectedMachine' => $selectedMachine,
            'dailyPlans' => $dailyPlans,
            'hourlyOutput' => $hourlyOutput,
            'operatorNames' => $operatorNames,
            'ngTypes' => $ngTypes,
            'defectMatrix' => $defectMatrix,
            'totals' => $totals,
        ]);
    }
}
