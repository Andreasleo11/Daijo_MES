<?php

namespace App\Livewire;

use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_BillOfMaterialParent;
use App\Models\Production\PRD_MaterialLog;
use App\Models\Production\PRD_MouldingUserLog;
use Livewire\Component;
use Livewire\WithPagination;

class DashboardAdmin extends Component
{
    use WithPagination;

    public $childs;
    public $materialLogs;
    public $mouldingUserLogs;
    public $completedItems = 0;
    public $overallCompletionPercentage = 0;
    public $totalPendingChildren = 0;

    protected $paginationTheme = 'tailwind'; // Use Tailwind styling

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Remove `$this->parents` because we will paginate it in `render()`
        $this->childs = PRD_BillOfMaterialChild::all();
        $this->materialLogs = PRD_MaterialLog::all();
        $this->mouldingUserLogs = PRD_MouldingUserLog::all();

        $this->calculateStatistics();
    }

    public function calculateStatistics()
    {
        $this->totalPendingChildren = $this->childs->filter(function ($child) {
            return $child->action_type !== 'stockfinish' &&
                   ($child->action_type !== 'buyfinish' || $child->status !== 'Finished');
        })->count();

        $this->completedItems = $this->childs->filter(
            fn($child) => in_array($child->action_type, ['stockfinish']) || $child->status === 'Finished'
        )->count();

        $this->overallCompletionPercentage = PRD_BillOfMaterialParent::all()->map(function ($parent) {
            $totalChildren = $this->childs->where('parent_id', $parent->id)->count();
            $finishedChildren = 0;

            foreach ($this->childs->where('parent_id', $parent->id) as $child) {
                $processCount = $this->materialLogs->where('child_id', $child->id)->count();
                $finishedCount = $this->materialLogs
                    ->where('child_id', $child->id)
                    ->filter(fn($log) => $log->status == 2)
                    ->count();

                if (($child->action_type === 'buyfinish' && $child->status === 'Finished') || $child->action_type === 'stockfinish') {
                    $finishedChildren++;
                } elseif ($processCount > 0 && $finishedCount === $processCount) {
                    $finishedChildren++;
                }
            }

            return $totalChildren > 0 ? ($finishedChildren / $totalChildren) * 100 : 0;
        })->average() ?? 0; // Prevent null error
    }

    public function render()
    {
        return view('livewire.dashboard-admin', [
            'parents' => PRD_BillOfMaterialParent::paginate(1), // Paginate parents (5 per page)
            'childs' => $this->childs,
            'materialLogs' => $this->materialLogs,
            'mouldingUserLogs' => $this->mouldingUserLogs,
            'completedItems' => $this->completedItems,
            'overallCompletionPercentage' => $this->overallCompletionPercentage,
            'totalPendingChildren' => $this->totalPendingChildren,
        ]);
    }
}
