<?php

namespace App\Livewire;

use App\Models\MasterBom;
use App\Models\MasterBomFgHeader;
use App\Models\MasterBomComponent;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MasterBomView extends Component
{
    use WithPagination, WithFileUploads;

    // Top-Level Main Tab: 'production' (Default) vs 'staging'
    public string $activeMainTab = 'production';

    // ==========================================
    // TAB 1: PRODUCTION MASTER BOM (2 TABLES)
    // ==========================================
    // FG Headers Filter & Search
    public string $fgSearch = '';
    public string $fgProjectFilter = '';
    public string $fgPackagingFilter = 'all'; // all, with_pkg, without_pkg
    public string $fgDepthFilter = 'all'; // all, 1, 2, 3, 4+
    public int $fgPerPage = 15;

    // Multilevel Components Modal for Selected FG
    public ?int $selectedFgId = null;
    public ?MasterBomFgHeader $selectedFg = null;
    public bool $showComponentModal = false;
    public string $compTypeFilter = 'all'; // all, wip, raw, packaging
    public string $compSearch = '';
    public float $compSimQty = 1.0;

    // Reverse BOM (Where-Used) Search
    public string $whereUsedSearch = '';

    // ==========================================
    // TAB 2: STAGING RAW BOM (SAP EXTRACT)
    // ==========================================
    public string $search = '';
    public string $filterType = 'all'; // all, fg, wip, raw
    public string $viewMode = 'grouped'; // 'grouped' or 'flat'
    public int $perPage = 25;

    // Upload State
    public $bomFile;
    public string $uploadMode = 'update'; // 'update' or 'replace'
    public bool $showUploadModal = false;
    public bool $isUploading = false;
    public string $uploadMessage = '';
    public string $uploadError = '';

    // Tree / Detail Modal State (Visual Explorer)
    public ?string $selectedParentItem = null;
    public ?string $selectedParentDesc = null;
    public array $explodedTree = [];
    public array $treeSummaryData = [];
    public string $treeActiveTab = 'tree'; // 'tree' or 'summary'
    public float $simulationQty = 1.0;
    public bool $showTreeModal = false;

    // Delete Confirmation
    public bool $showDeleteAllModal = false;

    protected $queryString = [
        'activeMainTab'       => ['as' => 'tab', 'except' => 'production'],
        'fgSearch'            => ['as' => 'fg_q', 'except' => ''],
        'fgProjectFilter'     => ['as' => 'project', 'except' => ''],
        'fgPackagingFilter'   => ['as' => 'pkg', 'except' => 'all'],
        'fgDepthFilter'       => ['as' => 'depth', 'except' => 'all'],
        'whereUsedSearch'     => ['as' => 'where_used', 'except' => ''],
        'search'              => ['except' => ''],
        'filterType'          => ['except' => 'all'],
        'viewMode'            => ['except' => 'grouped'],
    ];

    public function mount()
    {
        // Jika tabel valid belum ada tapi data staging ada, default ke tab staging
        if (!request()->has('tab')) {
            $validCount = MasterBomFgHeader::count();
            if ($validCount === 0 && MasterBom::count() > 0) {
                $this->activeMainTab = 'staging';
            }
        }
    }

    public function setMainTab(string $tab)
    {
        $this->activeMainTab = in_array($tab, ['production', 'staging']) ? $tab : 'production';
    }

    // Pagination Reset Hooks
    public function updatingFgSearch() { $this->resetPage('fg_page'); }
    public function updatingFgProjectFilter() { $this->resetPage('fg_page'); }
    public function updatingFgPackagingFilter() { $this->resetPage('fg_page'); }
    public function updatingFgDepthFilter() { $this->resetPage('fg_page'); }
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingViewMode() { $this->resetPage(); }

    public function openUploadModal()
    {
        $this->reset(['bomFile', 'uploadMessage', 'uploadError']);
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->reset(['bomFile', 'uploadMessage', 'uploadError']);
    }

    /**
     * Process Upload Excel / CSV
     */
    public function processUpload()
    {
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $this->validate([
            'bomFile' => 'required|file|mimes:xlsx,xls,csv,txt|max:51200', // max 50MB
        ], [
            'bomFile.required' => 'Pilih file Excel (.xlsx, .xls) atau .csv terlebih dahulu.',
            'bomFile.mimes'    => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'bomFile.max'      => 'Ukuran file maksimal adalah 50MB.',
        ]);

        $this->uploadError = '';
        $this->uploadMessage = '';

        try {
            $filePath = $this->bomFile->getRealPath();
            $extension = strtolower($this->bomFile->getClientOriginalExtension());

            $rows = [];

            if (in_array($extension, ['csv', 'txt'])) {
                if (($handle = fopen($filePath, 'r')) !== false) {
                    while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                        if (count($data) === 1 && str_contains($data[0], ';')) {
                            $data = str_getcsv($data[0], ';');
                        }
                        $rowAssoc = [];
                        foreach ($data as $idx => $val) {
                            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                            $rowAssoc[$letter] = $val;
                        }
                        $rows[] = $rowAssoc;
                    }
                    fclose($handle);
                }
            } else {
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);
            }

            if (empty($rows) || count($rows) < 2) {
                $this->uploadError = 'File Excel kosong atau tidak memiliki baris data.';
                return;
            }

            $headerRow = array_shift($rows);
            
            $colIndex = [
                'line_id'        => null,
                'parent_item'    => null,
                'parent_desc'    => null,
                'component_item' => null,
                'component_desc' => null,
                'quantity'       => null,
                'uom'            => null,
            ];

            $descCount = 0;
            foreach ($headerRow as $colLetter => $headerName) {
                $normalized = strtolower(trim((string)$headerName));
                
                if ($normalized === '#' || str_contains($normalized, 'line') || str_contains($normalized, 'no')) {
                    if ($colIndex['line_id'] === null) $colIndex['line_id'] = $colLetter;
                } elseif (str_contains($normalized, 'parent item') || str_contains($normalized, 'father')) {
                    $colIndex['parent_item'] = $colLetter;
                } elseif (str_contains($normalized, 'component item') || str_contains($normalized, 'child')) {
                    $colIndex['component_item'] = $colLetter;
                } elseif (str_contains($normalized, 'description') || str_contains($normalized, 'item desc')) {
                    if ($descCount === 0) {
                        $colIndex['parent_desc'] = $colLetter;
                        $descCount++;
                    } else {
                        $colIndex['component_desc'] = $colLetter;
                    }
                } elseif (str_contains($normalized, 'qty') || str_contains($normalized, 'quantity')) {
                    $colIndex['quantity'] = $colLetter;
                } elseif (str_contains($normalized, 'uom') || str_contains($normalized, 'unit') || str_contains($normalized, 'stock')) {
                    $colIndex['uom'] = $colLetter;
                }
            }

            if ($colIndex['parent_item'] === null) $colIndex['parent_item'] = 'B';
            if ($colIndex['parent_desc'] === null) $colIndex['parent_desc'] = 'C';
            if ($colIndex['component_item'] === null) $colIndex['component_item'] = 'D';
            if ($colIndex['component_desc'] === null) $colIndex['component_desc'] = 'E';
            if ($colIndex['quantity'] === null) $colIndex['quantity'] = 'F';
            if ($colIndex['uom'] === null) $colIndex['uom'] = 'G';

            $recordsToInsert = [];
            $now = now();
            $batchSize = 1000;
            $totalImported = 0;

            if ($this->uploadMode === 'replace') {
                MasterBom::truncate();
            }

            foreach ($rows as $row) {
                $parentItem = isset($row[$colIndex['parent_item']]) ? trim((string)$row[$colIndex['parent_item']]) : '';
                $compItem   = isset($row[$colIndex['component_item']]) ? trim((string)$row[$colIndex['component_item']]) : '';

                if (empty($parentItem) || empty($compItem)) {
                    continue;
                }

                $parentDesc = isset($row[$colIndex['parent_desc']]) ? trim((string)$row[$colIndex['parent_desc']]) : null;
                $compDesc   = isset($row[$colIndex['component_desc']]) ? trim((string)$row[$colIndex['component_desc']]) : null;
                
                $rawQty = isset($row[$colIndex['quantity']]) ? trim((string)$row[$colIndex['quantity']]) : '1';
                $cleanQty = str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $rawQty));
                $qty = is_numeric($cleanQty) ? (float)$cleanQty : 1.0;

                $uom = isset($row[$colIndex['uom']]) ? strtoupper(trim((string)$row[$colIndex['uom']])) : 'PCS';
                $lineId = ($colIndex['line_id'] && isset($row[$colIndex['line_id']])) ? trim((string)$row[$colIndex['line_id']]) : null;

                $recordsToInsert[] = [
                    'sap_line_id'           => $lineId,
                    'parent_item'           => $parentItem,
                    'parent_description'    => $parentDesc,
                    'component_item'        => $compItem,
                    'component_description' => $compDesc,
                    'quantity'              => $qty,
                    'uom'                   => $uom,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];

                if (count($recordsToInsert) >= $batchSize) {
                    MasterBom::insert($recordsToInsert);
                    $totalImported += count($recordsToInsert);
                    $recordsToInsert = [];
                }
            }

            if (!empty($recordsToInsert)) {
                MasterBom::insert($recordsToInsert);
                $totalImported += count($recordsToInsert);
            }

            $this->closeUploadModal();
            $this->resetPage();
            session()->flash('success', "Berhasil mengimpor " . number_format($totalImported) . " relasi BOM dari SAP.");

        } catch (\Throwable $e) {
            $this->uploadError = 'Gagal memproses file: ' . $e->getMessage();
        }
    }

    // ==========================================
    // ACTION FOR TABLE 1 (FG) & TABLE 2 (COMPONENTS)
    // ==========================================

    /**
     * Buka modal rincian multilevel components (Tabel 2: master_bom_components)
     */
    public function showFgComponents(int $fgId)
    {
        $this->selectedFgId = $fgId;
        $this->selectedFg = MasterBomFgHeader::find($fgId);
        $this->compTypeFilter = 'all';
        $this->compSearch = '';
        $this->compSimQty = 1.0;
        $this->showComponentModal = true;
    }

    public function closeComponentModal()
    {
        $this->showComponentModal = false;
        $this->selectedFgId = null;
        $this->selectedFg = null;
    }

    /**
     * Unduh CSV daftar komponen multilevel untuk 1 Finished Good
     */
    public function downloadFgComponentsCsv(int $fgId)
    {
        $fg = MasterBomFgHeader::findOrFail($fgId);
        $components = MasterBomComponent::where('fg_id', $fgId)
            ->orderBy('depth_level')
            ->orderBy('component_item')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"BOM_Multilevel_{$fg->fg_item_code}.csv\"",
        ];

        $callback = function () use ($fg, $components) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Finished Good Item', $fg->fg_item_code, $fg->fg_description, 'Project:', $fg->project_code ?: '-']);
            fputcsv($handle, ['Depth Level', 'Parent Assembly', 'Component Code', 'Component Description', 'Item Type', 'Unit Qty', 'Total Qty per 1 FG', 'UoM', 'Lineage Hierarchy']);

            foreach ($components as $c) {
                fputcsv($handle, [
                    'Level ' . $c->depth_level,
                    $c->parent_item,
                    $c->component_item,
                    $c->component_description,
                    $c->item_type,
                    $c->unit_qty,
                    $c->total_qty_per_fg,
                    $c->uom,
                    $c->lineage_path,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // ACTION FOR VISUAL TREE EXPLORER
    // ==========================================

    public function openTreeModal(string $parentItem, ?string $parentDesc = null)
    {
        $this->selectedParentItem = $parentItem;
        $this->selectedParentDesc = $parentDesc ?: MasterBom::where('parent_item', $parentItem)->value('parent_description');
        $this->simulationQty = 1.0;
        $this->treeActiveTab = 'tree';

        $this->explodedTree = MasterBom::explodeTree($parentItem, 1.0);
        $this->treeSummaryData = MasterBom::getFlattenedSummary($parentItem, 1.0);

        $this->showTreeModal = true;
    }

    public function updateSimulation()
    {
        if (empty($this->selectedParentItem)) return;
        $qty = max(0.0001, (float)$this->simulationQty);
        $this->explodedTree = MasterBom::explodeTree($this->selectedParentItem, $qty);
        $this->treeSummaryData = MasterBom::getFlattenedSummary($this->selectedParentItem, $qty);
    }

    public function closeTreeModal()
    {
        $this->showTreeModal = false;
        $this->selectedParentItem = null;
        $this->selectedParentDesc = null;
        $this->explodedTree = [];
        $this->treeSummaryData = [];
    }

    /**
     * Hapus Seluruh Data Master BOM Staging
     */
    public function deleteAllBoms()
    {
        MasterBom::truncate();
        $this->showDeleteAllModal = false;
        session()->flash('success', 'Seluruh data Master BOM Staging berhasil dihapus.');
    }

    /**
     * Download Sample CSV Template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Template_Master_BOM_SAP.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Parent Item', 'Item Description', 'Component Item', 'Item Description', 'Quantity', 'Stock UoM']);
            fputcsv($handle, ['1', '(RM)-732-CVRDRLR-BL', 'COVER DRL RH', '601-DAIJO15', 'BOX PP BOARD t5 555X445X210', '0.00154', 'PCS']);
            fputcsv($handle, ['2', '(RM)-732-CVRDRLR-BL', 'COVER DRL RH', '400-XR404-BK', 'ABS XR404 CMX20007 K94867', '0.07945', 'KG']);
            fputcsv($handle, ['6', '1-31000266345', 'PRINT HANDLE PANEL VP6915', '0-31000266065', 'HANDLE-PANEL (WHITE)', '1', 'PCS']);
            fputcsv($handle, ['7', '1-31000266345', 'PRINT HANDLE PANEL VP6915', '450-DSPA-001', 'THINNER DSPA-001', '0.00126', 'LT']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sinkronkan data staging master_boms ke 2 tabel produksi (FG Headers & Multilevel Components)
     */
    public function syncToProductionTables()
    {
        try {
            $service = app(\App\Services\MasterBomSyncService::class);
            $result = $service->syncAll();

            if ($result['status'] === 'empty') {
                session()->flash('error', $result['message']);
            } else {
                $anomalyText = !empty($result['anomalies']) 
                    ? " (" . count($result['anomalies']) . " Project Dummy SAP seperti SHAD TOP CASE berhasil dipetakan ke project_code)" 
                    : "";
                $msg = "⚡ Sukses! " . number_format($result['fgs_count']) . " FG Headers & " . number_format($result['components_count']) . " komponen produksi berhasil disimpan{$anomalyText}.";
                session()->flash('success', $msg);
                $this->activeMainTab = 'production';
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Ringkasan Statistik Global
        $totalRecords = MasterBom::count();
        $totalParents = MasterBom::distinct('parent_item')->count('parent_item');
        $totalComponents = MasterBom::distinct('component_item')->count('component_item');

        // Statistik Tabel Produksi (2 Tabel Valid)
        $totalValidFg = MasterBomFgHeader::count();
        $totalValidComp = MasterBomComponent::count();

        // Subquery WIP count staging
        $totalWip = DB::table('master_boms as b1')
            ->join('master_boms as b2', 'b1.component_item', '=', 'b2.parent_item')
            ->distinct('b1.component_item')
            ->count('b1.component_item');

        // TAB 1: PRODUCTION MASTER BOM QUERY
        $fgHeaders = null;
        $projectList = collect();
        $modalComponents = collect();
        $whereUsedResults = collect();

        if ($this->activeMainTab === 'production') {
            $fgQuery = MasterBomFgHeader::query();

            if (!empty(trim($this->fgSearch))) {
                $term = trim($this->fgSearch);
                $fgQuery->where(function ($q) use ($term) {
                    $q->where('fg_item_code', 'like', "%{$term}%")
                      ->orWhere('fg_description', 'like', "%{$term}%")
                      ->orWhere('project_code', 'like', "%{$term}%")
                      ->orWhere('customer_name', 'like', "%{$term}%");
                });
            }

            if (!empty($this->fgProjectFilter)) {
                if ($this->fgProjectFilter === 'NO_PROJECT') {
                    $fgQuery->whereNull('project_code');
                } else {
                    $fgQuery->where('project_code', $this->fgProjectFilter);
                }
            }

            if ($this->fgPackagingFilter === 'with_pkg') {
                $fgQuery->where('has_packaging', true);
            } elseif ($this->fgPackagingFilter === 'without_pkg') {
                $fgQuery->where('has_packaging', false);
            }

            if ($this->fgDepthFilter !== 'all') {
                if ($this->fgDepthFilter === '4+') {
                    $fgQuery->where('max_depth_level', '>=', 4);
                } else {
                    $fgQuery->where('max_depth_level', (int)$this->fgDepthFilter);
                }
            }

            $fgHeaders = $fgQuery->orderByRaw('CASE WHEN project_code IS NOT NULL THEN 0 ELSE 1 END')
                ->orderBy('project_code')
                ->orderBy('fg_item_code')
                ->paginate($this->fgPerPage, ['*'], 'fg_page');

            $projectList = MasterBomFgHeader::whereNotNull('project_code')
                ->select('project_code', DB::raw('count(*) as count'))
                ->groupBy('project_code')
                ->orderBy('project_code')
                ->get();

            // Fetch modal components jika sedang aktif
            if ($this->showComponentModal && $this->selectedFgId) {
                $compQuery = MasterBomComponent::where('fg_id', $this->selectedFgId);

                if (!empty(trim($this->compSearch))) {
                    $cterm = trim($this->compSearch);
                    $compQuery->where(function ($q) use ($cterm) {
                        $q->where('component_item', 'like', "%{$cterm}%")
                          ->orWhere('component_description', 'like', "%{$cterm}%")
                          ->orWhere('parent_item', 'like', "%{$cterm}%");
                    });
                }

                if ($this->compTypeFilter === 'wip') {
                    $compQuery->where('item_type', 'WIP');
                } elseif ($this->compTypeFilter === 'raw') {
                    $compQuery->where('item_type', 'RAW_MATERIAL');
                } elseif ($this->compTypeFilter === 'packaging') {
                    $compQuery->where('item_type', 'PACKAGING');
                }

                $modalComponents = $compQuery->orderBy('depth_level')->orderBy('component_item')->get();
            }

            // Fetch Where-Used (Reverse BOM): Dikelompokkan per Finished Good (FG Unik) agar tidak dobel
            if (!empty(trim($this->whereUsedSearch))) {
                $wTerm = trim($this->whereUsedSearch);
                $whereUsedResults = MasterBomComponent::with('fgHeader')
                    ->whereHas('fgHeader')
                    ->select(
                        'fg_id',
                        'component_item',
                        DB::raw('MAX(component_description) as component_description'),
                        DB::raw('MAX(item_type) as item_type'),
                        DB::raw('MAX(is_wip) as is_wip'),
                        DB::raw('MAX(uom) as uom'),
                        DB::raw('SUM(total_qty_per_fg) as total_qty_per_fg')
                    )
                    ->where(function ($q) use ($wTerm) {
                        $q->where('component_item', 'like', "%{$wTerm}%")
                          ->orWhere('component_description', 'like', "%{$wTerm}%");
                    })
                    ->groupBy('fg_id', 'component_item')
                    ->take(50)
                    ->get();
            }
        }

        // TAB 2: STAGING QUERY
        $parents = null;
        $componentsByParent = null;
        $boms = null;
        $allParentCodes = collect();
        $allComponentCodes = collect();

        if ($this->activeMainTab === 'staging') {
            $allParentCodes = MasterBom::select('parent_item')->distinct()->pluck('parent_item')->flip();
            $allComponentCodes = MasterBom::select('component_item')->distinct()->pluck('component_item')->flip();

            if ($this->viewMode === 'grouped') {
                $query = MasterBom::query()
                    ->select(
                        'parent_item',
                        DB::raw('MAX(parent_description) as parent_description'),
                        DB::raw('COUNT(*) as total_components')
                    )
                    ->groupBy('parent_item');

                if (!empty(trim($this->search))) {
                    $term = trim($this->search);
                    $query->where(function ($q) use ($term) {
                        $q->where('parent_item', 'like', "%{$term}%")
                          ->orWhere('parent_description', 'like', "%{$term}%")
                          ->orWhere('component_item', 'like', "%{$term}%")
                          ->orWhere('component_description', 'like', "%{$term}%");
                    });
                }

                if ($this->filterType === 'wip') {
                    $compCodes = MasterBom::select('component_item')->distinct()->pluck('component_item');
                    $query->whereIn('parent_item', $compCodes);
                } elseif ($this->filterType === 'fg') {
                    $compCodes = MasterBom::select('component_item')->distinct()->pluck('component_item');
                    $query->whereNotIn('parent_item', $compCodes);
                }

                $parents = $query->orderBy('parent_item')->paginate($this->perPage);

                $currentPageParentCodes = $parents->pluck('parent_item')->toArray();
                $componentsByParent = MasterBom::whereIn('parent_item', $currentPageParentCodes)
                    ->orderBy('component_item')
                    ->get()
                    ->groupBy('parent_item');
            } else {
                $query = MasterBom::query();

                if (!empty(trim($this->search))) {
                    $term = trim($this->search);
                    $query->where(function ($q) use ($term) {
                        $q->where('parent_item', 'like', "%{$term}%")
                          ->orWhere('parent_description', 'like', "%{$term}%")
                          ->orWhere('component_item', 'like', "%{$term}%")
                          ->orWhere('component_description', 'like', "%{$term}%");
                    });
                }

                if ($this->filterType === 'wip') {
                    $parentCodes = MasterBom::select('parent_item')->distinct()->pluck('parent_item');
                    $query->whereIn('component_item', $parentCodes);
                } elseif ($this->filterType === 'raw') {
                    $parentCodes = MasterBom::select('parent_item')->distinct()->pluck('parent_item');
                    $query->whereNotIn('component_item', $parentCodes);
                } elseif ($this->filterType === 'fg') {
                    $compCodes = MasterBom::select('component_item')->distinct()->pluck('component_item');
                    $query->whereNotIn('parent_item', $compCodes);
                }

                $boms = $query->orderBy('parent_item')->orderBy('component_item')->paginate($this->perPage);
            }
        }

        return view('livewire.master-bom-view', [
            // Stats
            'totalRecords'       => $totalRecords,
            'totalParents'       => $totalParents,
            'totalComponents'    => $totalComponents,
            'totalWip'           => $totalWip,
            'totalValidFg'       => $totalValidFg,
            'totalValidComp'     => $totalValidComp,

            // Tab 1: Production Data
            'fgHeaders'          => $fgHeaders,
            'projectList'        => $projectList,
            'modalComponents'    => $modalComponents,
            'whereUsedResults'   => $whereUsedResults,

            // Tab 2: Staging Data
            'parents'            => $parents,
            'componentsByParent' => $componentsByParent,
            'boms'               => $boms,
            'allParentCodes'     => $allParentCodes,
            'allComponentCodes'  => $allComponentCodes,
        ]);
    }
}
