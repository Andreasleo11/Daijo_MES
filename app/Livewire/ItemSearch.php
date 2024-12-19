<?php

namespace App\Livewire;

use App\Models\MasterListItem;
use Livewire\Component;
use Livewire\WithPagination;

class ItemSearch extends Component
{
    use WithPagination;

    public $search = ''; // Bind this to the search input

    public function render()
    {
        $items = MasterListItem::where('item_code', 'like', '%'.$this->search.'%')
            ->orWhereHas('files', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->with('files')
            ->orderByRaw('(select count(*) from files where files.item_code = master_list_items.item_code) desc') // Order by number of files
            ->paginate(10);

        return view('livewire.item-search', ['items' => $items]);
    }
}
