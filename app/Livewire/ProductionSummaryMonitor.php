<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionSummaryMonitor extends Component
{
    public string $filterDate = '';
    public ?int   $expandedLog = null;

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedLog = $this->expandedLog === $id ? null : $id;
    }

    public function getSummaryLogsProperty()
    {
        return DB::table('api_logs')
            ->where('api_name', 'PRODUCTION_SUMMARY')
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('created_at', $this->filterDate)
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $payload = json_decode($log->response_payload, true) ?? [];
                return [
                    'id'            => $log->id,
                    'created_at'    => Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('H:i:s'),
                    'date'          => Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d'),
                    'status'        => $log->status,
                    'message'       => $log->message,
                    'generated_at'  => $payload['generated_at'] ?? null,
                    'total_spk'     => $payload['total_spk'] ?? 0,
                    'total_records' => $payload['total_records'] ?? 0,
                    'label_used'    => $payload['label_used'] ?? [],
                    'summaries'     => $payload['summaries'] ?? [],
                    'total_qty'     => collect($payload['summaries'] ?? [])->sum('total_quantity'),
                ];
            });
    }

    public function render()
    {
        return view('livewire.production-summary-monitor');
    }
}