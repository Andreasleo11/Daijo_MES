<?php

namespace Tests\Feature\Services\Production;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Production\DailyItemCodeService;
use App\Models\MasterListItem;
use App\Models\SpkMaster;

class DailyItemCodeServiceTest extends TestCase
{
    use DatabaseTransactions; // Rolls back all database insertions made during test

    private DailyItemCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DailyItemCodeService();
        
        // Mocking prerequisites in database
        MasterListItem::insert([
            'item_code' => 'TEST-123',
            'item_name' => 'Testing Name',
            'standart_packaging_list' => 10,
            'pair' => 0,
            'tipe_mesin' => 'Injection',
        ]);

        SpkMaster::insert([
            'spk_number' => 'SPK-123',
            'item_code' => 'TEST-123',
            'planned_quantity' => 100,
            'completed_quantity' => 20,
            'post_date' => '2023-10-01',
            'due_date' => '2023-10-10',
            'production_status' => 'Open',
            'warehouse' => 'WH-A',
        ]);
    }

    public function test_calculate_item_stats_calculates_loss_package_correctly()
    {
        // 100 planned - 20 completed = 80 max
        $stats = $this->service->calculateItemStats('TEST-123', 25);
        
        $this->assertEquals(100, $stats['total_planned_quantity']);
        $this->assertEquals(20, $stats['total_completed_quantity']);
        $this->assertEquals(80, $stats['max_quantity']);
        $this->assertEquals(5, $stats['loss_package_quantity']); // 25 % 10 = 5
    }

    public function test_calculate_item_stats_prevents_modulo_by_zero()
    {
        MasterListItem::insert([
            'item_code' => 'TEST-ZERO-PKG',
            'item_name' => 'Test Name 0 PKG',
            'standart_packaging_list' => 0,
            'pair' => 0,
            'tipe_mesin' => 'Injection',
        ]);

        SpkMaster::insert([
            'spk_number' => 'SPK-ZERO-PKG',
            'item_code' => 'TEST-ZERO-PKG',
            'planned_quantity' => 100,
            'completed_quantity' => 0,
            'post_date' => '2023-10-01',
            'due_date' => '2023-10-10',
            'production_status' => 'Open',
            'warehouse' => 'WH-A',
        ]);

        $stats = $this->service->calculateItemStats('TEST-ZERO-PKG', 25);
        
        $this->assertEquals(0, $stats['loss_package_quantity']); // Modulo by 0 prevented
    }

    public function test_assign_item_codes_throws_exception_if_quantity_exceeds_spk_limit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Quantity of TEST-123 exceeds SPK with a maximum of 80.");

        $validatedData = [
            'shifts' => [1],
            'item_codes' => [1 => ['TEST-123']],
            'quantities' => [1 => [81]], // Exceeds limit (100 - 20)
            'start_dates' => [1 => ['2023-10-01']],
            'end_dates' => [1 => ['2023-10-01']],
            'start_times' => [1 => ['08:00:00']],
            'end_times' => [1 => ['16:00:00']],
            'remarks' => [1 => ['']],
            'schedule_date' => '2023-10-01',
            'machine_id' => 1,
        ];

        $this->service->assignItemCodes($validatedData);
    }

    public function test_assign_item_codes_inserts_records_correctly()
    {
        $validatedData = [
            'shifts' => [1],
            'item_codes' => [1 => ['TEST-123']],
            'quantities' => [1 => [25]], // Under 80 limit
            'start_dates' => [1 => ['2023-10-01']],
            'end_dates' => [1 => ['2023-10-01']],
            'start_times' => [1 => ['08:00:00']],
            'end_times' => [1 => ['16:00:00']],
            'remarks' => [1 => ['']],
            'schedule_date' => '2023-10-01',
            'machine_id' => 1,
        ];

        $this->service->assignItemCodes($validatedData);

        $this->assertDatabaseHas('daily_item_codes', [
            'user_id' => 1,
            'item_code' => 'TEST-123',
            'quantity' => 25,
            'loss_package_quantity' => 5, // 25 % 10
            'final_quantity' => 25,
            'actual_quantity' => 25,
        ]);
    }
}
