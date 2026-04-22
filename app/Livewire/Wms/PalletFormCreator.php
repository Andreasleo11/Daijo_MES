<?php

namespace App\Livewire\Wms;

use App\Models\MasterListItem;
use App\Models\WmsPosition;
use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Services\WmsService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PalletFormCreator extends Component
{
    // Form Fields
    public $part_no;
    public $model_name;
    public $prod_date;
    public $lot_no;
    public $delivery_name;
    public $delivery_shift;
    public $remarks;
    public $total_box = 0;
    public $total_pallet_qty = 0;

    // Scanning (4 Fields)
    public $scan_spk;
    public $scan_qty;
    public $scan_whse;
    public $scan_label;

    // List for search results
    public $searchResults = [];
    public $showDropdown = false;
    public $scanned_items = [];

    protected $rules = [
        'part_no' => 'required',
        'prod_date' => 'required|date',
        'delivery_name' => 'required',
        'delivery_shift' => 'required',
    ];

    public function mount()
    {
        $this->prod_date = now()->format('Y-m-d');
    }

    /**
     * Triggered when Part No is typed
     */
    public function updatedPartNo($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            $this->showDropdown = false;
            return;
        }

        $this->searchResults = MasterListItem::where('item_code', 'like', '%' . $value . '%')
            ->orWhere('item_name', 'like', '%' . $value . '%')
            ->limit(10)
            ->get(['item_code', 'item_name'])
            ->toArray();

        $this->showDropdown = count($this->searchResults) > 0;
    }

    /**
     * Select an item from dropdown
     */
    public function selectPartNo($itemCode, $itemName)
    {
        $this->part_no = $itemCode;
        $this->model_name = $itemName;
        $this->searchResults = [];
        $this->showDropdown = false;
    }

    private function resetScanner()
    {
        $this->scan_spk = '';
        $this->scan_qty = '';
        $this->scan_whse = '';
        $this->scan_label = '';
        $this->dispatch('focus-spk');
    }

    /**
     * Add a box with its 4 fields to the list
     */
    public function addItem()
    {
        if (empty($this->scan_spk) || empty($this->scan_label)) {
             session()->flash('scan_error', 'SPK dan Label harus di-scan.');
             return;
        }

        // 1. Validation: Unique Label on this pallet (Current Session)
        foreach ($this->scanned_items as $item) {
            if ($item['label'] === $this->scan_label && $item['spk_no'] === $this->scan_spk) {
                session()->flash('scan_error', 'Label ' . $this->scan_label . ' sudah ada di daftar scan saat ini.');
                $this->resetScanner();
                return;
            }
        }

        // 2. Validation: Unique Label in Database
        $exists = WmsPalletFormDetail::where('spk_no', $this->scan_spk)
            ->where('label', $this->scan_label)
            ->exists();

        if ($exists) {
            session()->flash('scan_error', 'Label ' . $this->scan_label . ' dengan SPK ' . $this->scan_spk . ' sudah pernah di-scan sebelumnya (Terdaftar di database).');
            $this->resetScanner();
            return;
        }

        // Add to list
        $this->scanned_items[] = [
            'spk_no' => $this->scan_spk,
            'qty' => $this->scan_qty ?: 0,
            'warehouse' => $this->scan_whse,
            'label' => $this->scan_label,
        ];

        $this->resetScanner();
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->scanned_items[$index]);
        $this->scanned_items = array_values($this->scanned_items);
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $this->total_box = count($this->scanned_items);
        $this->total_pallet_qty = array_sum(array_column($this->scanned_items, 'qty'));
    }

    public function generateForm(WmsService $wmsService)
    {
        $this->validate();

        if (count($this->scanned_items) === 0) {
            session()->flash('error', 'Minimal harus ada 1 box yang di-scan.');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Get Recommendation Position
            $item = MasterListItem::where('item_code', $this->part_no)->first();
            $customerCode = $item ? $item->customer_code : 'HM';
            
            $pos = $wmsService->recommendPosition($customerCode, $this->part_no);
            
            if (!$pos) {
                throw new \Exception('Maaf, tidak ada posisi rak yang tersedia untuk customer ' . $customerCode);
            }

            // 2. Generate Pallet ID
            $palletId = $wmsService->generatePalletId();

            // 3. Create Header
            $form = WmsPalletForm::create([
                'pallet_id' => $palletId,
                'position_id' => $pos->id,
                'part_no' => $this->part_no,
                'model_name' => $this->model_name,
                'prod_date' => $this->prod_date,
                'lot_no' => $this->lot_no,
                'delivery_name' => $this->delivery_name,
                'delivery_shift' => $this->delivery_shift,
                'box_qty' => $this->total_box,
                'total_pallet_qty' => $this->total_pallet_qty,
                'remarks' => $this->remarks,
            ]);

            // 4. Create Details
            foreach ($this->scanned_items as $item) {
                WmsPalletFormDetail::create([
                    'pallet_form_id' => $palletId,
                    'spk_no' => $item['spk_no'],
                    'qty' => $item['qty'],
                    'warehouse' => $item['warehouse'],
                    'label' => $item['label'],
                ]);
            }

            // 5. Update Position Status & Item tracking
            $pos->update(['last_item_code' => $this->part_no]);
            $wmsService->updatePositionStatus($pos->id);

            // 6. Record Log Transaction
            $wmsService->logTransaction($palletId, 'IN', $pos->id);

            DB::commit();

            return redirect()->route('wms.pallet-form.print', ['id' => $palletId]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.wms.pallet-form-creator');
    }
}
