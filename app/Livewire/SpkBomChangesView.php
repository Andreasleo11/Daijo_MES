<?php

namespace App\Livewire;

use App\Models\MasterBom;
use App\Models\MasterBomFgHeader;
use App\Models\MasterListItem;
use App\Models\SpkBomChangeLog;
use App\Models\SpkMaster;
use App\Services\SpkBomService;
use App\Services\SpkMasterService;
use Livewire\Component;
use Livewire\WithPagination;

class SpkBomChangesView extends Component
{
    use WithPagination;

    // Filter & Pencarian
    public string $search = '';
    public string $statusFilter = '';
    public string $bomFilter = 'all'; // all, with_bom, without_bom
    public int $perPage = 15;

    // State Accordion Inline Open/Close
    public array $openSpks = [];
    public array $spkBomCache = [];

    // State Modal Drill-Down Detail BOM
    public bool $showModal = false;
    public ?array $modalSpk = null;
    public ?array $modalBom = null;

    // State Modal Edit Plan Qty & Base Qty
    public bool $showEditModal = false;
    public string $editSpkNumber = '';
    public string $editItemCode = '';
    public string $editItemDescription = '';
    public string|int|float|null $editPlanQty = null;
    public string|int|float|null $editOldPlanQty = null;
    public string|int|float|null $editBaseQty = null;
    public float $editSpkPlannedQty = 0;

    // State Modal Tambah Material Baru
    public bool $showAddModal = false;
    public string $addSpkNumber = '';
    public string $addItemCode = '';
    public string $addItemName = '';
    public bool $showAddDropdown = false;
    public string|int|float|null $addPlanQty = null;
    public string|int|float|null $addBaseQty = null;
    public float $addSpkPlannedQty = 0;
    public string $addWarehouse = '';

    // State Modal Hapus Material
    public bool $showDeleteModal = false;
    public string $deleteSpkNumber = '';
    public string $deleteItemCode = '';
    public string $deleteItemDescription = '';

    // State Modal Ganti Material (Replace)
    public bool $showReplaceModal = false;
    public string $replaceSpkNumber = '';
    public string $replaceOldItemCode = '';
    public string $replaceOldItemDescription = '';
    public string $replaceNewItemCode = '';
    public string $replaceNewItemName = '';
    public bool $showReplaceDropdown = false;
    public string|int|float|null $replacePlanQty = null;
    public string|int|float|null $replaceBaseQty = null;
    public float $replaceSpkPlannedQty = 0;
    public string $replaceWarehouse = '';

    // State Modal Riwayat Perubahan (History Log)
    public bool $showHistoryModal = false;
    public ?string $historySpkNumber = null;
    public array $historyLogs = [];

    // State Mode Edit Resep (Batch Staging)
    public ?string $editingSpk = null;
    public array $stagedLines = [];

    // Flash Message
    public ?string $flashSuccess = null;
    public ?string $flashError = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'bomFilter'    => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBomFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Buka / Tutup Accordion BOM per SPK
     */
    public function toggleSpk(string $spkNumber, string $itemCode, float $plannedQty): void
    {
        if (isset($this->openSpks[$spkNumber])) {
            unset($this->openSpks[$spkNumber]);
        } else {
            $this->openSpks[$spkNumber] = true;
            if (!isset($this->spkBomCache[$spkNumber])) {
                $service = app(SpkBomService::class);
                $this->spkBomCache[$spkNumber] = $service->getLeafMaterials($itemCode, $plannedQty);
            }
        }
    }

    /**
     * Refresh data cache BOM untuk suatu SPK setelah ada update API
     */
    protected function refreshSpkBom(string $spkNumber): void
    {
        $spk = SpkMaster::where('spk_number', $spkNumber)->first();
        if ($spk) {
            $service = app(SpkBomService::class);
            $this->spkBomCache[$spkNumber] = $service->getLeafMaterials($spk->item_code, (float) $spk->planned_quantity);
            if ($this->showModal && $this->modalSpk && $this->modalSpk['spk_number'] === $spkNumber) {
                $this->modalBom = $this->spkBomCache[$spkNumber];
            }
        }
    }

