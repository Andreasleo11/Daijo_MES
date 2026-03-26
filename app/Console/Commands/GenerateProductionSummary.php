<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductionScannedData;
use App\Models\ProductionSummary;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateProductionSummary extends Command
{
    protected $signature = 'summary:generate';
    protected $description = 'Generate production summary every 5 minutes';

    public function handle()
    {
        DB::beginTransaction();
        try {
            // Get unprocessed data
            $unprocessedData = ProductionScannedData::where('processed', false)->get();

            if ($unprocessedData->isEmpty()) {
                $this->info('No new data to process.');
                return;
            }

            // Group by spk_code
            $summaries = $unprocessedData->groupBy('spk_code');

            $processedIds = [];

                foreach ($summaries as $spk_code => $group) {
                    $total_quantity = $group->sum('quantity');
                    $first          = $group->first();
                    $warehouse      = $first->warehouse;
                    $created_date   = $first->created_at->toDateString();

                    $summary = ProductionSummary::create([
                        'spk_code'       => $spk_code,
                        'total_quantity' => $total_quantity,
                        'warehouse'      => $warehouse,
                        'label'          => 'all',
                        'created_date'   => $created_date,
                        'sap_sent'       => 0,
                        'sap_sent_at'    => null,
                    ]);

                    $groupIds     = $group->pluck('id')->toArray();
                    $processedIds = array_merge($processedIds, $groupIds);

                    // ← ini harus di dalam loop, tiap group update pakai summary->id masing-masing
                    ProductionScannedData::whereIn('id', $groupIds)
                        ->update([
                            'processed'  => true,
                            'summary_id' => $summary->id,
                        ]);
                }
            // Mark processed records
            // INI UNTUK UPDATE PROCESSED JADI TRUE NANTI KALAU METHOD SUDAH AMAN 
            // ProductionScannedData::whereIn('id', $processedIds)->update(['processed' => true]);
          

            // Kumpulkan semua payload yang dibuat
            $allPayloads = [];
            foreach ($summaries as $spk_code => $group) {
                $first = $group->first();
                $allPayloads[] = [
                    'spk_code'       => $spk_code,
                    'total_quantity' => $group->sum('quantity'),
                    'warehouse'      => $first->warehouse,
                    'label'          => 'all',
                    'used_label'     => $group->pluck('label')->unique()->values()->toArray(),
                    'created_date'   => $first->created_at->toDateString(),
                    'records_count'  => $group->count(),
                ];
            }

            DB::table('api_logs')->insert([
                'api_name'         => 'PRODUCTION_SUMMARY',
                'method'           => 'INTERNAL',
                'endpoint'         => 'summary:generate',
                'request_payload'  => json_encode(['processed_ids' => $processedIds], JSON_PRETTY_PRINT),
                'response_payload' => json_encode([
                    'generated_at'   => now()->toDateTimeString(),
                    'total_spk'      => count($allPayloads),
                    'total_records'  => count($processedIds),
                    'label_used'     => collect($allPayloads)->pluck('label')->unique()->values(),
                    'summaries'      => $allPayloads,
                ], JSON_PRETTY_PRINT),
                'status_code'      => 200,
                'status'           => 'success',
                'message'          => 'Generated ' . count($allPayloads) . ' summaries from ' . count($processedIds) . ' records',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();
            $this->info('Production summary generated and data marked as processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error generating summary: ' . $e->getMessage());
        }
    }
}
