<?php

namespace Tests\Feature\Services\Inventory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Inventory\StockHealthService;
use App\Services\Inventory\DTOs\StockHealthFilterDTO;
use App\Services\Inventory\StockStatus;
use App\Models\Delivery\sapInventoryFg;
use App\Models\Delivery\SapReject;

class StockHealthServiceTest extends TestCase
{
    use DatabaseTransactions;

    private StockHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockHealthService();
        
        sapInventoryFg::insert([
            'item_code' => 'ITEM001',
            'item_name' => 'Widget A',
            'process_owner' => 'Dept A',
            'family' => 'Fam A',
            'stock' => 100,
            'safety_stock' => 50,
        ]);
        
        // At risk
        sapInventoryFg::insert([
            'item_code' => 'ITEM002',
            'item_name' => 'Widget B',
            'process_owner' => 'Dept B',
            'family' => 'Fam B',
            'stock' => 55,
            'safety_stock' => 50,
        ]);

        // Critical
        sapInventoryFg::insert([
            'item_code' => 'ITEM003',
            'item_name' => 'Widget C',
            'process_owner' => 'Dept A',
            'family' => 'Fam C',
            'stock' => 30,
            'safety_stock' => 50,
        ]);

        SapReject::insert([
            'item_no' => 'ITEM001',
            'in_stock' => 5,
            'item_description' => 'Widget A',
            'item_group' => 'Group A',
        ]);
    }

    public function test_get_dashboard_data_identifies_statuses_and_rejects()
    {
        $filter = new StockHealthFilterDTO();
        $data = $this->service->getDashboardData($filter);

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('processOwners', $data);
        $this->assertArrayHasKey('families', $data);

        // Find ITEM001 to assert healthy and reject populated
        $item1 = collect($data['items'])->firstWhere('itemCode', 'ITEM001');
        $this->assertEquals(100, $item1->stock);
        $this->assertEquals(5, $item1->rejectStock);
        $this->assertEquals(StockStatus::Healthy, $item1->status);

        // Find ITEM002 to assert at risk
        $item2 = collect($data['items'])->firstWhere('itemCode', 'ITEM002');
        $this->assertEquals(StockStatus::AtRisk, $item2->status);

        // Find ITEM003 to assert critical
        $item3 = collect($data['items'])->firstWhere('itemCode', 'ITEM003');
        $this->assertEquals(StockStatus::Critical, $item3->status);
        
        // Assert summaries (3 items exist in our mock set exactly from Dept A/B)
        // Since DB may have real values, we should check minimum thresholds or clean it out during transactions
        // Because of the shared database, we check against our inserted mocked ones specifically
        $this->assertGreaterThanOrEqual(1, $data['summary']['healthy']);
        $this->assertGreaterThanOrEqual(1, $data['summary']['at_risk']);
        $this->assertGreaterThanOrEqual(1, $data['summary']['critical']);
    }

    public function test_get_dashboard_data_filters_by_process_owner()
    {
        $filter = new StockHealthFilterDTO('', 'Dept A', '');
        $data = $this->service->getDashboardData($filter);

        $itemCodes = collect($data['items'])->pluck('itemCode')->toArray();
        $this->assertContains('ITEM001', $itemCodes);
        $this->assertContains('ITEM003', $itemCodes);
        $this->assertNotContains('ITEM002', $itemCodes);
    }
}
