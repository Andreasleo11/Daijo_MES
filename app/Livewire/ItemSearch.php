<?php

namespace App\Livewire;

use App\Models\MasterListItem;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class ItemSearch extends Component
{
    use WithPagination;

    public $search = ''; // Bind this to the search input

    public $showOnlyNoFiles = false;
    public $showAllNoFiles = false;

    public function toggleShowOnlyNoFiles()
    {
        $this->showOnlyNoFiles = !$this->showOnlyNoFiles;
        $this->resetPage(); // reset ke halaman 1 pas filter berubah
    }

    public function toggleShowAllNoFiles()
    {
        $this->showAllNoFiles = !$this->showAllNoFiles;
        $this->showOnlyNoFiles = false; // matikan filter daily saat all aktif
        $this->resetPage();
    }

    public function render()
    {
        $today = Carbon::today()->toDateString(); // atau use now()->toDateString()

        $items = MasterListItem::when($this->showOnlyNoFiles, function ($query) use ($today) {
                $query->doesntHave('files')
                    ->whereIn('item_code', function ($subQuery) use ($today) {
                        $subQuery->select('item_code')
                            ->from('daily_item_codes')
                            ->whereDate('schedule_date', $today);
                    });
            })
            ->when($this->showAllNoFiles, function ($query) {
                $query->doesntHave('files');
             })
            ->where(function ($query) {
                $query->where('item_code', 'like', '%'.$this->search.'%')
                    ->orWhereHas('files', function ($query) {
                        $query->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->with('files')
            ->orderByRaw('(select count(*) from files where files.item_code = master_list_items.item_code) desc')
            ->paginate(10);

        return view('livewire.item-search', ['items' => $items]);
    }
}
