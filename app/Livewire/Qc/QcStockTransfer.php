<?php

namespace App\Livewire\Qc;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\QcTransferService;
use App\Models\QcTransferLog;

class QcStockTransfer extends Component
{
    use WithPagination;

    public string $filterDate       = '';
    public string $filterSpk        = '';
    public string $filterItemCode   = '';
    public string $filterWarehouse  = '';
    public string $filterQcStatus   = 'pending'; // 'pending' (0,2), 'completed' (1), 'all'
    public int    $perPage          = 25;

    public array $expandedRows   = [];
    public array $rowDetails     = [];
    public array $ngInputs       = []; // [scanned_data_id => ng_qty]
    public array $remarksInputs  = []; // [scanned_data_id => remarks]
    public array $processingRows = []; // [id => true]

    public function mount(): void
    {
        Carbon::setLocale('id');
        // Keep filterDate empty by default so all pending QC items display
        $this->filterDate = '';
    }

    public function updatingFilterDate(): void     { $this->resetPage(); }
    public function updatingFilterSpk(): void      { $this->resetPage(); }
    public function updatingFilterItemCode(): void { $this->resetPage(); }
    public function updatingFilterWarehouse(): void{ $this->resetPage(); }
    public function updatingFilterQcStatus(): void { $this->resetPage(); }

