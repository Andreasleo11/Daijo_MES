<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\WmsWarehouse;
use App\Models\WmsRack;
use App\Models\WmsPosition;
use App\Models\WmsPalletForm;
use App\Livewire\Wms\RackMapping;
use Livewire\Livewire;

class WmsRackManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->warehouse = WmsWarehouse::create([
            'whse_code' => 'J06',
            'whse_name' => 'Monitoring Hunian Rak Gudang J06 (Highly Marelli)',
        ]);
    }

    public function test_rack_and_warehouse_management()
    {
        $this->actingAs($this->user);

        // 1. Warehouse update
        Livewire::test(RackMapping::class)
            ->set('whseCode', 'J08')
            ->set('whseName', 'Gudang Finished Goods J08')
            ->call('saveWarehouse')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wms_warehouses', ['whse_code' => 'J08']);

        // 2. Create rack & add slot, remove slot, re-add slot
        $rack = WmsRack::create(['whse_id' => $this->warehouse->id, 'rack_code' => '004']);
        WmsPosition::create(['rack_id' => $rack->id, 'level_no' => 1, 'slot_no' => 1, 'position_code' => 'J06-HM-004-L1S1']);

        Livewire::test(RackMapping::class)
            ->call('addSlotToRack', $rack->id)
            ->call('removeSlotFromRack', $rack->id)
            ->call('addSlotToRack', $rack->id)
            ->call('addLevelToRack', $rack->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wms_positions', ['position_code' => 'J06-HM-004-L1S2']);
        $this->assertDatabaseHas('wms_positions', ['position_code' => 'J06-HM-004-L2S1']);
    }
}