    /**
     * Buka Modal Rincian BOM SPK
     */
    public function openModal(string $spkNumber, string $itemCode, float $plannedQty, ?string $partName = null, ?string $status = null): void
    {
        $service = app(SpkBomService::class);
        $this->modalBom = $service->getLeafMaterials($itemCode, $plannedQty);

        $this->modalSpk = [
            'spk_number'   => $spkNumber,
            'item_code'    => $itemCode,
            'planned_qty'  => $plannedQty,
            'part_name'    => $partName ?: ($this->modalBom['fg_description'] ?? '-'),
            'status'       => $status,
            'project_code' => $this->modalBom['project_code'] ?? null,
        ];

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->modalSpk = null;
        $this->modalBom = null;
    }

    /**
     * Buka Seluruh Baris SPK pada halaman aktif
     */
    public function expandAll(array $spkList): void
    {
        $service = app(SpkBomService::class);
        foreach ($spkList as $item) {
            $spkNo = $item['spk_number'];
            $this->openSpks[$spkNo] = true;
            if (!isset($this->spkBomCache[$spkNo])) {
                $this->spkBomCache[$spkNo] = $service->getLeafMaterials($item['item_code'], (float)$item['planned_qty']);
            }
        }
    }

    /**
     * Tutup Semua Baris
     */
    public function collapseAll(): void
    {
        $this->openSpks = [];
    }

    // ==========================================
    // EDIT MODE MANAGEMENT (BATCH STAGING)
    // ==========================================
    public function startEditMode(string $spkNumber): void
    {
        if (!empty($this->editingSpk) && $this->editingSpk !== $spkNumber && !empty($this->stagedLines)) {
            $this->flashError = "Anda masih memiliki perubahan belum disimpan pada SPK {$this->editingSpk}. Silakan kirim ke SAP atau batalkan terlebih dahulu.";
            return;
        }

        $this->editingSpk = $spkNumber;
        $this->stagedLines = [];
        $this->openSpks[$spkNumber] = true;

        if (!isset($this->spkBomCache[$spkNumber])) {
            $spk = SpkMaster::where('spk_number', $spkNumber)->first();
            if ($spk) {
                $service = app(SpkBomService::class);
                $this->spkBomCache[$spkNumber] = $service->getLeafMaterials($spk->item_code, (float) $spk->planned_quantity);
            }
        }
    }

    public function cancelEditMode(): void
    {
        $this->stagedLines = [];
        $this->editingSpk = null;
        $this->flashSuccess = 'Mode edit dibatalkan. Seluruh perubahan draft dibersihkan.';
        $this->flashError = null;
    }

    public function unstageItem(string $itemCode): void
    {
        if (isset($this->stagedLines[$itemCode])) {
            unset($this->stagedLines[$itemCode]);
            $this->flashSuccess = "Perubahan pada material {$itemCode} telah dibatalkan dari draft.";
        }
    }

