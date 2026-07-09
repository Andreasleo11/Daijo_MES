<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\MasterListItem;
use App\Models\SpkItemHistory;
use App\Models\WmsWarehouse;
use App\Models\WmsRack;
use App\Models\WmsPosition;
use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Services\WmsSapSyncService;
use App\Services\WmsService;
use Livewire\Livewire;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WmsPalletFormCreatorDeliveryTest extends TestCase
{
    protected Role $adminRole;
    protected User $adminUser;
    protected WmsPosition $position;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent Vite from trying to find compiled manifests during testing
        $this->withoutVite();

        // 1. Force use SQLite In-Memory Database for this test to avoid local MySQL dependency
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);

        // 2. Manually build the minimum schema required for this test (Avoid running full migrations with MySQL-specific syntax)
        $this->createTestSchema();

        // 3. Setup role & user
        $this->adminRole = Role::create(['name' => 'ADMIN']);
        
        $this->adminUser = new User([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->role_id = $this->adminRole->id;
        $this->adminUser->save();

        // 4. Setup master items
        MasterListItem::create([
            'item_code' => 'PART-XYZ',
            'item_name' => 'Test Part',
            'customer_code' => 'CUST-A',
        ]);

        SpkItemHistory::create([
            'spk_number' => 'SPK-1234',
            'item_code' => 'PART-XYZ',
        ]);

        // 5. Setup WMS rack positions
        $whse = WmsWarehouse::create([
            'whse_code' => 'J06',
            'whse_name' => 'Gudang J06',
        ]);

        $rack = WmsRack::create([
            'whse_id' => $whse->id,
            'rack_code' => 'R1',
        ]);

        $this->position = WmsPosition::create([
            'rack_id' => $rack->id,
            'level_no' => 2,
            'slot_no' => 1,
            'position_code' => 'R1-02-01',
            'customer_code' => 'CUST-A',
            'status' => 'EMPTY',
            'max_capacity' => 10,
        ]);
    }

    private function createTestSchema(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable(); // Allow null temporarily during creation
            $table->timestamps();
        });

        Schema::create('master_list_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('customer_code')->nullable();
            $table->timestamps();
        });

        Schema::create('spk_item_histories', function (Blueprint $table) {
            $table->id();
            $table->string('spk_number');
            $table->string('item_code');
            $table->timestamps();
        });

        Schema::create('wms_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('whse_code');
            $table->string('whse_name');
            $table->timestamps();
        });

        Schema::create('wms_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whse_id');
            $table->string('rack_code');
            $table->timestamps();
        });

        Schema::create('wms_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id');
            $table->integer('level_no');
            $table->integer('slot_no');
            $table->string('customer_code')->nullable();
            $table->string('position_code')->unique();
            $table->string('status')->default('EMPTY');
            $table->string('last_item_code')->nullable();
            $table->integer('max_capacity')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wms_pallet_forms', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_id')->unique();
            $table->foreignId('position_id');
            $table->string('part_no')->nullable();
            $table->string('model_name')->nullable();
            $table->date('prod_date');
            $table->string('lot_no')->nullable();
            $table->string('delivery_name')->nullable();
            $table->string('delivery_shift')->nullable();
            $table->integer('box_qty')->default(0);
            $table->double('total_pallet_qty', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->integer('sap_sync_status')->default(0);
            $table->text('sap_error_msg')->nullable();
            $table->timestamp('sap_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wms_pallet_form_details', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_form_id');
            $table->string('part_no')->nullable();
            $table->string('model_name')->nullable();
            $table->string('spk_no')->nullable();
            $table->double('qty', 15, 2)->default(0.00);
            $table->string('warehouse')->nullable();
            $table->string('label')->nullable();
            $table->boolean('is_no_label')->default(false);
            $table->string('no_label_reason')->nullable();
            $table->integer('sap_sync_status')->default(0);
            $table->text('sap_error_msg')->nullable();
            $table->timestamp('sap_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name')->nullable();
            $table->string('method')->nullable();
            $table->text('endpoint')->nullable();
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->string('status')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('wms_pallet_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_id');
            $table->string('transaction_type');
            $table->foreignId('position_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Test normal mode (wms.pallet-form.create):
     * Scanned warehouse (FFI) is saved as-is.
     */
    public function test_normal_pallet_creator_saves_warehouse_as_scanned(): void
    {
        // Fake Job Dispatch
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('wms.pallet-form.create'));

        $response->assertOk();

        $wmsService = app(WmsService::class);

        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Wms\PalletFormCreator::class)
            ->set('prod_date', '2026-07-09')
            ->set('delivery_name', 'Operator A')
            ->set('delivery_shift', '1')
            ->call('addItem', 'LBL-101', 'SPK-1234', 50, 'FFI')
            ->call('generateForm', $wmsService)
            ->assertHasNoErrors();

        // Assert database values
        $pallet = WmsPalletForm::first();
        $this->assertNotNull($pallet);

        $detail = WmsPalletFormDetail::where('pallet_form_id', $pallet->pallet_id)->first();
        $this->assertNotNull($detail);
        // Verify warehouse is saved as FFI (scanned value)
        $this->assertEquals('FFI', $detail->warehouse);
    }

    /**
     * Test delivery mode (wms.pallet-form.create-delivery):
     * Scanned warehouse (FFI) is overwritten to FG in database.
     */
    public function test_delivery_pallet_creator_overwrites_warehouse_to_fg(): void
    {
        // Fake Job Dispatch
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('wms.pallet-form.create-delivery'));

        $response->assertOk();

        $wmsService = app(WmsService::class);

        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Wms\PalletFormCreator::class, ['isDelivery' => true])
            ->set('prod_date', '2026-07-09')
            ->set('delivery_name', 'Operator B')
            ->set('delivery_shift', '2')
            ->call('addItem', 'LBL-202', 'SPK-1234', 100, 'FFI')
            ->call('generateForm', $wmsService)
            ->assertHasNoErrors();

        // Assert database values
        $pallet = WmsPalletForm::first();
        $this->assertNotNull($pallet);

        $detail = WmsPalletFormDetail::where('pallet_form_id', $pallet->pallet_id)->first();
        $this->assertNotNull($detail);
        // Verify warehouse is overwritten to FG in database
        $this->assertEquals('FG', $detail->warehouse);
    }

    /**
     * Test SAP Sync payloads for both modes with Http faking.
     */
    public function test_sap_sync_payload_sends_saved_warehouse_correctly(): void
    {
        // Fake SAP API calls
        Http::fake([
            '*/auth/token' => Http::response(['access_token' => 'mock_token'], 200),
            '*/api/receipt_production/create' => Http::response(['status' => true, 'message' => 'Success'], 200),
        ]);

        // 1. Create a Normal Pallet Form Detail (warehouse = FFI)
        $normalPallet = WmsPalletForm::create([
            'pallet_id' => 'PLT-NORMAL-001',
            'position_id' => $this->position->id,
            'part_no' => 'PART-XYZ',
            'model_name' => 'Test Part',
            'prod_date' => '2026-07-09',
            'delivery_name' => 'Operator A',
            'delivery_shift' => '1',
            'box_qty' => 1,
            'total_pallet_qty' => 50,
            'sap_sync_status' => 0,
        ]);

        WmsPalletFormDetail::create([
            'pallet_form_id' => 'PLT-NORMAL-001',
            'part_no' => 'PART-XYZ',
            'model_name' => 'Test Part',
            'spk_no' => 'SPK-1234',
            'qty' => 50,
            'warehouse' => 'FFI',
            'label' => 'LBL-101',
            'sap_sync_status' => 0,
        ]);

        // 2. Create a Delivery Pallet Form Detail (warehouse = FG)
        $deliveryPallet = WmsPalletForm::create([
            'pallet_id' => 'PLT-DELIV-002',
            'position_id' => $this->position->id,
            'part_no' => 'PART-XYZ',
            'model_name' => 'Test Part',
            'prod_date' => '2026-07-09',
            'delivery_name' => 'Operator B',
            'delivery_shift' => '2',
            'box_qty' => 1,
            'total_pallet_qty' => 100,
            'sap_sync_status' => 0,
        ]);

        WmsPalletFormDetail::create([
            'pallet_form_id' => 'PLT-DELIV-002',
            'part_no' => 'PART-XYZ',
            'model_name' => 'Test Part',
            'spk_no' => 'SPK-1234',
            'qty' => 100,
            'warehouse' => 'FG', // saved as FG
            'label' => 'LBL-202',
            'sap_sync_status' => 0,
        ]);

        $sapSyncService = app(WmsSapSyncService::class);

        // Run sync for Normal pallet
        $sapSyncService->syncPallet('PLT-NORMAL-001');

        // Run sync for Delivery pallet
        $sapSyncService->syncPallet('PLT-DELIV-002');

        // Assert HTTP requests
        Http::assertSent(function ($request) {
            // Check Normal payload
            if ($request->url() === 'http://192.168.6.149:9001/api/receipt_production/create') {
                $payload = $request->data();
                if ($payload[0]['quantity'] == 50) {
                    return $payload[0]['warehouse'] === 'FFI';
                }
            }
            return true;
        });

        Http::assertSent(function ($request) {
            // Check Delivery payload
            if ($request->url() === 'http://192.168.6.149:9001/api/receipt_production/create') {
                $payload = $request->data();
                if ($payload[0]['quantity'] == 100) {
                    return $payload[0]['warehouse'] === 'FG';
                }
            }
            return true;
        });
    }
}
