<?php

namespace App\Livewire\MaterialWarehouse;

use App\Models\MwhPallet;
use App\Models\MwhPosition;
use App\Services\MaterialWarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialPalletIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'ALL';
    public int $perPage = 25;

    // Relocation Modal State
    public bool $showRelocateModal = false;
    public ?int $relocatingPalletId = null;
    public ?string $relocatingPalletCode = null;
    public ?int $newPositionId = null;

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openRelocateModal(int $id): void
    {
        $pallet = MwhPallet::findOrFail($id);
        $this->relocatingPalletId   = $pallet->id;
        $this->relocatingPalletCode = $pallet->pallet_id;
        $this->newPositionId        = $pallet->position_id;
        $this->showRelocateModal    = true;
    }

    public function saveRelocation(MaterialWarehouseService $mwhService): void
    {
        $this->validate([
            'newPositionId' => 'required|exists:mwh_positions,id',
        ]);

        $pallet = MwhPallet::findOrFail($this->relocatingPalletId);
        $oldPositionId = $pallet->position_id;

        if ($oldPositionId !== (int) $this->newPositionId) {
            $pallet->update([
                'position_id' => $this->newPositionId,
            ]);

            // Update status of old and new rack slots
            if ($oldPositionId) {
                $mwhService->updatePositionStatus($oldPositionId);
            }
            $mwhService->updatePositionStatus($this->newPositionId);

            session()->flash('success', "Pallet {$pallet->pallet_id} berhasil direlokasi ke slot baru.");
        }

        $this->showRelocateModal = false;
    }

    public function deletePallet(int $id, MaterialWarehouseService $mwhService): void
    {
        $pallet = MwhPallet::find($id);
        if ($pallet) {
            $positionId = $pallet->position_id;
            $palletCode = $pallet->pallet_id;

            // Also delete related outgoing transaction history for this test pallet
            \App\Models\MwhOutgoing::where('pallet_id', $palletCode)->delete();
            $pallet->delete();

            if ($positionId) {
                $mwhService->updatePositionStatus($positionId);
            }

            session()->flash('success', "Pallet {$palletCode} beserta history transaksinya berhasil dihapus.");
        }
    }

    public function render()
    {
        $pallets = MwhPallet::with(['position.rack', 'material', 'incomingHeader'])
            ->when($this->search, function ($query) {
                $s = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($s) {
                    $q->where('pallet_id', 'like', $s)
                      ->orWhere('item_code', 'like', $s)
                      ->orWhere('lot_no', 'like', $s)
                      ->orWhereHas('position', function ($posQ) use ($s) {
                          $posQ->where('position_code', 'like', $s);
                      });
                });
            })
            ->when($this->statusFilter !== 'ALL', function ($query) {
                if ($this->statusFilter === 'PARTIAL') {
                    $query->where(function($q) {
                        $q->where('status', 'PARTIAL')
                          ->orWhereRaw('current_qty < initial_qty AND current_qty > 0')
                          ->orWhereHas('position', function($pq) {
                              $pq->where('status', 'PARTIAL');
                          });
                    });
                } elseif ($this->statusFilter === 'STORED') {
                    $query->where(function($q) {
                        $q->where('status', 'STORED')
                          ->whereRaw('current_qty >= initial_qty AND current_qty > 0');
                    });
                } elseif ($this->statusFilter === 'EMPTY') {
                    $query->where(function($q) {
                        $q->where('status', 'EMPTY')
                          ->orWhere('current_qty', '<=', 0);
                    });
                }
            })
            ->leftJoin('mwh_incoming_headers', 'mwh_pallets.incoming_header_id', '=', 'mwh_incoming_headers.id')
            ->select('mwh_pallets.*')
            ->orderByRaw('COALESCE(mwh_incoming_headers.arrival_date, DATE(mwh_pallets.created_at)) DESC')
            ->orderBy('mwh_pallets.id', 'desc')
            ->paginate($this->perPage);

        $availablePositions = MwhPosition::with('rack')
            ->whereIn('status', ['EMPTY', 'PARTIAL'])
            ->orderBy('position_code', 'asc')
            ->get();

        return view('livewire.material-warehouse.material-pallet-index', [
            'pallets'            => $pallets,
            'availablePositions' => $availablePositions,
        ]);
    }
}
