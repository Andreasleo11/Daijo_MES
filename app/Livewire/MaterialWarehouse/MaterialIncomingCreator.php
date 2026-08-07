<?php

namespace App\Livewire\MaterialWarehouse;

use App\Models\MasterListMaterial;
use App\Models\MwhIncomingHeader;
use App\Models\MwhPallet;
use App\Models\MwhPosition;
use App\Services\MaterialWarehouseService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class MaterialIncomingCreator extends Component
{
    // Header Fields
    public string $document_no = '';
    public string $incoming_type = 'SUPPLIER'; // SUPPLIER vs RETURN_PRODUCTION
    public string $supplier_name = '';
    public string $returned_from = '';
    public string $po_number = '';
    public string $original_outgoing_code = '';
    public bool $is_prefilled_from_outgoing = false;
    public string $arrival_date = '';
    public string $remarks = '';

    // Item Rows (Multi-line)
    public array $items = [];

    // Success State
    public bool $showSuccessModal = false;
    public array $createdPallets = [];

    public function mount(MaterialWarehouseService $mwhService): void
    {
        $this->arrival_date = now()->format('Y-m-d');
        $this->document_no  = $mwhService->generateDocumentNo();

        // Handle pre-filled query parameters from Outgoing History for Retur Produksi
        $reqType         = request()->query('type');
        $reqOutgoingCode = request()->query('outgoing_code');
        $reqReturnedFrom = request()->query('returned_from');
        $reqItemCode     = request()->query('item_code');

        if ($reqType === 'RETURN_PRODUCTION') {
            $this->incoming_type = 'RETURN_PRODUCTION';
        }

        if ($reqOutgoingCode) {
            $this->original_outgoing_code = trim($reqOutgoingCode);
            $this->is_prefilled_from_outgoing = true;
        }

        if ($reqReturnedFrom) {
            $this->returned_from = trim($reqReturnedFrom);
        }

        // Add 1 initial row
        $this->addItemRow();

        if ($reqItemCode) {
            $mat = MasterListMaterial::where('item_code', trim($reqItemCode))->first();
            if ($mat) {
                $this->items[0]['item_code']        = $mat->item_code;
                $this->items[0]['item_description'] = $mat->item_description ?? '';
            }
        }
    }

    public function addItemRow(): void
    {
        $this->items[] = [
            'item_code'        => '',
            'item_description' => '',
            'lot_no'           => '',
            'qty'              => '',
            'position_id'      => '',
            'searchResults'    => [],
        ];
    }

    public function removeItemRow(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems($value, $key): void
    {
        // Live search for item_code autocomplete
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'item_code') {
            $index = (int) $parts[0];
            $query = trim($value);

            if (strlen($query) >= 2) {
                $this->items[$index]['searchResults'] = MasterListMaterial::query()
                    ->where('item_code', 'like', '%' . $query . '%')
                    ->orWhere('item_description', 'like', '%' . $query . '%')
                    ->limit(10)
                    ->get(['item_code', 'item_description', 'purchasing_uom'])
                    ->toArray();
            } else {
                $this->items[$index]['searchResults'] = [];
            }
        }
    }

    public function selectMaterial(int $index, string $itemCode, string $itemDesc): void
    {
        $this->items[$index]['item_code']        = $itemCode;
        $this->items[$index]['item_description'] = $itemDesc;
        $this->items[$index]['searchResults']    = [];
    }

    public function saveIncoming(MaterialWarehouseService $mwhService): void
    {
        $this->validate([
            'incoming_type'          => 'required|in:SUPPLIER,RETURN_PRODUCTION',
            'arrival_date'           => 'required|date',
            'supplier_name'          => 'nullable|string|max:255',
            'returned_from'          => 'nullable|string|max:255',
            'po_number'              => 'nullable|string|max:255',
            'original_outgoing_code' => 'nullable|string|max:255',
            'remarks'                => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.item_code'      => 'required|string|exists:master_list_materials,item_code',
            'items.*.qty'            => 'required|numeric|min:0.01',
            'items.*.position_id'    => 'required|exists:mwh_positions,id',
        ], [
            'items.*.item_code.required'   => 'Part Code material harus diisi.',
            'items.*.item_code.exists'     => 'Part Code material tidak ada di Master List.',
            'items.*.qty.required'         => 'Jumlah (KG) harus diisi.',
            'items.*.position_id.required' => 'Pilih slot rak lokasi penyimpanan.',
        ]);

        try {
            DB::beginTransaction();

            $header = MwhIncomingHeader::create([
                'document_no'             => $mwhService->generateDocumentNo(),
                'incoming_type'           => $this->incoming_type,
                'supplier_name'           => $this->incoming_type === 'SUPPLIER' ? (trim($this->supplier_name) ?: null) : null,
                'returned_from'           => $this->incoming_type === 'RETURN_PRODUCTION' ? (trim($this->returned_from) ?: null) : null,
                'po_number'               => trim($this->po_number) ?: null,
                'original_outgoing_code' => trim($this->original_outgoing_code) ?: null,
                'arrival_date'            => $this->arrival_date,
                'remarks'                 => trim($this->remarks) ?: null,
            ]);

            $this->createdPallets = [];

            foreach ($this->items as $row) {
                $totalQty = (float) $row['qty'];
                $itemCode = strtoupper(trim($row['item_code']));
                $lotNo    = trim($row['lot_no']) ?: null;
                $initialPosId = (int) $row['position_id'];

                $remainingToSplit = $totalQty;

                while ($remainingToSplit > 0) {
                    $palletQty = min(1000.0, $remainingToSplit);
                    $remainingToSplit -= $palletQty;

                    // Determine target slot position for this specific pallet
                    $targetPosId = $initialPosId;

                    $targetPos = MwhPosition::find($targetPosId);
                    $currentStored = MwhPallet::where('position_id', $targetPosId)->where('current_qty', '>', 0)->sum('current_qty');
                    $availableCap = $targetPos ? ($targetPos->max_capacity - $currentStored) : 0;

                    // If selected target slot does not have enough capacity for this pallet, find next available empty/partial slot
                    if ($availableCap < $palletQty) {
                        $nextPos = MwhPosition::whereIn('status', ['EMPTY', 'PARTIAL'])
                            ->where('id', '!=', $targetPosId)
                            ->get()
                            ->first(function($p) use ($palletQty) {
                                $stored = MwhPallet::where('position_id', $p->id)->where('current_qty', '>', 0)->sum('current_qty');
                                return ($p->max_capacity - $stored) >= $palletQty;
                            });

                        if ($nextPos) {
                            $targetPosId = $nextPos->id;
                        }
                    }

                    $inserted = false;
                    $attempts = 0;

                    while (!$inserted && $attempts < 5) {
                        $attempts++;
                        $palletId = $mwhService->generatePalletId();

                        try {
                            $pallet = MwhPallet::create([
                                'pallet_id'          => $palletId,
                                'incoming_header_id' => $header->id,
                                'item_code'          => $itemCode,
                                'lot_no'             => $lotNo,
                                'initial_qty'        => $palletQty,
                                'current_qty'        => $palletQty,
                                'uom'                => 'KG',
                                'position_id'        => $targetPosId,
                                'status'             => 'STORED',
                            ]);
                            $inserted = true;
                        } catch (\Illuminate\Database\QueryException $qe) {
                            if ($attempts >= 5 || !str_contains($qe->getMessage(), '1062 Duplicate entry')) {
                                throw $qe;
                            }
                            usleep(50000);
                        }
                    }

                    $mwhService->updatePositionStatus($targetPosId);

                    $this->createdPallets[] = [
                        'pallet_id'   => $pallet->pallet_id,
                        'item_code'   => $pallet->item_code,
                        'qty'         => $pallet->current_qty,
                        'position'    => $pallet->position ? $pallet->position->position_code : '-',
                    ];
                }
            }

            DB::commit();

            $this->showSuccessModal = true;
            $typeLabel = $this->incoming_type === 'RETURN_PRODUCTION' ? 'Retur Sisa Material Produksi' : 'Kedatangan Supplier';
            session()->flash('success', "Transaksi Penerimaan Material ({$typeLabel}) & Palletizing berhasil disimpan.");
        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            if (str_contains($qe->getMessage(), '1062 Duplicate entry')) {
                session()->flash('error', 'Terjadi duplikasi ID Pallet saat menyimpan. Silakan coba klik Simpan sekali lagi.');
            } else {
                session()->flash('error', 'Terjadi kesalahan basis data saat menyimpan transaksi kedatangan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan transaksi kedatangan: ' . $e->getMessage());
        }
    }

    public function resetForm(MaterialWarehouseService $mwhService): void
    {
        $this->reset(['incoming_type', 'supplier_name', 'returned_from', 'po_number', 'original_outgoing_code', 'is_prefilled_from_outgoing', 'remarks', 'items', 'createdPallets', 'showSuccessModal']);
        $this->incoming_type = 'SUPPLIER';
        $this->arrival_date  = now()->format('Y-m-d');
        $this->document_no   = $mwhService->generateDocumentNo();
        $this->addItemRow();
    }

    public function render()
    {
        $positions = MwhPosition::with('rack')
            ->whereIn('status', ['EMPTY', 'PARTIAL'])
            ->orderBy('position_code', 'asc')
            ->get();

        return view('livewire.material-warehouse.material-incoming-creator', [
            'positions' => $positions,
        ]);
    }
}