    public function submitBatchChanges(): void
    {
        if (empty($this->editingSpk)) {
            $this->flashError = 'Tidak ada SPK yang sedang dalam mode edit.';
            return;
        }

        if (empty($this->stagedLines)) {
            $this->flashError = 'Belum ada perubahan material yang ditambahkan ke draft edit.';
            return;
        }

        try {
            $service = app(SpkMasterService::class);
            $userId = auth()->id();
            $userName = auth()->user()?->name ?: 'System';

            $payloadLines = [];
            foreach ($this->stagedLines as $staged) {
                $line = [
                    'item_code' => $staged['item_code'],
                ];

                if (!empty($staged['delete'])) {
                    $line['delete'] = true;
                } else {
                    if (isset($staged['base_qty']) && $staged['base_qty'] !== null && $staged['base_qty'] !== '') {
                        $line['base_qty'] = (float) $staged['base_qty'];
                    }
                    if (isset($staged['plan_qty']) && $staged['plan_qty'] !== null && $staged['plan_qty'] !== '') {
                        $line['plan_qty'] = (float) $staged['plan_qty'];
                    }
                    if (!empty($staged['warehouse'])) {
                        $line['warehouse'] = trim($staged['warehouse']);
                    }
                }

                if (isset($staged['old_plan_qty']) && $staged['old_plan_qty'] !== null) {
                    $line['old_plan_qty'] = (float) $staged['old_plan_qty'];
                }
                if (!empty($staged['action_type'])) {
                    $line['action_type'] = $staged['action_type'];
                }
                if (!empty($staged['replaced_item_code'])) {
                    $line['replaced_item_code'] = $staged['replaced_item_code'];
                }

                $payloadLines[] = $line;
            }

            $spkNo = $this->editingSpk;
            $count = count($payloadLines);

            $result = $service->updateProductionOrderLines(
                $spkNo,
                $payloadLines,
                $userId,
                $userName
            );

            $this->flashSuccess = "Berhasil mengirim {$count} perubahan material untuk SPK {$spkNo} ke SAP! ({$result['message']})";
            $this->flashError = null;

            // Bersihkan draft & keluar dari mode edit
            $this->stagedLines = [];
            $this->editingSpk = null;
            $this->refreshSpkBom($spkNo);

        } catch (\Exception $e) {
            $this->flashError = 'Gagal mengirim perubahan ke SAP: ' . $e->getMessage();
            $this->flashSuccess = null;
        }
    }

    // ==========================================
    // ACTION 1: EDIT QUANTITY MATERIAL (STAGING)
    // ==========================================
    public function openEditQtyModal(string $spkNumber, string $itemCode, ?string $desc, float $planQty, ?float $baseQty = null, ?float $spkPlannedQty = null): void
    {
        $this->closeAllModals();
        $this->editSpkNumber = $spkNumber;
        $this->editItemCode = $itemCode;
        $this->editItemDescription = $desc ?: '-';

        // Jika item ini sudah pernah diedit di draft, gunakan nilai dari draft
        if (isset($this->stagedLines[$itemCode])) {
            $staged = $this->stagedLines[$itemCode];
            $this->editPlanQty = $staged['plan_qty'] ?? $planQty;
            $this->editOldPlanQty = $staged['old_plan_qty'] ?? $planQty;
            $this->editBaseQty = $staged['base_qty'] ?? $baseQty;
        } else {
            $this->editPlanQty = $planQty;
            $this->editOldPlanQty = $planQty;
            $this->editBaseQty = $baseQty;
        }

        if ($spkPlannedQty === null) {
            $spkPlannedQty = (float) SpkMaster::where('spk_number', $spkNumber)->value('planned_quantity');
        }
        $this->editSpkPlannedQty = (float) $spkPlannedQty;
        $this->showEditModal = true;
    }

    public function updatedEditBaseQty(): void
    {
        $clean = str_replace(',', '.', trim((string) $this->editBaseQty));
        if ($clean !== '' && is_numeric($clean)) {
            $base = (float) $clean;
            if ($base > 0 && $this->editSpkPlannedQty > 0) {
                $this->editPlanQty = round($base * $this->editSpkPlannedQty, 4);
            }
        }
    }

