<?php

namespace Tests\Feature;

use App\Livewire\MaterialWarehouse\MaterialIncomingCreator;
use App\Livewire\MaterialWarehouse\MaterialOutgoingCreator;
use App\Livewire\MaterialWarehouse\MaterialPalletIndex;
use App\Livewire\MaterialWarehouse\MaterialQrLookup;
use App\Models\MasterListMaterial;
use App\Models\MwhPallet;
use App\Models\MwhPosition;
use App\Models\MwhRack;
use App\Models\MwhWarehouse;
use App\Models\User;
use App\Services\MaterialWarehouseService;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialWarehouseTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSchema();
    }

    private function createTestSchema(): void
    {
        Schema::dropIfExists('mwh_outgoings');
        Schema::dropIfExists('mwh_pallets');
        Schema::dropIfExists('mwh_incoming_headers');

        if (!Schema::hasTable('mwh_warehouses')) {
            Schema::create('mwh_warehouses', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('whse_code')->unique();
                $table->string('whse_name');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('mwh_racks')) {
            Schema::create('mwh_racks', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('whse_id');
                $table->string('rack_code');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('mwh_positions')) {
            Schema::create('mwh_positions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('rack_id');
                $table->integer('level_no');
                $table->integer('slot_no');
                $table->string('position_code')->unique();
                $table->string('slot_label')->nullable();
                $table->string('status')->default('EMPTY');
                $table->string('last_item_code')->nullable();
                $table->integer('max_capacity')->default(1000);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('master_list_materials')) {
            Schema::create('master_list_materials', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('item_code', 100)->unique();
                $table->text('item_description')->nullable();
                $table->string('preferred_supplier', 100)->nullable();
                $table->string('purchasing_uom', 50)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('mwh_incoming_headers')) {
            Schema::create('mwh_incoming_headers', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('document_no')->unique();
                $table->string('supplier_name')->nullable();
                $table->string('po_number')->nullable();
                $table->date('arrival_date');
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('mwh_pallets')) {
            Schema::create('mwh_pallets', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('pallet_id')->unique();
                $table->foreignId('incoming_header_id')->nullable();
                $table->string('item_code');
                $table->string('lot_no')->nullable();
                $table->decimal('initial_qty', 12, 2)->default(0);
                $table->decimal('current_qty', 12, 2)->default(0);
                $table->string('uom', 20)->default('KG');
                $table->foreignId('position_id')->nullable();
                $table->enum('status', ['STORED', 'PARTIAL', 'EMPTY'])->default('STORED');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('mwh_outgoings')) {
            Schema::create('mwh_outgoings', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('outgoing_code')->unique();
                $table->string('pallet_id');
                $table->foreignId('position_id')->nullable();
                $table->string('item_code');
                $table->decimal('qty_taken', 12, 2)->default(0);
                $table->string('uom', 20)->default('KG');
                $table->date('outgoing_date');
                $table->string('issued_to')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->foreignId('role_id')->nullable();
                $table->string('password')->default('password');
                $table->timestamps();
            });
        }
    }

    public function test_incoming_splits_qty_over_1000kg_into_multiple_pallets()
    {
        $wh = MwhWarehouse::firstOrCreate(['whse_code' => 'MTR-01'], ['whse_name' => 'Gudang Material']);
        $rack = MwhRack::firstOrCreate(['whse_id' => $wh->id, 'rack_code' => 'RAK-A1']);
        $pos = MwhPosition::firstOrCreate([
            'rack_id'       => $rack->id,
            'level_no'      => 1,
            'slot_no'       => 1,
            'position_code' => 'RAK-A1-L01-S01',
        ], ['max_capacity' => 2000]);

        $mat = MasterListMaterial::firstOrCreate([
            'item_code' => '180-CN890-FL',
        ], [
            'item_description' => 'SPK CORD 22AWG 3M TR TUBE W-B',
            'purchasing_uom'   => 'PCS',
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_' . uniqid() . '@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(MaterialIncomingCreator::class)
            ->set('supplier_name', 'PT Material Supplier')
            ->set('po_number', 'PO-998811')
            ->set('arrival_date', '2026-07-22')
            ->set('items.0.item_code', '180-CN890-FL')
            ->set('items.0.qty', '2500') // 2500 KG -> should split into 1000, 1000, 500
            ->set('items.0.position_id', $pos->id)
            ->call('saveIncoming')
            ->assertHasNoErrors();

        // 3 pallets should be created
        $this->assertEquals(3, MwhPallet::where('item_code', '180-CN890-FL')->count());

        $pallets = MwhPallet::where('item_code', '180-CN890-FL')->orderBy('initial_qty', 'desc')->get();
        $this->assertEquals(1000.00, $pallets[0]->initial_qty);
        $this->assertEquals(1000.00, $pallets[1]->initial_qty);
        $this->assertEquals(500.00, $pallets[2]->initial_qty);
    }

    public function test_partial_outgoing_picking_reduces_pallet_qty_and_updates_status()
    {
        $mwhService = app(MaterialWarehouseService::class);

        $wh = MwhWarehouse::firstOrCreate(['whse_code' => 'MTR-01'], ['whse_name' => 'Gudang Material']);
        $rack = MwhRack::firstOrCreate(['whse_id' => $wh->id, 'rack_code' => 'RAK-A1']);
        $pos = MwhPosition::firstOrCreate([
            'rack_id'       => $rack->id,
            'level_no'      => 1,
            'slot_no'       => 2,
            'position_code' => 'RAK-A1-L01-S02',
        ], ['max_capacity' => 1000]);

        $pallet = MwhPallet::create([
            'pallet_id'   => 'MPLT-TEST-001',
            'item_code'   => '180-CN890-FL',
            'initial_qty' => 800.00,
            'current_qty' => 800.00,
            'uom'         => 'KG',
            'position_id' => $pos->id,
            'status'      => 'STORED',
        ]);

        $mwhService->updatePositionStatus($pos->id);

        // Pick 300 KG (Partial picking)
        $mwhService->processOutgoingPicking('MPLT-TEST-001', 300.00, '2026-07-22', 'Mesin 01', 'Pemakaian Moulding');

        $pallet->refresh();
        $this->assertEquals(500.00, $pallet->current_qty);
        $this->assertEquals('PARTIAL', $pallet->status);

        $this->assertDatabaseHas('mwh_outgoings', [
            'pallet_id' => 'MPLT-TEST-001',
            'qty_taken' => 300.00,
            'issued_to' => 'Mesin 01',
        ]);
    }

    public function test_can_render_material_warehouse_components()
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_' . uniqid() . '@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(MaterialIncomingCreator::class)->assertStatus(200);
        Livewire::test(MaterialPalletIndex::class)->assertStatus(200);
        Livewire::test(MaterialOutgoingCreator::class)->assertStatus(200);
        Livewire::test(MaterialQrLookup::class)->assertStatus(200);
        Livewire::test(\App\Livewire\MaterialWarehouse\RackMapping::class)->assertStatus(200);
    }

    public function test_rack_mapping_search_highlights_matching_slot_and_shows_detail()
    {
        $wh = MwhWarehouse::firstOrCreate(['whse_code' => 'MTR-01'], ['whse_name' => 'Gudang Material']);
        $rack = MwhRack::firstOrCreate(['whse_id' => $wh->id, 'rack_code' => 'RAK-B1']);
        $pos = MwhPosition::firstOrCreate([
            'rack_id'       => $rack->id,
            'level_no'      => 1,
            'slot_no'       => 1,
            'position_code' => 'RAK-B1-L01-S01',
        ], ['max_capacity' => 1000]);

        $pallet = MwhPallet::create([
            'pallet_id'   => 'MPLT-SEARCH-999',
            'item_code'   => 'SEARCH-ITEM-01',
            'initial_qty' => 750.00,
            'current_qty' => 750.00,
            'position_id' => $pos->id,
            'status'      => 'STORED',
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_' . uniqid() . '@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\MaterialWarehouse\RackMapping::class)
            ->set('searchTerm', 'SEARCH-ITEM-01')
            ->assertViewHas('matchingPositionIds', function ($ids) use ($pos) {
                return in_array($pos->id, $ids);
            })
            ->call('selectPosition', $pos->id)
            ->assertSet('selectedPositionId', $pos->id)
            ->assertSee('MPLT-SEARCH-999')
            ->assertSee('SEARCH-ITEM-01');
    }

    public function test_rack_mapping_can_store_material_directly_into_selected_slot()
    {
        $wh = MwhWarehouse::firstOrCreate(['whse_code' => 'MTR-01'], ['whse_name' => 'Gudang Material']);
        $rack = MwhRack::firstOrCreate(['whse_id' => $wh->id, 'rack_code' => 'RAK-C1']);
        $pos = MwhPosition::firstOrCreate([
            'rack_id'       => $rack->id,
            'level_no'      => 1,
            'slot_no'       => 1,
            'position_code' => 'RAK-C1-L01-S01',
        ], ['max_capacity' => 1000]);

        MasterListMaterial::firstOrCreate([
            'item_code' => 'MAT-DIRECT-01',
        ], [
            'item_description' => 'Direct Test Material',
            'purchasing_uom'   => 'KG',
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_' . uniqid() . '@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\MaterialWarehouse\RackMapping::class)
            ->call('selectPosition', $pos->id)
            ->set('new_item_code', 'MAT-DIRECT-01')
            ->set('new_qty', '650')
            ->set('new_lot_no', 'LOT-DIRECT-99')
            ->call('storeMaterialToSlot')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mwh_pallets', [
            'position_id' => $pos->id,
            'item_code'   => 'MAT-DIRECT-01',
            'current_qty' => 650.00,
            'lot_no'      => 'LOT-DIRECT-99',
        ]);

        $pos->refresh();
        $this->assertEquals('PARTIAL', $pos->status);
    }

    public function test_public_pallet_lookup_accessible_without_authentication()
    {
        $pallet = MwhPallet::create([
            'pallet_id'   => 'MPLT-PUBLIC-777',
            'item_code'   => 'PUBLIC-ITEM-01',
            'initial_qty' => 500.00,
            'current_qty' => 500.00,
            'status'      => 'STORED',
        ]);

        // Unsigned request should be rejected with 403 Forbidden
        $this->get('/public/material-pallet/MPLT-PUBLIC-777')->assertStatus(403);

        // Signed URL request should succeed without login
        $signedUrl = \Illuminate\Support\Facades\URL::signedRoute('mwh.public-pallet-lookup', ['palletId' => 'MPLT-PUBLIC-777']);
        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('MPLT-PUBLIC-777');
        $response->assertSee('PUBLIC-ITEM-01');
        $response->assertSee('500.00');
    }
}
