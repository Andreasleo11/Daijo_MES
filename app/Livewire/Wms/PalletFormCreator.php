<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Models\SpkItemHistory;
use App\Models\MasterListItem;
use App\Services\WmsService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PalletFormCreator extends Component
{
    // ─── Header Form Fields ────────────────────────────────────────────────────
    public $prod_date;
    public $lot_no;
    public $delivery_name;
    public $delivery_shift;
    public $remarks;
    public $total_box        = 0;
    public $total_pallet_qty = 0;

    // ─── Scan Fields ───────────────────────────────────────────────────────────
    public string $scan_spk   = '';
    public string $scan_qty   = '';
    public string $scan_whse  = '';
    public string $scan_label = '';
    public int $scan_box_count = 1; // Default to 1 box

    // Auto-filled from SPK lookup (read-only display)
    public string $scan_part_no       = '';
    public string $scan_model_name    = '';
    public string $scan_customer_code = '';

    // No-Label mode state
    public string $label_mode      = 'SCAN'; // 'SCAN' | 'NO_LABEL'
    public string $no_label_reason = '';

    // Accumulated scan list
    public array $scanned_items = [];

    // Real-time recommendation
    public $recommendedSlot = null;

    // ─── Validation ────────────────────────────────────────────────────────────
    protected $rules = [
        'prod_date'     => 'required|date',
        'delivery_name' => 'required',
        'delivery_shift'=> 'required',
    ];

    public function mount(): void
    {
        $this->prod_date = now()->format('Y-m-d');
    }



    // ─── No-Label Toggle ───────────────────────────────────────────────────────

    /**
     * Toggle antara mode SCAN biasa dan mode NO_LABEL.
     */
    public function toggleNoLabel(): void
    {
        if ($this->label_mode === 'SCAN') {
            // Aktifkan no-label mode
            $this->label_mode        = 'NO_LABEL';
            $this->scan_spk          = '';
            $this->scan_label        = '';
            $this->scan_part_no      = '';
            $this->scan_model_name   = '';
            $this->scan_customer_code = '';
            $this->dispatch('focus-qty');
        } else {
            // Kembali ke mode scan normal
            $this->label_mode      = 'SCAN';
            $this->no_label_reason = '';
            $this->dispatch('focus-spk');
        }
    }

    // ─── Core Scanner Logic ────────────────────────────────────────────────────

    public function resetScanner(): void
    {
        $this->scan_spk          = '';
        $this->scan_qty          = '';
        $this->scan_whse         = '';
        $this->scan_label        = '';
        $this->scan_box_count    = 1;
        $this->scan_part_no      = '';
        $this->scan_model_name   = '';
        $this->scan_customer_code = '';
        $this->no_label_reason   = '';
        $this->dispatch('focus-spk');
    }

    /**
     * Add a box to the scanned list.
     * Handles both normal (with SPK+label) and no-label boxes.
     */
    public function addItem(): void
    {
        // ─── Case 2: No-Label Mode ─────────────────────────────────────────────
        if ($this->label_mode === 'NO_LABEL') {
            if (empty(trim($this->scan_qty))) {
                session()->flash('scan_error', 'Qty per box harus diisi.');
                return;
            }

            $count = max(1, (int) $this->scan_box_count);

            for ($i = 0; $i < $count; $i++) {
                $this->scanned_items[] = [
                    'part_no'         => null,
                    'model_name'      => null,
                    'customer_code'   => null,
                    'spk_no'          => null,
                    'qty'             => (float) $this->scan_qty,
                    'warehouse'       => $this->scan_whse ?: null,
                    'label'           => null,
                    'is_no_label'     => true,
                    'no_label_reason' => $this->no_label_reason ?: null,
                ];
            }

            $this->label_mode = 'SCAN';
            $this->resetScanner();
            $this->calculateTotals();
            return;
        }

        // ─── Case 1: Normal / Multi-Item Mode ──────────────────────────────────

        // Wajib ada SPK
        if (empty(trim($this->scan_spk))) {
            session()->flash('scan_error', 'SPK harus di-scan terlebih dahulu.');
            return;
        }

        // Pastikan SPK valid (ada di database)
        if (empty($this->scan_part_no)) {
            $spkHistory = SpkItemHistory::where('spk_number', trim($this->scan_spk))->first();
            if (! $spkHistory) {
                session()->flash('scan_error', 'SPK "' . $this->scan_spk . '" tidak ditemukan di sistem. Pastikan SPK sudah terdaftar.');
                return;
            }
            // Auto-fill jika belum terisi (misalnya debounce belum trigger)
            $item = MasterListItem::where('item_code', $spkHistory->item_code)->first();
            $this->scan_part_no       = $spkHistory->item_code;
            $this->scan_model_name    = $item?->item_name ?? '-';
            $this->scan_customer_code = $item?->customer_code ?? '';
        }

        // Wajib ada label
        if (empty(trim($this->scan_label))) {
            session()->flash('scan_error', 'Label harus di-scan, atau tekan "Tanpa Label" jika box tidak memiliki label.');
            return;
        }

        // Cek duplikat di session yang sedang berjalan
        foreach ($this->scanned_items as $item) {
            if (
                ! empty($item['label']) &&
                $item['label'] === trim($this->scan_label) &&
                $item['spk_no'] === trim($this->scan_spk)
            ) {
                session()->flash('scan_error', 'Label "' . $this->scan_label . '" dengan SPK ini sudah ada di daftar scan saat ini.');
                $this->resetScanner();
                return;
            }
        }

        // Cek duplikat di database (global)
        $exists = WmsPalletFormDetail::where('spk_no', trim($this->scan_spk))
            ->where('label', trim($this->scan_label))
            ->exists();

        if ($exists) {
            session()->flash('scan_error', 'Label "' . $this->scan_label . '" dengan SPK ini sudah pernah tersimpan di database sebelumnya.');
            $this->resetScanner();
            return;
        }

        // Tambahkan ke list
        $this->scanned_items[] = [
            'part_no'         => $this->scan_part_no,
            'model_name'      => $this->scan_model_name,
            'customer_code'   => $this->scan_customer_code,
            'spk_no'          => trim($this->scan_spk),
            'qty'             => (float) ($this->scan_qty ?: 0),
            'warehouse'       => $this->scan_whse ?: null,
            'label'           => trim($this->scan_label),
            'is_no_label'     => false,
            'no_label_reason' => null,
        ];

        $this->resetScanner();
        $this->calculateTotals();
    }

    public function removeItem(int $index): void
    {
        unset($this->scanned_items[$index]);
        $this->scanned_items = array_values($this->scanned_items);
        $this->calculateTotals();
    }

    private function calculateTotals(): void
    {
        $this->total_box        = count($this->scanned_items);
        $this->total_pallet_qty = array_sum(array_column($this->scanned_items, 'qty'));
        
        $this->updateRecommendation();
    }

    private function updateRecommendation(): void
    {
        if (count($this->scanned_items) === 0) {
            $this->recommendedSlot = null;
            return;
        }

        $wmsService = app(WmsService::class);
        $customerCodes = array_column($this->scanned_items, 'customer_code');
        $firstPartNo = array_filter(array_column($this->scanned_items, 'part_no'))[0] ?? '';
        
        $pos = $wmsService->recommendPosition($customerCodes, $firstPartNo);
        $this->recommendedSlot = $pos ? $pos->position_code : 'RAK PENUH';
    }

    // ─── Generate Pallet Form ──────────────────────────────────────────────────

    public function generateForm(WmsService $wmsService)
    {
        $this->validate();

        if (count($this->scanned_items) === 0) {
            session()->flash('error', 'Minimal harus ada 1 box yang di-scan.');
            return;
        }

        try {
            DB::beginTransaction();

            // --- Hitung summary untuk header ---
            $allPartNos = array_unique(
                array_filter(array_column($this->scanned_items, 'part_no'))
            );
            $isMixed = count($allPartNos) > 1;

            $headerPartNo    = $isMixed ? 'MIXED' : ($allPartNos[0] ?? null);
            $headerModelName = $isMixed ? 'MULTI-ITEM' : ($this->scanned_items[0]['model_name'] ?? null);

            // --- Rack recommendation ---
            $customerCodes = array_column($this->scanned_items, 'customer_code');
            $primaryPartNo = $allPartNos[0] ?? '';

            $pos = $wmsService->recommendPosition($customerCodes, $primaryPartNo);

            if (! $pos) {
                throw new \Exception('Tidak ada posisi rak yang tersedia. Warehouse mungkin penuh.');
            }

            // --- Generate ONE Pallet ID ---
            $palletId = $wmsService->generatePalletId();

            // --- Create Header ---
            WmsPalletForm::create([
                'pallet_id'        => $palletId,
                'position_id'      => $pos->id,
                'part_no'          => $headerPartNo,
                'model_name'       => $headerModelName,
                'prod_date'        => $this->prod_date,
                'lot_no'           => $this->lot_no,
                'delivery_name'    => $this->delivery_name,
                'delivery_shift'   => $this->delivery_shift,
                'box_qty'          => $this->total_box,
                'total_pallet_qty' => $this->total_pallet_qty,
                'remarks'          => $this->remarks,
            ]);

            // --- Create Details ---
            foreach ($this->scanned_items as $boxItem) {
                WmsPalletFormDetail::create([
                    'pallet_form_id'  => $palletId,
                    'part_no'         => $boxItem['part_no'],
                    'model_name'      => $boxItem['model_name'],
                    'spk_no'          => $boxItem['spk_no'],
                    'qty'             => $boxItem['qty'],
                    'warehouse'       => $boxItem['warehouse'],
                    'label'           => $boxItem['label'],
                    'is_no_label'     => $boxItem['is_no_label'],
                    'no_label_reason' => $boxItem['no_label_reason'],
                ]);
            }

            // --- Update position tracking ---
            $pos->update(['last_item_code' => $headerPartNo]);
            $wmsService->updatePositionStatus($pos->id);

            // --- Log Transaction ---
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