    public function submitEditQty(): void
    {
        if ($this->editPlanQty !== null) {
            $this->editPlanQty = str_replace(',', '.', trim((string) $this->editPlanQty));
        }
        if ($this->editBaseQty !== null && $this->editBaseQty !== '') {
            $this->editBaseQty = str_replace(',', '.', trim((string) $this->editBaseQty));
        }

        $this->validate([
            'editSpkNumber' => 'required',
            'editItemCode'  => 'required',
            'editPlanQty'   => 'required|numeric|min:0',
            'editBaseQty'   => 'nullable|numeric|gt:0',
        ]);

        if (empty($this->editingSpk) || $this->editingSpk !== $this->editSpkNumber) {
            $this->startEditMode($this->editSpkNumber);
        }

        $this->stagedLines[$this->editItemCode] = [
            'item_code'    => $this->editItemCode,
            'item_name'    => $this->editItemDescription,
            'action_type'  => 'UPDATE_QTY',
            'base_qty'     => ($this->editBaseQty !== null && $this->editBaseQty !== '') ? (float) $this->editBaseQty : null,
            'plan_qty'     => (float) $this->editPlanQty,
            'old_plan_qty' => $this->editOldPlanQty ? (float) $this->editOldPlanQty : null,
            'warehouse'    => null,
            'delete'       => false,
            'is_new'       => false,
        ];

        $this->flashSuccess = "Perubahan material {$this->editItemCode} disimpan ke draft. Klik 'Selesai & Kirim ke SAP' untuk menerapkan.";
        $this->flashError = null;
        $this->showEditModal = false;
    }

    // ==========================================
    // ACTION 2: TAMBAH MATERIAL BARU (DROPDOWN & STAGING)
    // ==========================================
    public function openAddMaterialModal(string $spkNumber, ?float $plannedQty = null): void
    {
        $this->closeAllModals();
        $this->addSpkNumber = $spkNumber;
        $this->addItemCode = '';
        $this->addItemName = '';
        $this->showAddDropdown = false;
        $this->addBaseQty = null;
        $this->addPlanQty = null;
        $this->addWarehouse = '';

        if ($plannedQty === null) {
            $plannedQty = (float) SpkMaster::where('spk_number', $spkNumber)->value('planned_quantity');
        }
        $this->addSpkPlannedQty = (float) $plannedQty;
        $this->showAddModal = true;
    }

    public function updatedAddItemCode(): void
    {
        $this->showAddDropdown = true;
        $match = MasterListItem::where('item_code', trim($this->addItemCode))->first();
        if ($match) {
            $this->addItemName = $match->item_name ?: '';
        } else {
            $this->addItemName = '';
        }
    }

    public function getAddItemSuggestionsProperty(): array
    {
        $term = trim($this->addItemCode);
        if (strlen($term) < 1) {
            return [];
        }
        return MasterListItem::where(function ($q) use ($term) {
                $q->where('item_code', 'like', "%{$term}%")
                  ->orWhere('item_name', 'like', "%{$term}%");
            })
            ->limit(15)
            ->get(['item_code', 'item_name'])
            ->toArray();
    }

    public function selectAddMaterial(string $itemCode, ?string $itemName = null): void
    {
        $this->addItemCode = $itemCode;
        $this->addItemName = $itemName ?: '';
        $this->showAddDropdown = false;
    }

    public function updatedAddBaseQty(): void
    {
        $clean = str_replace(',', '.', trim((string) $this->addBaseQty));
        if ($clean !== '' && is_numeric($clean)) {
            $base = (float) $clean;
            if ($base > 0 && $this->addSpkPlannedQty > 0) {
                $this->addPlanQty = round($base * $this->addSpkPlannedQty, 4);
                return;
            }
        }
        $this->addPlanQty = null;
    }

