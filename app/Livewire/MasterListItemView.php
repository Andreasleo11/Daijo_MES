<?php

namespace App\Livewire;

use App\Models\MasterListItem;
use App\Models\MasterCustomerDelivery;
use Livewire\Component;
use Livewire\WithPagination;

class MasterListItemView extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterCustomer = '';
    public string $filterMachine  = '';
    public int    $perPage      = 25;

    // Edit state
    public ?int   $editingId    = null;
    public array  $editForm     = [];

    protected $rules = [
        'editForm.tipe_mesin'             => 'nullable|string|max:255',
        'editForm.standart_packaging_list'=> 'nullable|integer|min:0',
        'editForm.setup_time_minute'      => 'nullable|string|max:255',
        'editForm.pair'                   => 'nullable|string|max:255',
        'editForm.cavity'                 => 'nullable|integer|min:0',
        'editForm.customer_code'          => 'nullable|string|max:255',
        'editForm.cycle_time'             => 'nullable|numeric|min:0',
    ];

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingFilterCustomer() { $this->resetPage(); }
    public function updatingFilterMachine()  { $this->resetPage(); }

    public function startEdit(int $id): void
    {
        $item = MasterListItem::findOrFail($id);
        $this->editingId = $id;
        $this->editForm  = [
            'tipe_mesin'              => $item->tipe_mesin,
            'standart_packaging_list' => $item->standart_packaging_list,
            'setup_time_minute'       => $item->setup_time_minute,
            'pair'                    => $item->pair,
            'cavity'                  => $item->cavity,
            'customer_code'           => $item->customer_code,
            'cycle_time'              => $item->cycle_time,
        ];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editForm  = [];
    }

    public function saveEdit(): void
    {
        $this->validate();

        MasterListItem::findOrFail($this->editingId)->update($this->editForm);

        $this->editingId = null;
        $this->editForm  = [];
        session()->flash('success', 'Item updated successfully.');
    }

    public function getCustomerListProperty()
    {
        return MasterCustomerDelivery::orderBy('customer_name')->get();
    }

    public function getMachineListProperty()
    {
        return MasterListItem::select('tipe_mesin')
            ->distinct()
            ->whereNotNull('tipe_mesin')
            ->orderBy('tipe_mesin')
            ->pluck('tipe_mesin');
    }

    public function render()
    {
        $items = MasterListItem::with('customer')
            ->when($this->search, fn($q) =>
                $q->where('item_code', 'like', "%{$this->search}%")
                  ->orWhere('item_name', 'like', "%{$this->search}%")
            )
            ->when($this->filterCustomer, fn($q) =>
                $q->where('customer_code', $this->filterCustomer)
            )
            ->when($this->filterMachine, fn($q) =>
                $q->where('tipe_mesin', $this->filterMachine)
            )
            ->orderBy('item_code')
            ->paginate($this->perPage);

        return view('livewire.master-list-item-view', [
            'items' => $items,
        ]);
    }
}