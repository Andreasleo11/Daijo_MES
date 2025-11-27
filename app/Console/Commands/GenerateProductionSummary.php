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
                // Sum quantity for this spk_code
                $total_quantity = $group->sum('quantity');

                // Get the first record’s warehouse and created_at date
                $first = $group->first();
                $warehouse = $first->warehouse;
                $created_date = $first->created_at->toDateString();

                // Insert into summary table
                ProductionSummary::create([
                    'spk_code'       => $spk_code,
                    'total_quantity' => $total_quantity,
                    'warehouse'      => $warehouse,
                    'label'          => 'all', // Label is always 'all'
                    'created_date'   => $created_date,
                ]);

                // Collect IDs of processed records
                $processedIds = array_merge($processedIds, $group->pluck('id')->toArray());
            }

            // Mark processed records
            // INI UNTUK UPDATE PROCESSED JADI TRUE NANTI KALAU METHOD SUDAH AMAN 
            ProductionScannedData::whereIn('id', $processedIds)->update(['processed' => true]);

            DB::commit();
            $this->info('Production summary generated and data marked as processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error generating summary: ' . $e->getMessage());
        }
    }
}