    public function submitAddMaterial(): void
    {
        if ($this->addBaseQty !== null) {
            $this->addBaseQty = str_replace(',', '.', trim((string) $this->addBaseQty));
        }

        $this->validate([
            'addSpkNumber' => 'required',
            'addItemCode'  => 'required|string',
            'addBaseQty'   => 'required|numeric|gt:0',
        ], [
            'addItemCode.required' => 'Kode item material wajib diisi atau dipilih dari dropdown.',
            'addBaseQty.required'  => 'Base Qty (kebutuhan per unit) wajib diisi.',
            'addBaseQty.numeric'   => 'Base Qty harus berupa angka desimal valid (contoh: 0.15).',
            'addBaseQty.gt'        => 'Base Qty harus lebih besar dari 0.',
        ]);

        if (empty($this->editingSpk) || $this->editingSpk !== $this->addSpkNumber) {
            $this->startEditMode($this->addSpkNumber);
        }

        $baseFloat = (float) $this->addBaseQty;

        // Auto hitung plan_qty dari base_qty * SPK planned target
        $calculatedPlanQty = round($baseFloat * (float) $this->addSpkPlannedQty, 4);
        if ($calculatedPlanQty <= 0) {
            $calculatedPlanQty = $baseFloat;
        }

        $itemCode = trim($this->addItemCode);
        $this->stagedLines[$itemCode] = [
            'item_code'          => $itemCode,
            'item_name'          => trim($this->addItemName),
            'action_type'        => 'ADD_MATERIAL',
            'base_qty'           => $baseFloat,
            'plan_qty'           => (float) $calculatedPlanQty,
            'old_plan_qty'       => null,
            'warehouse'          => $this->addWarehouse ? trim($this->addWarehouse) : null,
            'delete'             => false,
            'replaced_item_code' => null,
            'is_new'             => true,
        ];

        $this->flashSuccess = "Material {$itemCode} ditambahkan ke draft edit. Klik 'Selesai & Kirim ke SAP' untuk menerapkan.";
        $this->flashError = null;
        $this->showAddModal = false;
    }

    // ==========================================
    // ACTION 3: HAPUS MATERIAL (STAGING DELETE: TRUE)
    // ==========================================
    public function openDeleteModal(string $spkNumber, string $itemCode, ?string $desc): void
    {
        $this->closeAllModals();
        $this->deleteSpkNumber = $spkNumber;
        $this->deleteItemCode = $itemCode;
        $this->deleteItemDescription = $desc ?: '-';
        $this->showDeleteModal = true;
    }

    public function submitDeleteMaterial(): void
    {
        $this->validate([
            'deleteSpkNumber' => 'required',
            'deleteItemCode'  => 'required',
        ]);

        if (empty($this->editingSpk) || $this->editingSpk !== $this->deleteSpkNumber) {
            $this->startEditMode($this->deleteSpkNumber);
        }

        $itemCode = $this->deleteItemCode;

        // Jika item ini baru saja ditambahkan di draft sesi ini, cukup batalkan/hapus dari stage
        if (isset($this->stagedLines[$itemCode]) && !empty($this->stagedLines[$itemCode]['is_new'])) {
            unset($this->stagedLines[$itemCode]);
            $this->flashSuccess = "Material draft {$itemCode} telah dihapus dari daftar penambahan.";
        } else {
            // Tandai untuk delete di SAP
            $this->stagedLines[$itemCode] = [
                'item_code'    => $itemCode,
                'item_name'    => $this->deleteItemDescription,
                'action_type'  => 'DELETE_MATERIAL',
                'base_qty'     => null,
                'plan_qty'     => null,
                'old_plan_qty' => null,
                'warehouse'    => null,
                'delete'       => true,
                'is_new'       => false,
            ];
            $this->flashSuccess = "Material {$itemCode} ditandai untuk dihapus. Klik 'Selesai & Kirim ke SAP' untuk menerapkan.";
        }

        $this->flashError = null;
        $this->showDeleteModal = false;
    }

    // ==========================================
    // ACTION 4: GANTI MATERIAL (REPLACE & STAGING)
    // ==========================================
    public function openReplaceModal(string $spkNumber, string $oldItemCode, ?string $desc, ?float $plannedQty = null): void
    {
        $this->closeAllModals();
        $this->replaceSpkNumber = $spkNumber;
        $this->replaceOldItemCode = $oldItemCode;
        $this->replaceOldItemDescription = $desc ?: '-';
        $this->replaceNewItemCode = '';
        $this->replaceNewItemName = '';
        $this->showReplaceDropdown = false;
        $this->replacePlanQty = null;
        $this->replaceBaseQty = null;
        $this->replaceWarehouse = '';

        if ($plannedQty === null) {
            $plannedQty = (float) SpkMaster::where('spk_number', $spkNumber)->value('planned_quantity');
        }
        $this->replaceSpkPlannedQty = (float) $plannedQty;
        $this->showReplaceModal = true;
    }

