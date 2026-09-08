<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Models\ScannedData;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class WmsPalletLookupAndSoScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_so_scan_does_not_delete_pallet_detail_with_different_part_no()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Pallet 1 for item '275PVM650A' with label '301'
        $palletA = WmsPalletForm::create([
            'pallet_id' => 'PLT-TEST-001',
            'part_no' => '275PVM650A',
            'model_name' => 'CASE CTR 5H45',
            'prod_date' => '2026-09-08',
            'delivery_shift' => 1,
            'box_qty' => 1,
            'total_pallet_qty' => 12,
            'status' => 'STORED',
        ]);

        $detailA = WmsPalletFormDetail::create([
            'pallet_form_id' => 'PLT-TEST-001',
            'part_no' => '275PVM650A',
            'model_name' => 'CASE CTR 5H45',
            'spk_no' => '26023496',
            'qty' => 12,
            'warehouse' => 'FG',
            'label' => '301',
        ]);

        // Pallet 2 for item '8002C301.' with label '301'
        $palletB = WmsPalletForm::create([
            'pallet_id' => 'PLT-TEST-002',
            'part_no' => '8002C301.',
            'model_name' => 'OTHER PART',
            'prod_date' => '2026-09-08',
            'delivery_shift' => 1,
            'box_qty' => 1,
            'total_pallet_qty' => 30,
            'status' => 'STORED',
        ]);

        $detailB = WmsPalletFormDetail::create([
            'pallet_form_id' => 'PLT-TEST-002',
            'part_no' => '8002C301.',
            'model_name' => 'OTHER PART',
            'spk_no' => '26023840',
            'qty' => 30,
            'warehouse' => 'FG',
            'label' => '301',
        ]);

        \App\Models\SpkItemHistory::create([
            'spk_number' => '26023840',
            'item_code' => '8002C301.',
        ]);

        \App\Models\SoData::create([
            'doc_num' => 'SO-1001',
            'customer' => 'TEST CUSTOMER',
            'item_code' => '8002C301.',
            'item_name' => 'OTHER PART',
            'quantity' => 100,
            'sales_uom' => 'PCS',
            'sales_pack' => 'BOX',
            'packaging_quantity' => 10,
            'posting_date' => '2026-09-08',
            'create_date' => '2026-09-08',
            'update_date' => '2026-09-08',
            'update_fulltime' => 20260908120000,
        ]);

        // Now SO scans part '8002C301.' with label '301'
        $response = $this->postJson(route('so.scanBarcode'), [
            'so_number' => 'SO-1001',
            'spk_code' => '26023840',
            'quantity' => 30,
            'warehouse' => 'FG',
            'label' => '301',
        ]);

        // Pallet A's detail ('275PVM650A') MUST NOT be deleted (not soft deleted)
        $this->assertNull($detailA->fresh()->deleted_at);
        $this->assertEquals(1, $palletA->fresh()->box_qty);
        $this->assertEquals('STORED', $palletA->fresh()->status);

        // Pallet B's detail ('8002C301.') MUST be soft deleted
        $this->assertNotNull($detailB->fresh()->deleted_at);
        $this->assertEquals(0, $palletB->fresh()->box_qty);
        $this->assertEquals('OUT', $palletB->fresh()->status);
    }

    public function test_artisan_command_restores_falsely_deleted_pallet_details()
    {
        $pallet = WmsPalletForm::create([
            'pallet_id' => 'PLT-RESTORE-001',
            'part_no' => '275PVM650A',
            'model_name' => 'CASE CTR 5H45',
            'prod_date' => '2026-09-08',
            'delivery_shift' => 1,
            'box_qty' => 0,
            'total_pallet_qty' => 0,
            'status' => 'OUT',
        ]);

        $detail = WmsPalletFormDetail::create([
            'pallet_form_id' => 'PLT-RESTORE-001',
            'part_no' => '275PVM650A',
            'model_name' => 'CASE CTR 5H45',
            'spk_no' => '26023496',
            'qty' => 12,
            'warehouse' => 'FG',
            'label' => '301',
        ]);

        // Manually soft delete it (simulating the previous bug)
        $detail->delete();

        $this->assertNotNull($detail->fresh()->deleted_at);

        // Run the repair artisan command
        Artisan::call('wms:fix-pallet-details');

        // It should be restored because no scanned_data exists for '275PVM650A' label '301'
        $this->assertNull($detail->fresh()->deleted_at);
        $this->assertEquals(1, $pallet->fresh()->box_qty);
        $this->assertEquals(12, $pallet->fresh()->total_pallet_qty);
        $this->assertEquals('STORED', $pallet->fresh()->status);
    }
}
