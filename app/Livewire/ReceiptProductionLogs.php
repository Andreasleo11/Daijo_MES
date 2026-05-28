<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\ReceiptProductionService;

class ReceiptProductionLogs extends Component
{
    use WithPagination;

    public string $filterDate   = '';
    public string $filterSpk    = '';
    public string $filterStatus = '';
    public int    $perPage      = 50;

    public string $filterItemCode = '';
    public string $filterWarehouse = '';

    public array $expandedRows = [];
    public array $rowDetails   = [];
    public array $pushingRows  = []; // Track which rows are currently pushing
    public array $pushResults  = []; // Store push results

    public function mount(): void
    {
        Carbon::setLocale('id');
        $this->filterDate = now()->timezone('Asia/Jakarta')->format('Y-m-d');
    }

    public function updatingFilterItemCode() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
    }

    public function updatingFilterSpk() 
    { 
        $this->resetPage(); 
    }

    public function updatingFilterWarehouse() 
    { 
        $this->resetPage(); 
    }

    public function updatingFilterDate() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
    }

    public function updatingFilterStatus() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
    }

    public function markAsIgnored(int $id): void
    {
        DB::table('production_summary')
            ->where('id', $id)
            ->update([
                'sap_sent'    => 99,
                'updated_at'  => now(),
            ]);

        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->dispatch('push-notification', ['status' => 'success', 'message' => 'SPK berhasil diabaikan']);
    }

    public function markAsPending(int $id): void
    {
        DB::table('production_summary')
            ->where('id', $id)
            ->update([
                'sap_sent'    => 0,
                'sap_sent_at' => null,
                'updated_at'  => now(),
            ]);

        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->dispatch('push-notification', ['status' => 'success', 'message' => 'SPK direset ke pending']);
    }

    /**
     * Manual push single SPK to SAP
     */
    public function pushToSapManual(int $summaryId): void
    {
        try {
            $this->pushingRows[$summaryId] = true;

            $summary = DB::table('production_summary')
                ->where('id', $summaryId)
                ->first();

            if (!$summary) {
                $this->pushResults[$summaryId] = [
                    'status' => 'error',
                    'message' => 'SPK tidak ditemukan'
                ];
                unset($this->pushingRows[$summaryId]);
                return;
            }

            // Cari scanned data
            $scannedData = DB::table('production_scanned_data')
                ->where('spk_code', $summary->spk_code)
                ->first();

            if (!$scannedData) {
                $this->pushResults[$summaryId] = [
                    'status' => 'error',
                    'message' => 'Data scanning tidak ditemukan untuk SPK ini'
                ];
                unset($this->pushingRows[$summaryId]);
                return;
            }

            // Gunakan ReceiptProductionService untuk push
            $service = app(ReceiptProductionService::class);
            $response = $service->pushSingleRecord($summary, $scannedData);

            if ($response['success']) {
                $this->pushResults[$summaryId] = [
                    'status' => 'success',
                    'message' => 'SPK berhasil dikirim ke SAP'
                ];
                
                // Refresh logs
                $this->dispatch('sap-push-success');
            } else {
                $this->pushResults[$summaryId] = [
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Gagal mengirim ke SAP'
                ];
            }

            unset($this->pushingRows[$summaryId]);
            cache()->forget("receipt_stats_{$this->filterDate}");

        } catch (\Throwable $e) {
            Log::error('Manual push error', [
                'summary_id' => $summaryId,
                'error' => $e->getMessage(),
            ]);

            $this->pushResults[$summaryId] = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];

            unset($this->pushingRows[$summaryId]);
        }
    }

    /**
     * Manual push multiple pending SPKs (Batch)
     */
    public function pushPendingBatchToSap(): void
    {
        try {
            $this->dispatch('batch-push-start');

            $service = app(ReceiptProductionService::class);
            
            // Ambil semua pending records sesuai filter
            // Exclude status 2 (sedang diproses worker lain) dari batch push
            $summaries = DB::table('production_summary')
                ->whereIn('warehouse', ['FFI', 'KRFFI'])
                ->when($this->filterWarehouse, fn($q) =>
                    $q->where('warehouse', $this->filterWarehouse)
                )
                ->where('sap_sent', 0) // Hanya yang benar-benar pending, bukan yang lagi processing
                ->when($this->filterDate, fn($q) =>
                    $q->whereDate('created_date', $this->filterDate)
                )
                ->when($this->filterSpk, fn($q) =>
                    $q->where('spk_code', 'like', "%{$this->filterSpk}%")
                )
                ->get();

            if ($summaries->isEmpty()) {
                $this->dispatch('push-notification', [
                    'status' => 'warning',
                    'message' => 'Tidak ada SPK pending untuk dikirim'
                ]);
                return;
            }

            $successCount = 0;
            $failCount = 0;

            foreach ($summaries as $summary) {
                $scannedData = DB::table('production_scanned_data')
                    ->where('spk_code', $summary->spk_code)
                    ->first();

                if (!$scannedData) {
                    $failCount++;
                    continue;
                }

                $response = $service->pushSingleRecord($summary, $scannedData);
                if ($response['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            cache()->forget("receipt_stats_{$this->filterDate}");

            $this->dispatch('push-notification', [
                'status' => 'success',
                'message' => "Batch push selesai: {$successCount} berhasil, {$failCount} gagal"
            ]);

            $this->dispatch('sap-push-success');

        } catch (\Throwable $e) {
            Log::error('Batch push error', ['error' => $e->getMessage()]);
            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Error batch push: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Push single scanned detail item (jika perlu)
     */
    public function pushDetailToSap(int $detailId, int $summaryId): void
    {
        try {
            $detail = DB::table('production_scanned_data')
                ->where('id', $detailId)
                ->first();

            if (!$detail) {
                $this->dispatch('push-notification', [
                    'status' => 'error',
                    'message' => 'Detail tidak ditemukan'
                ]);
                return;
            }

            // Get summary for context
            $summary = DB::table('production_summary')
                ->where('id', $summaryId)
                ->first();

            $service = app(ReceiptProductionService::class);
            $response = $service->pushSingleRecord($summary, $detail);

            if ($response['success']) {
                $this->dispatch('push-notification', [
                    'status' => 'success',
                    'message' => 'Detail berhasil dikirim ke SAP'
                ]);
                $this->dispatch('sap-push-success');
            } else {
                $this->dispatch('push-notification', [
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Gagal mengirim detail'
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Detail push error', ['error' => $e->getMessage()]);
            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    private function baseQuery()
    {
        return DB::table('production_summary')
            ->whereIn('warehouse', ['FFI', 'KRFFI'])
            ->when($this->filterWarehouse, fn($q) =>
                $q->where('warehouse', $this->filterWarehouse)
            );
    }

    public function getLogsProperty()
    {
        return $this->baseQuery()
            ->leftJoin(
                DB::raw('(SELECT spk_code, MIN(item_code) as item_code
                          FROM production_scanned_data
                          GROUP BY spk_code) as psd'),
                'production_summary.spk_code', '=', 'psd.spk_code'
            )
            ->select(
                'production_summary.id',
                'production_summary.spk_code',
                'production_summary.total_quantity',
                'production_summary.warehouse',
                'production_summary.label',
                'production_summary.sap_sent',
                'production_summary.sap_sent_at',
                'production_summary.created_date',
                'production_summary.created_at',
                'psd.item_code'
            )
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('production_summary.created_date', $this->filterDate)
            )
           ->when($this->filterSpk, fn($q) =>
                $q->where('production_summary.spk_code', 'like', "%{$this->filterSpk}%")
            )
            ->when($this->filterItemCode, fn($q) =>
                $q->where('psd.item_code', 'like', "%{$this->filterItemCode}%")
            )
            ->when($this->filterStatus === 'sent', fn($q) =>
                $q->where('production_summary.sap_sent', 1)
            )
            ->when($this->filterStatus === 'pending', fn($q) =>
                // Pending = belum terkirim (0) atau stuck/processing (2)
                $q->whereIn('production_summary.sap_sent', [0, 2])
            )
            ->when($this->filterStatus === 'ignored', fn($q) =>
                $q->where('production_summary.sap_sent', 99)
            )
    
            ->orderBy('production_summary.created_date', 'desc')
            ->orderBy('production_summary.id', 'desc')
            ->paginate($this->perPage);
    }

    public function getFilteredTotalQtyProperty()
    {
        return $this->baseQuery()
            ->leftJoin(
                DB::raw('(SELECT spk_code, MIN(item_code) as item_code
                        FROM production_scanned_data
                        GROUP BY spk_code) as psd'),
                'production_summary.spk_code', '=', 'psd.spk_code'
            )
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('production_summary.created_date', $this->filterDate)
            )
            ->when($this->filterSpk, fn($q) =>
                $q->where('production_summary.spk_code', 'like', "%{$this->filterSpk}%")
            )
            ->when($this->filterItemCode, fn($q) =>
                $q->where('psd.item_code', 'like', "%{$this->filterItemCode}%")
            )
            ->when($this->filterStatus === 'sent',    fn($q) => $q->where('production_summary.sap_sent', 1))
            ->when($this->filterStatus === 'pending', fn($q) => $q->whereIn('production_summary.sap_sent', [0, 2]))
            ->when($this->filterStatus === 'ignored', fn($q) => $q->where('production_summary.sap_sent', 99))
            ->sum('production_summary.total_quantity');
    }

    public function getStatsProperty()
    {
        $cacheKey = "receipt_stats_{$this->filterDate}_{$this->filterStatus}_{$this->filterSpk}_{$this->filterItemCode}_{$this->filterWarehouse}";
    
        return cache()->remember(
            $cacheKey,
            now()->addSeconds(30),
            function () {
                $base = DB::table('production_summary')
                    ->whereIn('warehouse', ['FFI', 'KRFFI'])
                    ->when($this->filterWarehouse, fn($q) =>
                        $q->where('warehouse', $this->filterWarehouse)
                    )
                    ->when($this->filterDate, fn($q) =>
                        $q->whereDate('created_date', $this->filterDate)
                    );

                $result = (clone $base)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN sap_sent = 1  THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN sap_sent IN (0, 2) THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN sap_sent = 99 THEN 1 ELSE 0 END) as ignored,
                        SUM(CASE WHEN sap_sent = 2  THEN 1 ELSE 0 END) as processing,
                        SUM(total_quantity) as total_qty
                    ')
                    ->first();

                return [
                    'total'      => $result->total      ?? 0,
                    'sent'       => $result->sent        ?? 0,
                    'pending'    => $result->pending     ?? 0,
                    'ignored'    => $result->ignored     ?? 0,
                    'processing' => $result->processing  ?? 0,
                    'total_qty'  => $result->total_qty   ?? 0,
                ];
            }
        );
    }

    public function toggleDetail(int $summaryId): void
    {
        if (isset($this->expandedRows[$summaryId])) {
            unset($this->expandedRows[$summaryId]);
            unset($this->rowDetails[$summaryId]);
            return;
        }

        $this->expandedRows[$summaryId] = true;

        $this->rowDetails[$summaryId] = DB::table('production_scanned_data')
            ->where('summary_id', $summaryId)
            ->select('id', 'spk_code', 'item_code', 'quantity', 'label', 'user', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'spk_code'   => $d->spk_code,
                'item_code'  => $d->item_code,
                'quantity'   => $d->quantity,
                'label'      => $d->label,
                'user'       => $d->user,
                'created_at' => Carbon::parse($d->created_at)->timezone('Asia/Jakarta')->format('H:i:s'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.receipt-production-logs');
    }
}