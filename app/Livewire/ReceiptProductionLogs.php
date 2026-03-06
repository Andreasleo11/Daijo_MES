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
    public string $filterStatus = '';
    public string $filterSpk    = '';
    public ?int   $expandedId   = null;
    public int    $perPage      = 20;

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function updatingFilterDate()   { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterSpk()    { $this->resetPage(); }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function getLogsProperty()
    {
        return DB::table('api_logs')
            ->where('api_name', 'receipt_production')
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('created_at', $this->filterDate)
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->when($this->filterSpk, fn($q) =>
                $q->where('message', 'like', "%{$this->filterSpk}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->through(function ($log) {
            $request  = json_decode($log->request_payload, true);
            $response = json_decode($log->response_payload, true);

            // Handle kalau request null/kosong/bukan array
            $payloads = [];
            if (is_array($request) && !empty($request)) {
                if (isset($request[0]) && is_array($request[0])) {
                    $payloads = $request;
                } elseif (!empty($request)) {
                    $payloads = [$request];
                }
            }

            $firstPayload = $payloads[0] ?? [];
            $totalQty     = collect($payloads)->sum('quantity');
            $totalItems   = count($payloads);

            // Ambil spk_codes — filter yang null/kosong
            $spkCodes = collect($payloads)
                ->pluck('spk_code')
                ->filter(fn($v) => !is_null($v) && $v !== '')
                ->unique()
                ->values()
                ->toArray();

            // Query production_summary hanya kalau ada spk_codes
            $summaryMap = collect();
            if (!empty($spkCodes)) {
                $summaryMap = DB::table('production_summary')
                    ->whereIn('spk_code', $spkCodes)
                    ->get()
                    ->keyBy('spk_code')
                    ->map(fn($s) => [
                        'sap_sent'    => $s->sap_sent,
                        'sap_sent_at' => $s->sap_sent_at,
                        'total_qty'   => $s->total_quantity,
                        'warehouse'   => $s->warehouse,
                    ]);
            }

            // Enrich payloads
            $enrichedPayloads = collect($payloads)->map(function ($p) use ($summaryMap) {
                $spk     = $p['spk_code'] ?? null;
                $summary = $spk ? $summaryMap->get($spk) : null;
                return array_merge($p, [
                    'sap_sent'    => $summary['sap_sent']    ?? null,
                    'sap_sent_at' => $summary['sap_sent_at'] ?? null,
                ]);
            })->toArray();

            return [
                'id'             => $log->id,
                'status'         => $log->status,
                'status_code'    => $log->status_code,
                'message'        => $log->message,
                'created_at'     => Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'spk_code'       => $totalItems > 1 ? $totalItems . ' SPK' : ($firstPayload['spk_code'] ?? '-'),
                'item_code'      => $totalItems > 1 ? $totalItems . ' item' : ($firstPayload['item_code'] ?? '-'),
                'warehouse'      => $firstPayload['warehouse'] ?? '-',
                'quantity'       => $totalQty,
                'label'          => $firstPayload['label'] ?? '-',
                'is_multi'       => $totalItems > 1,
                'payloads'       => $enrichedPayloads,
                'total_items'    => $totalItems,
                'error_msg'      => $response['message'] ?? $log->message ?? '-',
                'sap_status'     => $response['status']  ?? null,
                'raw_response'   => $log->response_payload,
                'sap_sent'       => $enrichedPayloads[0]['sap_sent']    ?? null,
                'sap_sent_at'    => $enrichedPayloads[0]['sap_sent_at'] ?? null,
                'total_sent'     => collect($enrichedPayloads)->where('sap_sent', 1)->count(),
                'total_not_sent' => collect($enrichedPayloads)->where('sap_sent', 0)->count(),
            ];
        });
    }

    public function getStatsProperty()
    {
        $base = DB::table('api_logs')
            ->where('api_name', 'receipt_production')
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate));

        return [
            'total'   => (clone $base)->count(),
            'success' => (clone $base)->where('status', 'success')->count(),
            'failed'  => (clone $base)->where('status', 'failed')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.receipt-production-logs');
    }
}