    private function baseQuery()
    {
        return DB::table('production_summary')
            ->where('sap_sent', 1) // Must be receipted to SAP first
            ->whereIn('warehouse', ['FFI', 'KRFFI'])
            ->when($this->filterWarehouse, fn($q) => $q->where('warehouse', $this->filterWarehouse))
            ->when($this->filterDate, fn($q) => $q->where('created_date', $this->filterDate))
            ->when($this->filterSpk, fn($q) => $q->where('spk_code', 'like', "%{$this->filterSpk}%"))
            ->when($this->filterQcStatus === 'pending', fn($q) => $q->whereIn(DB::raw('COALESCE(qc_status, 0)'), [0, 2]))
            ->when($this->filterQcStatus === 'completed', fn($q) => $q->where('qc_status', 1))
            ->when($this->filterItemCode, function($q) {
                $q->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('production_scanned_data')
                        ->whereColumn('production_scanned_data.spk_code', 'production_summary.spk_code')
                        ->where('production_scanned_data.item_code', 'like', "%{$this->filterItemCode}%");
                });
            });
    }

    public function getSummariesProperty()
    {
        $paginated = $this->baseQuery()
            ->select(
                'production_summary.id',
                'production_summary.spk_code',
                'production_summary.total_quantity',
                'production_summary.warehouse',
                'production_summary.label',
                'production_summary.sap_sent',
                'production_summary.sap_sent_at',
                'production_summary.qc_status',
                'production_summary.created_date',
                'production_summary.created_at'
            )
            ->orderBy('production_summary.created_date', 'desc')
            ->orderBy('production_summary.id', 'desc')
            ->paginate($this->perPage);

        // Map item_code and box counts
        $summaryIds = $paginated->pluck('id')->toArray();
        $spkCodes   = $paginated->pluck('spk_code')->unique()->toArray();

        $itemCodesMap = [];
        if (!empty($spkCodes)) {
            $itemCodesMap = DB::table('production_scanned_data')
                ->whereIn('spk_code', $spkCodes)
                ->groupBy('spk_code')
                ->select('spk_code', DB::raw('MIN(item_code) as item_code'))
                ->pluck('item_code', 'spk_code')
                ->toArray();
        }

        $boxCountsMap = [];
        $inspectedCountsMap = [];
        if (!empty($summaryIds)) {
            $boxCountsMap = DB::table('production_scanned_data')
                ->whereIn('summary_id', $summaryIds)
                ->groupBy('summary_id')
                ->select('summary_id', DB::raw('COUNT(*) as total_boxes'))
                ->pluck('total_boxes', 'summary_id')
                ->toArray();

            $inspectedCountsMap = QcTransferLog::whereIn('production_summary_id', $summaryIds)
                ->groupBy('production_summary_id')
                ->select('production_summary_id', DB::raw('COUNT(*) as inspected_boxes'))
                ->pluck('inspected_boxes', 'production_summary_id')
                ->toArray();
        }

        foreach ($paginated->items() as $item) {
            $item->item_code       = $itemCodesMap[$item->spk_code] ?? '—';
            $item->total_boxes     = $boxCountsMap[$item->id] ?? 0;
            $item->inspected_boxes = $inspectedCountsMap[$item->id] ?? 0;
        }

        return $paginated;
    }

    public function getStatsProperty()
    {
        $base = DB::table('production_summary')
            ->where('sap_sent', 1)
            ->whereIn('warehouse', ['FFI', 'KRFFI'])
            ->when($this->filterWarehouse, fn($q) => $q->where('warehouse', $this->filterWarehouse))
            ->when($this->filterDate, fn($q) => $q->where('created_date', $this->filterDate));

        $res = (clone $base)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN COALESCE(qc_status,0) = 0 THEN 1 ELSE 0 END) as uninspected,
                SUM(CASE WHEN qc_status = 2 THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qc_status = 1 THEN 1 ELSE 0 END) as completed,
                SUM(total_quantity) as total_qty
            ')
            ->first();

        return [
            'total'       => $res->total ?? 0,
            'uninspected' => $res->uninspected ?? 0,
            'partial'     => $res->partial ?? 0,
            'completed'   => $res->completed ?? 0,
            'total_qty'   => $res->total_qty ?? 0,
        ];
    }

    public function toggleDetail(int $summaryId): void
    {
        if (isset($this->expandedRows[$summaryId])) {
            unset($this->expandedRows[$summaryId]);
            unset($this->rowDetails[$summaryId]);
            return;
        }

        $this->expandedRows[$summaryId] = true;
        $this->loadRowDetails($summaryId);
    }

    private function loadRowDetails(int $summaryId): void
    {
        $boxes = DB::table('production_scanned_data')
            ->where('summary_id', $summaryId)
            ->select('id', 'spk_code', 'item_code', 'quantity', 'label', 'user', 'created_at')
            ->orderBy('id', 'asc')
            ->get();

        $logs = QcTransferLog::where('production_summary_id', $summaryId)
            ->get()
            ->keyBy('scanned_data_id');

        $details = [];
        foreach ($boxes as $box) {
            $log = $logs[$box->id] ?? null;
            $details[] = [
                'id'           => $box->id,
                'spk_code'     => $box->spk_code,
                'item_code'    => $box->item_code,
                'quantity'     => (int)$box->quantity,
                'label'        => $box->label,
                'user'         => $box->user,
                'created_at'   => Carbon::parse($box->created_at)->timezone('Asia/Jakarta')->format('H:i:s'),
                'is_inspected' => $log !== null,
                'log'          => $log ? [
                    'id'              => $log->id,
                    'ok_qty'          => $log->ok_qty,
                    'ng_qty'          => $log->ng_qty,
                    'ok_to_warehouse' => $log->ok_to_warehouse,
                    'ng_to_warehouse' => $log->ng_to_warehouse,
                    'ok_sap_status'   => $log->ok_sap_status,
                    'ok_sap_error'    => $log->ok_sap_error,
                    'ng_sap_status'   => $log->ng_sap_status,
                    'ng_sap_error'    => $log->ng_sap_error,
                    'remarks'         => $log->remarks,
                ] : null,
            ];

            // Initialize default NG input to 0 if not set
            if (!isset($this->ngInputs[$box->id])) {
                $this->ngInputs[$box->id] = 0;
            }
        }

        $this->rowDetails[$summaryId] = $details;
    }

    public function submitSingleBox(int $scannedDataId, int $summaryId, QcTransferService $service): void
    {
        $key = 'box_' . $scannedDataId;
        $this->processingRows[$key] = true;

        try {
            $ngQty = (int)($this->ngInputs[$scannedDataId] ?? 0);
            $remarks = $this->remarksInputs[$scannedDataId] ?? null;
            $userId = Auth::id();

            $res = $service->processSingleBoxInspection($scannedDataId, $ngQty, $userId, $remarks);

            if ($res['success']) {
                $this->dispatch('push-notification', [
                    'status' => 'success',
                    'message' => $res['message']
                ]);
                $this->loadRowDetails($summaryId);
            } else {
                $this->dispatch('push-notification', [
                    'status' => 'error',
                    'message' => $res['message']
                ]);
            }
        } catch (\Throwable $e) {
            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        } finally {
            unset($this->processingRows[$key]);
        }
    }

    public function submitWholeSummary(int $summaryId, QcTransferService $service): void
    {
        $key = 'summary_' . $summaryId;
        $this->processingRows[$key] = true;

        try {
            if (!isset($this->rowDetails[$summaryId])) {
                $this->loadRowDetails($summaryId);
            }

            $boxNgMap = [];
            $remarksMap = [];
            foreach ($this->rowDetails[$summaryId] as $box) {
                if (!$box['is_inspected']) {
                    $boxId = $box['id'];
                    $boxNgMap[$boxId] = (int)($this->ngInputs[$boxId] ?? 0);
                }
            }

            if (empty($boxNgMap)) {
                $this->dispatch('push-notification', [
                    'status' => 'warning',
                    'message' => 'Semua box pada summary ini sudah selesai diinspeksi.'
                ]);
                return;
            }

            $userId = Auth::id();
            $res = $service->processSummaryInspection($summaryId, $boxNgMap, $userId);

            if ($res['success']) {
                $this->dispatch('push-notification', [
                    'status' => 'success',
                    'message' => $res['message']
                ]);
            } else {
                $this->dispatch('push-notification', [
                    'status' => 'warning',
                    'message' => $res['message']
                ]);
            }

            $this->loadRowDetails($summaryId);
        } catch (\Throwable $e) {
            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        } finally {
            unset($this->processingRows[$key]);
        }
    }

    public function retryTransfer(int $logId, QcTransferService $service): void
    {
        try {
            $log = QcTransferLog::find($logId);
            if (!$log) {
                $this->dispatch('push-notification', ['status' => 'error', 'message' => 'Log tidak ditemukan']);
                return;
            }

            $res = $service->executeSapTransfers($log);

            if ($res['ok_success'] && $res['ng_success']) {
                $this->dispatch('push-notification', ['status' => 'success', 'message' => "Retry transfer log #{$logId} berhasil."]);
            } else {
                $msg = implode(', ', $res['messages']);
                $this->dispatch('push-notification', ['status' => 'error', 'message' => "Retry transfer log #{$logId} gagal: {$msg}"]);
            }

            if (isset($this->rowDetails[$log->production_summary_id])) {
                $this->loadRowDetails($log->production_summary_id);
            }
        } catch (\Throwable $e) {
            $this->dispatch('push-notification', ['status' => 'error', 'message' => 'Error retry: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.qc.qc-stock-transfer');
    }
}
