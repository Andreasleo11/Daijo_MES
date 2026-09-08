<?php

namespace Tests\Feature;

use App\Models\CustomBarcodeLog;
use App\Models\MasterListItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomBarcodeLabelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);

        $this->createTestSchema();
    }

    private function createTestSchema(): void
    {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_list_items', function ($table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name')->nullable();
            $table->string('description_in_foreign_lang')->nullable();
            $table->string('family')->nullable();
            $table->string('color')->nullable();
            $table->string('position')->nullable();
            $table->string('half_code_1')->nullable();
            $table->string('half_code_2')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_barcode_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->string('spk_number');
            $table->integer('quantity');
            $table->string('warehouse')->default('WFI');
            $table->string('shift', 10);
            $table->integer('start_label');
            $table->integer('end_label');
            $table->integer('total_labels');
            $table->date('prod_date')->nullable();
            $table->string('operator')->nullable();
            $table->string('customer')->nullable();
            $table->string('barcode_type')->nullable();
            $table->boolean('is_trial')->default(false);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function test_custom_barcode_print_with_empty_date()
    {
        $role = Role::create(['name' => 'ADMIN']);
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'role_id'  => $role->id,
            'password' => bcrypt('password'),
        ]);

        MasterListItem::create([
            'item_code' => 'ITEM-001',
            'item_name' => 'SAMPLE ITEM',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('barcode.custom.print'), [
            'item_code'    => 'ITEM-001',
            'spk_number'   => 'SPK-9999',
            'quantity'     => 100,
            'warehouse'    => 'WFI',
            'start_label'  => 1,
            'end_label'    => 2,
            'shift'        => 'I',
            'prod_date'    => '',
            'barcode_type' => 'default',
        ]);

        $response->assertOk();
        $response->assertViewHas('labels', function ($labels) {
            return count($labels) === 2
                && $labels[0]['prod_date'] === null
                && $labels[0]['prod_date_formatted'] === '';
        });

        // Ensure logged correctly with null prod_date
        $log = CustomBarcodeLog::first();
        $this->assertNotNull($log);
        $this->assertNull($log->prod_date);
    }

    public function test_custom_barcode_print_with_filled_date()
    {
        $user = User::create([
            'name'     => 'Test User 2',
            'email'    => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        MasterListItem::create([
            'item_code' => 'ITEM-SHARP',
            'item_name' => 'SHARP ITEM',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('barcode.custom.print'), [
            'item_code'    => 'ITEM-SHARP',
            'spk_number'   => 'SPK-SHARP-01',
            'quantity'     => 50,
            'warehouse'    => 'FFI',
            'start_label'  => 1,
            'end_label'    => 1,
            'shift'        => 'II',
            'prod_date'    => '2026-09-07',
            'barcode_type' => 'sharp',
        ]);

        $response->assertOk();
        $response->assertViewHas('labels', function ($labels) {
            return $labels[0]['prod_date'] === '2026-09-07'
                && $labels[0]['prod_date_formatted'] === 'SEPTEMBER 2026'
                && $labels[0]['year_code'] === 'BF'
                && (string)$labels[0]['month_code'] === '9';
        });
    }
}
