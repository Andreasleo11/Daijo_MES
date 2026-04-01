<?php

namespace App\Livewire;

use App\Services\MachineMonitoringService;
use App\Models\MasterZone;
use Livewire\Component;
use Livewire\WithPagination;

class MachineStatusMonitor extends Component
{
    use WithPagination;

    public $selectedZone = '';
    public $search = '';
    
    // Drill-down properties
    public $selectedMachineId = null;
    public $machineDetails = null;
    
    // Auto-refresh properties
    public $refreshInterval = 30; // Seconds

    protected MachineMonitoringService $machineService;

    /**
     * Dependency injection in Livewire 3
     */
    public function boot(MachineMonitoringService $service)
    {
        $this->machineService = $service;
    }

    public function selectMachine($id)
    {
        $this->selectedMachineId = $id;
        $this->machineDetails = $this->machineService->getMachineDetailedHistory($id);
    }

    public function closeDetail()
    {
        $this->selectedMachineId = null;
        $this->machineDetails = null;
    }

    /**
     * Get data for rendering
     */
    public function render()
    {
        $machines = $this->machineService->getMachineStatuses(
            $this->selectedZone ?: null, 
            $this->search ?: null
        );

        $zones = MasterZone::all();

        return view('livewire.machine-status-monitor', [
            'machines' => $machines,
            'zones' => $zones,
        ]);
    }
}