    public function updatedReplaceNewItemCode(): void
    {
        $this->showReplaceDropdown = true;
        $match = MasterListItem::where('item_code', trim($this->replaceNewItemCode))->first();
        if ($match) {
            $this->replaceNewItemName = $match->item_name ?: '';
        } else {
            $this->replaceNewItemName = '';
        }
    }

    public function getReplaceItemSuggestionsProperty(): array
    {
        $term = trim($this->replaceNewItemCode);
        if (strlen($term) < 1) {
            return [];
        }
        return MasterListItem::where(function ($q) use ($term) {
                $q->where('item_code', 'like', "%{$term}%")
                  ->orWhere('item_name', 'like', "%{$term}%");
            })
            ->limit(15)
            ->get(['item_code', 'item_name'])
            ->toArray();
    }

    public function selectReplaceMaterial(string $itemCode, ?string $itemName = null): void
    {
        $this->replaceNewItemCode = $itemCode;
        $this->replaceNewItemName = $itemName ?: '';
        $this->showReplaceDropdown = false;
    }

    public function updatedReplaceBaseQty(): void
    {
        $clean = str_replace(',', '.', trim((string) $this->replaceBaseQty));
        if ($clean !== '' && is_numeric($clean)) {
            $base = (float) $clean;
            if ($base > 0 && $this->replaceSpkPlannedQty > 0) {
                $this->replacePlanQty = round($base * $this->replaceSpkPlannedQty, 4);
                return;
            }
        }
        $this->replacePlanQty = null;
    }

    public function submitReplaceMaterial(): void
    {
        if ($this->replaceBaseQty !== null) {
            $this->replaceBaseQty = str_replace(',', '.', trim((string) $this->replaceBaseQty));
        }

        $this->validate([
            'replaceSpkNumber'   => 'required',
            'replaceOldItemCode' => 'required',
            'replaceNewItemCode' => 'required|string',
            'replaceBaseQty'     => 'required|numeric|gt:0',
        ], [
            'replaceNewItemCode.required' => 'Kode material pengganti wajib diisi atau dipilih dari dropdown.',
            'replaceBaseQty.required'     => 'Base Qty (kebutuhan per unit) material pengganti wajib diisi.',
            'replaceBaseQty.numeric'      => 'Base Qty harus berupa angka desimal valid (contoh: 0.15).',
            'replaceBaseQty.gt'           => 'Base Qty harus lebih besar dari 0.',
        ]);

        if (empty($this->editingSpk) || $this->editingSpk !== $this->replaceSpkNumber) {
            $this->startEditMode($this->replaceSpkNumber);
        }

        $baseFloat = (float) $this->replaceBaseQty;
        $calculatedPlanQty = round($baseFloat * (float) $this->replaceSpkPlannedQty, 4);
        if ($calculatedPlanQty <= 0) {
            $calculatedPlanQty = $baseFloat;
        }

        $oldItem = $this->replaceOldItemCode;
        $newItem = trim($this->replaceNewItemCode);

        // 1. Tandai item lama untuk delete
        $this->stagedLines[$oldItem] = [
            'item_code'    => $oldItem,
            'item_name'    => $this->replaceOldItemDescription,
            'action_type'  => 'DELETE_MATERIAL',
            'base_qty'     => null,
            'plan_qty'     => null,
            'old_plan_qty' => null,
            'warehouse'    => null,
            'delete'       => true,
            'replaced_by'  => $newItem,
            'is_new'       => false,
        ];

        // 2. Tambahkan item baru
        $this->stagedLines[$newItem] = [
            'item_code'          => $newItem,
            'item_name'          => trim($this->replaceNewItemName),
            'action_type'        => 'REPLACE_MATERIAL',
            'base_qty'           => $baseFloat,
            'plan_qty'           => (float) $calculatedPlanQty,
            'old_plan_qty'       => null,
            'warehouse'          => $this->replaceWarehouse ? trim($this->replaceWarehouse) : null,
            'delete'             => false,
            'replaced_item_code' => $oldItem,
            'is_new'             => true,
        ];

        $this->flashSuccess = "Penggantian {$oldItem} dengan {$newItem} disimpan ke draft. Klik 'Selesai & Kirim ke SAP' untuk menerapkan.";
        $this->flashError = null;
        $this->showReplaceModal = false;
    }

