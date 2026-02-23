<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Delivery\delsched_stock;
use Carbon\Carbon;
use DateTime;

class ProcessDelschedStep4 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 1;

    public function handle()
    {
        $this->updateLog('delsched_main', 'step4', 'running', 'Step 4: Kalkulasi stock...');

        try {
            // Insert stock per item
            $tab_delsched_final_item = DB::table('delsched_final')->select('item_code')->distinct()->get();

            foreach ($tab_delsched_final_item as $item) {
                $tab_sap_inventoryfg = DB::table('sap_inventory_fg')->where('item_code', '=', $item->item_code)->first();
                $val_stock = $tab_sap_inventoryfg->stock;

                delsched_stock::insert([
                    'item_code'   => $item->item_code,
                    'quantity'    => $val_stock,
                    'total_after' => $val_stock,
                ]);
            }

            $tab_delsched_final = DB::table('delsched_final')->orderBy('id', 'asc')->get();

            foreach ($tab_delsched_final as $delsched_final) {
                $today         = Carbon::today();
                $final_id      = $delsched_final->id;
                $dept          = $delsched_final->departement;
                $item_code     = $delsched_final->item_code;
                $delivery_date = Carbon::parse($delsched_final->delivery_date);
                $outstanding   = $delsched_final->outstanding;
                $deliver_qty   = $delsched_final->delivery_qty;
                $delivered     = $delsched_final->delivered;

                $stock = DB::table('delsched_stock')->where('item_code', $item_code)->first();
                if (!$stock) continue;

                $stock_id    = $stock->id;
                $stock_qty   = $stock->quantity;
                $total_after = $stock->total_after;

                if ($deliver_qty == $delivered) {
                    DB::table('delsched_final')->where('id', $final_id)->update([
                        'stock'   => $stock_qty,
                        'balance' => $total_after,
                        'status'  => 'SUCCESS',
                        'remark'  => 'FINISHED',
                    ]);
                    continue;
                }

                if ($total_after >= $outstanding) {
                    $cal_outstanding_st  = 0;
                    $cal_total_after_now = $total_after - $outstanding;
                    $stock_ready         = true;
                } else {
                    $cal_outstanding_st  = $outstanding - $total_after;
                    $cal_total_after_now = $total_after - $outstanding;
                    $stock_ready         = false;
                }

                $day_limit = ($dept == 390) ? 2 : 3;
                $limit_date = $today->copy()->addDays($day_limit);

                if ($delivery_date->lte($limit_date)) {
                    $rcd_status = $stock_ready ? 'WARNING' : 'DANGER';
                    $rcd_remark = $stock_ready ? 'PAST DUE DATE - STOCK READY' : 'PAST DUE DATE - NO STOCK';
                } else {
                    $rcd_status = $stock_ready ? 'MUTED' : 'INFO';
                    $rcd_remark = $stock_ready ? 'OPEN - STOCK READY' : 'OPEN - NO STOCK';
                }

                DB::table('delsched_stock')->where('id', $stock_id)->update(['total_after' => $cal_total_after_now]);
                DB::table('delsched_final')->where('id', $final_id)->update([
                    'stock'           => $stock_qty,
                    'balance'         => $cal_total_after_now,
                    'outstanding_stk' => $cal_outstanding_st,
                    'status'          => $rcd_status,
                    'remark'          => $rcd_remark,
                ]);
            }

            $now = new DateTime();
            $now->modify('+420 minutes');
            DB::table('uti_date_list')->where('id', '13')->update(['updated_at' => $now]);

            // Done
            $this->updateLog('delsched_main', 'step4', 'done', 'Semua step selesai!');

        } catch (\Exception $e) {
            $this->updateLog('delsched_main', 'step4', 'failed', 'Step 4 gagal: ' . $e->getMessage());
            Log::error('ProcessDelschedStep4 failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateLog($key, $step, $status, $message)
    {
        DB::table('delsched_process_logs')->updateOrInsert(
            ['process_key' => $key],
            ['current_step' => $step, 'status' => $status, 'message' => $message, 'updated_at' => now()]
        );
    }
}