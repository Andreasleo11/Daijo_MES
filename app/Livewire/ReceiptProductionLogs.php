<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\ReceiptProductionService;
use App\Jobs\PushSingleReceiptProductionJob;

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

    public array $selectedLogs = [];
    public bool  $selectAll    = false;

    public function mount(): void
    {
        Carbon::setLocale('id');
        $this->filterDate = now()->timezone('Asia/Jakarta')->format('Y-m-d');
    }

    public function updatingFilterItemCode() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatingFilterSpk() 
    { 
        $this->resetPage(); 
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatingFilterWarehouse() 
    { 
        $this->resetPage(); 
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatingFilterDate() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatingFilterStatus() 
    { 
        $this->resetPage(); 
        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatedPage(): void
    {
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedLogs = collect($this->logs->items())
                ->filter(fn($row) => in_array($row->sap_sent, [0, 2, 3]))
                ->pluck('id')
                ->map(fn($id) => (string)$id)
                ->toArray();
        } else {
            $this->selectedLogs = [];
        }
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

    public function markAsSuccess(int $id): void
    {
        DB::table('production_summary')
            ->where('id', $id)
            ->update([
                'sap_sent'    => 1,
                'sap_sent_at' => now(),
                'updated_at'  => now(),
            ]);

        cache()->forget("receipt_stats_{$this->filterDate}");
        $this->dispatch('push-notification', ['status' => 'success', 'message' => 'SPK berhasil ditandai sebagai sukses']);
    }

    public function ignoreAllFiltered(): void
    {
        if ($this->filterStatus === 'sent' || $this->filterStatus === 'ignored') {
            $this->dispatch('push-notification', ['status' => 'warning', 'message' => 'Tidak ada data pending terfilter untuk diabaikan']);
            return;
        }

        $query = DB::table('production_summary')
            ->whereIn('warehouse', ['FFI', 'KRFFI'])
            ->whereIn('production_summary.sap_sent', [0, 2, 3])
            ->when($this->filterWarehouse, fn($q) =>
                $q->where('warehouse', $this->filterWarehouse)
            )
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
            );

        $ids = $query->pluck('production_summary.id')->toArray();

        if (empty($ids)) {
            $this->dispatch('push-notification', ['status' => 'warning', 'message' => 'Tidak ada data pending terfilter untuk diabaikan']);
            return;
        }

        DB::table('production_summary')
            ->whereIn('id', $ids)
            ->update([
                'sap_sent'   => 99,
                'updated_at' => now(),
            ]);

        $cacheKey = "receipt_stats_{$this->filterDate}_{$this->filterStatus}_{$this->filterSpk}_{$this->filterItemCode}_{$this->filterWarehouse}";
        cache()->forget($cacheKey);
        cache()->forget("receipt_stats_{$this->filterDate}");

        $this->dispatch('push-notification', ['status' => 'success', 'message' => count($ids) . ' SPK berhasil diabaikan']);
    }

    public function ignoreSelected(): void
    {
        if (empty($this->selectedLogs)) {
            $this->dispatch('push-notification', ['status' => 'warning', 'message' => 'Tidak ada data terpilih untuk diabaikan']);
            return;
        }

        DB::table('production_summary')
            ->whereIn('id', $this->selectedLogs)
            ->update([
                'sap_sent'   => 99,
                'updated_at' => now(),
            ]);

        $count = count($this->selectedLogs);

        $this->selectedLogs = [];
        $this->selectAll = false;

        $cacheKey = "receipt_stats_{$this->filterDate}_{$this->filterStatus}_{$this->filterSpk}_{$this->filterItemCode}_{$this->filterWarehouse}";
        cache()->forget($cacheKey);
        cache()->forget("receipt_stats_{$this->filterDate}");

        $this->dispatch('push-notification', ['status' => 'success', 'message' => "{$count} SPK berhasil diabaikan"]);
    }

    /**
     * Manual push single SPK to SAP
     */
    public function pushToSapManual(int $summaryId): void
    {
        try {
            // Cek apakah SPK ada dan bisa dikirim
            $summary = DB::table('production_summary')
                ->where('id', $summaryId)
                ->first();

            if (!$summary) {
                $this->dispatch('push-notification', [
                    'status' => 'error',
                    'message' => 'SPK tidak ditemukan'
                ]);
                return;
            }

            // Lock record di UI thread segera agar status berubah jadi "Processing" di UI
            $locked = DB::table('production_summary')
                ->where('id', $summaryId)
                ->whereIn('sap_sent', [0, 3])
                ->update(['sap_sent' => 2, 'updated_at' => now()]);

            if ($locked) {
                // Dispatch job background
                PushSingleReceiptProductionJob::dispatch($summaryId);
                
                cache()->forget("receipt_stats_{$this->filterDate}");

                $this->dispatch('push-notification', [
                    'status' => 'success',
                    'message' => 'SPK ' . $summary->spk_code . ' sedang dikirim ke SAP di background'
                ]);
            } else {
                $this->dispatch('push-notification', [
                    'status' => 'warning',
                    'message' => 'SPK tidak dapat diproses (sedang dikirim atau sudah selesai)'
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Manual push dispatch error', [
                'summary_id' => $summaryId,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Gagal memulai proses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Manual push multiple pending SPKs (Batch)
     */
    public function pushPendingBatchToSap(): void
    {
        try {
            $this->dispatch('batch-push-start');

            // Ambil semua pending records sesuai filter
            // Exclude status 2 (sedang diproses) dari batch push
            $summaries = DB::table('production_summary')
                ->whereIn('warehouse', ['FFI', 'KRFFI'])
                ->when($this->filterWarehouse, fn($q) =>
                    $q->where('warehouse', $this->filterWarehouse)
                )
                ->whereIn('sap_sent', [0, 3]) // Hanya pending atau failed
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

            $dispatchedCount = 0;

            foreach ($summaries as $summary) {
                // Lock record di UI thread segera
                $locked = DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->whereIn('sap_sent', [0, 3])
                    ->update(['sap_sent' => 2, 'updated_at' => now()]);

                if ($locked) {
                    PushSingleReceiptProductionJob::dispatch($summary->id);
                    $dispatchedCount++;
                }
            }

            cache()->forget("receipt_stats_{$this->filterDate}");

            $this->dispatch('push-notification', [
                'status' => 'success',
                'message' => "Batch push dikirim ke background queue: {$dispatchedCount} SPK sedang diproses"
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
     * Push selected items to SAP (Background Queue)
     */
    public function pushSelectedToSap(): void
    {
        if (empty($this->selectedLogs)) {
            $this->dispatch('push-notification', [
                'status' => 'warning',
                'message' => 'Tidak ada SPK terpilih untuk dikirim'
            ]);
            return;
        }

        try {
            $this->dispatch('batch-push-start');

            $summaries = DB::table('production_summary')
                ->whereIn('id', $this->selectedLogs)
                ->whereIn('sap_sent', [0, 3]) // Hanya pending atau failed
                ->get();

            if ($summaries->isEmpty()) {
                $this->dispatch('push-notification', [
                    'status' => 'warning',
                    'message' => 'Tidak ada SPK pending/gagal terpilih yang bisa dikirim'
                ]);
                return;
            }

            $dispatchedCount = 0;

            foreach ($summaries as $summary) {
                // Lock record di UI thread segera agar status berubah jadi "Processing"
                $locked = DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->whereIn('sap_sent', [0, 3])
                    ->update(['sap_sent' => 2, 'updated_at' => now()]);

                if ($locked) {
                    PushSingleReceiptProductionJob::dispatch($summary->id);
                    $dispatchedCount++;
                }
            }

            $this->selectedLogs = [];
            $this->selectAll = false;

            cache()->forget("receipt_stats_{$this->filterDate}");

            $this->dispatch('push-notification', [
                'status' => 'success',
                'message' => "Bulk push terpilih: {$dispatchedCount} SPK dikirim ke background queue"
            ]);

            $this->dispatch('sap-push-success');

        } catch (\Throwable $e) {
            Log::error('Push selected error', ['error' => $e->getMessage()]);
            $this->dispatch('push-notification', [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
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
        $paginatedLogs = $this->baseQuery()
            ->select(
                'production_summary.id',
                'production_summary.spk_code',
                'production_summary.total_quantity',
                'production_summary.warehouse',
                'production_summary.label',
                'production_summary.sap_sent',
                'production_summary.sap_sent_at',
                'production_summary.created_date',
                'production_summary.created_at'
            )
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('production_summary.created_date', $this->filterDate)
            )
            ->when($this->filterSpk, fn($q) =>
                $q->where('production_summary.spk_code', 'like', "%{$this->filterSpk}%")
            )
            ->when($this->filterItemCode, function($q) {
                $q->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('production_scanned_data')
                        ->whereColumn('production_scanned_data.spk_code', 'production_summary.spk_code')
                        ->where('production_scanned_data.item_code', 'like', "%{$this->filterItemCode}%");
                });
            })
            ->when($this->filterStatus === 'sent', fn($q) =>
                $q->where('production_summary.sap_sent', 1)
            )
            ->when($this->filterStatus === 'pending', fn($q) =>
                // Pending = belum terkirim (0), stuck/processing (2), atau gagal/timeout (3)
                $q->whereIn('production_summary.sap_sent', [0, 2, 3])
            )
            ->when($this->filterStatus === 'ignored', fn($q) =>
                $q->where('production_summary.sap_sent', 99)
            )
    
            ->orderBy('production_summary.created_date', 'desc')
            ->orderBy('production_summary.id', 'desc')
            ->paginate($this->perPage);

        // Lazy load item_code untuk 50 baris yang sedang ditampilkan
        $spkCodes = $paginatedLogs->pluck('spk_code')->unique()->toArray();
        $itemCodesMap = [];
        if (!empty($spkCodes)) {
            $itemCodesMap = DB::table('production_scanned_data')
                ->whereIn('spk_code', $spkCodes)
                ->groupBy('spk_code')
                ->select('spk_code', DB::raw('MIN(item_code) as item_code'))
                ->pluck('item_code', 'spk_code')
                ->toArray();
        }

        foreach ($paginatedLogs->items() as $item) {
            $item->item_code = $itemCodesMap[$item->spk_code] ?? '—';
        }

        return $paginatedLogs;
    }

    public function getFilteredTotalQtyProperty()
    {
        return $this->baseQuery()
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('production_summary.created_date', $this->filterDate)
            )
            ->when($this->filterSpk, fn($q) =>
                $q->where('production_summary.spk_code', 'like', "%{$this->filterSpk}%")
            )
            ->when($this->filterItemCode, function($q) {
                $q->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('production_scanned_data')
                        ->whereColumn('production_scanned_data.spk_code', 'production_summary.spk_code')
                        ->where('production_scanned_data.item_code', 'like', "%{$this->filterItemCode}%");
                });
            })
            ->when($this->filterStatus === 'sent',    fn($q) => $q->where('production_summary.sap_sent', 1))
            ->when($this->filterStatus === 'pending', fn($q) => $q->whereIn('production_summary.sap_sent', [0, 2, 3]))
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
                        SUM(CASE WHEN sap_sent IN (0, 2, 3) THEN 1 ELSE 0 END) as pending,
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