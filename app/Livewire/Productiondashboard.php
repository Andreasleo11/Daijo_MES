<?php

namespace App\Livewire;

use App\Services\ProductionDashboardService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class ProductionDashboard extends Component
{
    public $viewType = 'monthly';
    public $year;
    public $month;
    public $week = 1;
    public $itemCode = null;
    public $machineUserId = null;
    
    // For searchable item code
    public $itemCodeSearch = '';
    public $showItemCodeDropdown = false;

    public array $chartData = [];
    public array $summary = [];
    public array $ngBreakdown = [];
    public array $downtimeAnalysis = []; // ✅ NEW
    public array $topRemarks = []; // ✅ NEW

    public array $years = [];
    public array $months = [];
    public array $weeks = [];
    public array $itemCodes = [];
    public array $filteredItemCodes = [];
    public array $machines = [];

    protected ProductionDashboardService $productionService;

    public function boot(ProductionDashboardService $service)
    {
        $this->productionService = $service;
    }

    public function mount()
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->populateFilterOptions();
        $this->loadData();
    }

    private function populateFilterOptions()
    {
        $currentYear = now()->year;

        for ($i = -3; $i <= 1; $i++) {
            $this->years[] = $currentYear + $i;
        }

        $this->months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $this->updateWeeks();
        $this->updateItemCodes();
        
        // ✅ Get users with IDs for machine mapping
        $machineUsers = \App\Models\User::whereIn('name', [
            '0350F', '0450F', '0450G', '0450H', '0450I', '0450J',
            '0550B', '0650D', '0650E', '0850D',
            'K2800A', 'K2100A', 'K1400A', 'K1400B', 'K1400C',
            'K0900A', 'K0900B', 'K0650A', 'K0650B',
            'K0750A', 'K0750B', 'K0450A',
        ])->get(['id', 'name']);
        
        $this->machines = $machineUsers->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name
            ];
        })->sortBy('name')->values()->toArray();
    }

    private function updateItemCodes()
    {
        $this->itemCodes = $this->productionService->getItemCodes($this->year, $this->month);
        $this->filteredItemCodes = $this->itemCodes;
    }

    public function updatedItemCodeSearch()
    {
        if (empty($this->itemCodeSearch)) {
            $this->filteredItemCodes = $this->itemCodes;
            $this->showItemCodeDropdown = false;
        } else {
            $this->filteredItemCodes = array_filter($this->itemCodes, function($code) {
                return stripos($code, $this->itemCodeSearch) !== false;
            });
            $this->showItemCodeDropdown = true;
        }
    }

    public function selectItemCode($code)
    {
        $this->itemCode = $code;
        $this->itemCodeSearch = $code;
        $this->showItemCodeDropdown = false;
        $this->loadData();
    }

    public function clearItemCode()
    {
        $this->itemCode = null;
        $this->itemCodeSearch = '';
        $this->filteredItemCodes = $this->itemCodes;
        $this->showItemCodeDropdown = false;
        $this->loadData();
    }

    public function updatedYear()
    {
        $this->updateWeeks();
        $this->updateItemCodes();
        $this->loadData();
    }

    public function updatedMonth()
    {
        $this->updateWeeks();
        $this->updateItemCodes();
        $this->loadData();
    }

    public function updatedWeek()
    {
        $this->loadData();
    }

    public function updatedViewType()
    {
        $this->loadData();
    }

    public function updatedMachineUserId()
    {
        $this->loadData();
    }

    private function updateWeeks()
    {
        $this->weeks = $this->productionService->getWeeksInMonth($this->year, $this->month);

        if (empty($this->weeks)) {
            $this->week = 1;
            return;
        }

        if (!isset($this->weeks[$this->week - 1])) {
            $this->week = 1;
        }
    }

    public function loadData()
    {
        [$startDate, $endDate] = $this->getDateRange();

        // Existing data
        $data = $this->productionService->getProductionData(
            $startDate,
            $endDate,
            $this->itemCode,
            $this->machineUserId
        );

        $this->chartData = $data['chart_data'] ?? [];
        $this->summary = $data['summary'] ?? [];

        $this->ngBreakdown = $this->productionService->getNgBreakdown(
            $startDate,
            $endDate,
            $this->itemCode,
            $this->machineUserId
        ) ?? [];

        // ✅ NEW: Get downtime analysis
        $this->downtimeAnalysis = $this->productionService->getDowntimeAnalysis(
            $startDate,
            $endDate,
            $this->itemCode,
            $this->machineUserId
        );

        // ✅ NEW: Get top problematic remarks
        $this->topRemarks = $this->productionService->getTopProblematicRemarks(
            $startDate,
            $endDate,
            $this->itemCode,
            $this->machineUserId
        );

        $this->dispatch('chartDataUpdated', chartData: $this->chartData);
    }

    private function getDateRange(): array
    {
        if ($this->viewType === 'weekly' && !empty($this->weeks)) {
            $weekData = $this->weeks[$this->week - 1] ?? $this->weeks[0];
            $startDate = Carbon::parse($weekData['start']);
            $endDate = Carbon::parse($weekData['end']);
        } else {
            $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        }

        return [$startDate, $endDate];
    }

    public function resetFilters()
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->week = 1;
        $this->itemCode = null;
        $this->itemCodeSearch = '';
        $this->machineUserId = null;

        $this->updateWeeks();
        $this->updateItemCodes();
        $this->loadData();
    }

    public function exportPdf()
    {
        return redirect()->route('production-dashboard.pdf', [
            'viewType' => $this->viewType,
            'year' => $this->year,
            'month' => $this->month,
            'week' => $this->week,
            'itemCode' => $this->itemCode,
            'machineUserId' => $this->machineUserId,
        ]);
    }

    public function render()
    {
        return view('livewire.production-dashboard');
    }
}