    // ==========================================
    // ACTION 5: LIHAT RIWAYAT LOG PERUBAHAN (HISTORY)
    // ==========================================
    public function openHistoryModal(?string $spkNumber = null): void
    {
        $this->closeAllModals();
        $this->historySpkNumber = $spkNumber;

        $query = SpkBomChangeLog::latest();
        if ($spkNumber) {
            $query->where('spk_number', $spkNumber);
        }

        $this->historyLogs = $query->limit(100)->get()->toArray();
        $this->showHistoryModal = true;
    }

    public function closeAllModals(): void
    {
        $this->showEditModal = false;
        $this->showAddModal = false;
        $this->showAddDropdown = false;
        $this->showDeleteModal = false;
        $this->showReplaceModal = false;
        $this->showReplaceDropdown = false;
        $this->showHistoryModal = false;
    }

    public function render()
    {
        $query = SpkMaster::with(['masterItem', 'fgHeader'])->withCount('bomChangeLogs');

        // 1. Filter Pencarian
        if (!empty(trim($this->search))) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('spk_number', 'like', "%{$term}%")
                  ->orWhere('item_code', 'like', "%{$term}%")
                  ->orWhere('production_status', 'like', "%{$term}%")
                  ->orWhereHas('masterItem', function ($sub) use ($term) {
                      $sub->where('item_name', 'like', "%{$term}%");
                  });
            });
        }

        // 2. Filter Status
        if (!empty($this->statusFilter)) {
            $query->where('production_status', $this->statusFilter);
        }

        // 3. Filter Ketersediaan BOM
        if ($this->bomFilter === 'with_bom') {
            $validFgCodes = MasterBomFgHeader::pluck('fg_item_code')->toArray();
            $stagingParents = MasterBom::select('parent_item')->distinct()->pluck('parent_item')->toArray();
            $allBomCodes = array_values(array_unique(array_merge($validFgCodes, $stagingParents)));
            $query->whereIn('item_code', $allBomCodes);
        } elseif ($this->bomFilter === 'without_bom') {
            $validFgCodes = MasterBomFgHeader::pluck('fg_item_code')->toArray();
            $stagingParents = MasterBom::select('parent_item')->distinct()->pluck('parent_item')->toArray();
            $allBomCodes = array_values(array_unique(array_merge($validFgCodes, $stagingParents)));
            $query->whereNotIn('item_code', $allBomCodes);
        }

        // Paginate SPK Masters
        $spks = $query->orderByDesc('post_date')
            ->orderBy('spk_number')
            ->paginate($this->perPage);

        // Ringkasan Statistik Global
        $totalSpk = SpkMaster::count();
        $totalReleased = SpkMaster::where('production_status', 'R')->count();
        $totalPlanned = SpkMaster::where('production_status', 'P')->count();
        $totalPlannedPcs = SpkMaster::sum('planned_quantity');
        $totalBomChanges = SpkBomChangeLog::count();

        // Status List untuk filter dropdown
        $statusList = SpkMaster::whereNotNull('production_status')
            ->where('production_status', '!=', '')
            ->distinct()
            ->pluck('production_status')
            ->toArray();

        return view('livewire.spk-bom-changes-view', [
            'spks'             => $spks,
            'totalSpk'         => $totalSpk,
            'totalReleased'    => $totalReleased,
            'totalPlanned'     => $totalPlanned,
            'totalPlannedPcs'  => $totalPlannedPcs,
            'totalBomChanges'  => $totalBomChanges,
            'statusList'       => $statusList,
        ]);
    }
}
