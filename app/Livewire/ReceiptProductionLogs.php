<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceiptProductionLogs extends Component
{
    use WithPagination;

    public string $filterDate   = '';
    public string $filterSpk    = '';
    public string $filterStatus = '';
    public int    $perPage      = 50;

    public function mount(): void
    {
        Carbon::setLocale('id');
        $this->filterDate = now()->timezone('Asia/Jakarta')->format('Y-m-d');
    }

    public function updatingFilterDate()   { $this->resetPage(); }
    public function updatingFilterSpk()    { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }

    private function baseQuery()
    {
        return DB::table('production_summary')
            ->where('warehouse', 'FFI'); // exact match → pakai index warehouse
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
            ->when($this->filterStatus === 'sent', fn($q) =>
                $q->where('production_summary.sap_sent', 1)
            )
            ->when($this->filterStatus === 'pending', fn($q) =>
                $q->where('production_summary.sap_sent', 0)
            )
            ->orderBy('production_summary.created_date', 'desc') // pakai created_date yg ada index, bukan created_at
            ->orderBy('production_summary.id', 'desc')           // tiebreaker pakai PK
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        // Cache stats biar ga query ulang tiap render
        return cache()->remember(
            "receipt_stats_{$this->filterDate}",
            now()->addSeconds(30),
            function () {
                $base = DB::table('production_summary')
                    ->where('warehouse', 'FFI')
                    ->when($this->filterDate, fn($q) =>
                        $q->whereDate('created_date', $this->filterDate)
                    );

                // 1 query dengan conditional aggregates, bukan 4 query terpisah
                $result = (clone $base)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN sap_sent = 1 THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN sap_sent = 0 THEN 1 ELSE 0 END) as pending,
                        SUM(total_quantity) as total_qty
                    ')
                    ->first();

                return [
                    'total'     => $result->total     ?? 0,
                    'sent'      => $result->sent       ?? 0,
                    'pending'   => $result->pending    ?? 0,
                    'total_qty' => $result->total_qty  ?? 0,
                ];
            }
        );
    }

    public function render()
    {
        return view('livewire.receipt-production-logs');
    }
}