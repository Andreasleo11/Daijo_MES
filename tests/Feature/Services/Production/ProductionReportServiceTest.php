<?php

namespace Tests\Feature\Services\Production;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Production\ProductionReportService;
use App\Models\DailyItemCode;
use App\Models\MasterListItem;
use App\Models\Delivery\sapInventoryFg;
use App\Models\HourlyRemark;
use App\Models\ProductionNgDetail;
use App\Models\ProductionNgType;
use App\Models\User;

class ProductionReportServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ProductionReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductionReportService();

        // 1. Create a user (machine)
        $machine = new User();
        $machine->name = 'Machine A';
        $machine->email = 'machine_a_' . uniqid() . '@test.com';
        $machine->password = bcrypt('password');
        $machine->role_id = 1;
        $machine->save();

        // 2. Create Master records
        MasterListItem::insert([
            'item_code' => 'RPT-123',
            'item_name' => 'Report Widget',
            'standart_packaging_list' => 10,
            'cavity' => 4,
            'pair' => 0,
            'tipe_mesin' => 'Injection'
        ]);

        sapInventoryFg::insert([
            'item_code' => 'RPT-123',
            'cycle_time' => 0.5 // 0.5 * 60 = 30 seconds
        ]);

        // 3. Create DailyItemCode
        $daily = new DailyItemCode();
        $daily->user_id = $machine->id;
        $daily->item_code = 'RPT-123';
        $daily->schedule_date = '2023-10-01';
        $daily->start_date = '2023-10-01';
        $daily->end_date = '2023-10-01';
        $daily->start_time = '08:00';
        $daily->end_time = '16:00';
        $daily->shift = 1;
        $daily->quantity = 100;
        $daily->loss_package_quantity = 0;
        $daily->final_quantity = 100;
        $daily->actual_quantity = 100;
        $daily->temporal_cycle_time = 25;
        $daily->save();

        // 4. Create Hourly Remarks
        $remark1 = new HourlyRemark();
        $remark1->dic_id = $daily->id;
        $remark1->actual_production = 50;
        $remark1->NG = 5;
        $remark1->remark = 'Good run';
        $remark1->start_time = '08:00';
        $remark1->end_time = '09:00';
        $remark1->target = 100;
        $remark1->pic = 'John Doe';
        $remark1->save();
        
        $remark2 = new HourlyRemark();
        $remark2->dic_id = $daily->id;
        $remark2->actual_production = 40;
        $remark2->NG = 5;
        $remark2->remark = 'Minor stoppage';
        $remark2->start_time = '09:00';
        $remark2->end_time = '10:00';
        $remark2->target = 100;
        $remark2->pic = 'John Doe';
        $remark2->save();

        // 5. Create NG Types & Details
        $ngType = new ProductionNgType();
        $ngType->ng_type = 'Scratch';
        $ngType->save();

        $ngDetail1 = new ProductionNgDetail();
        $ngDetail1->hourly_remark_id = $remark1->id;
        $ngDetail1->ng_type_id = $ngType->id;
        $ngDetail1->ng_quantity = 5;
        $ngDetail1->save();

        $ngDetail2 = new ProductionNgDetail();
        $ngDetail2->hourly_remark_id = $remark2->id;
        $ngDetail2->ng_type_id = $ngType->id;
        $ngDetail2->ng_quantity = 5;
        $ngDetail2->save();
    }

    public function test_get_daily_report_data_aggregates_correctly()
    {
        $result = $this->service->getDailyReportData('2023-10-01');

        $this->assertIsArray($result);

        // Make sure we grab the one we mocked
        $itemSummary = collect($result)->firstWhere('item_code', 'RPT-123');
        
        $this->assertNotNull($itemSummary);
        $this->assertEquals('Report Widget', $itemSummary['item_name']);
        
        $this->assertEquals(25, $itemSummary['cycletime']); // from temporal_cycle_time
        $this->assertEquals(30, $itemSummary['sap_cycletime']); // 0.5 * 60
        
        $this->assertEquals(90, $itemSummary['total_actual']); // 50 + 40
        $this->assertEquals(10, $itemSummary['total_ng']); // 5 + 5
        
        // reject rate = (10 / (90 + 10)) * 100 = 10%
        $this->assertEquals(10.0, $itemSummary['reject_rate']);
        
        // Verify Shift 1 Details
        $shift1 = $itemSummary['shifts'][1];
        $this->assertEquals('Machine A', $shift1['user']);
        $this->assertEquals(90, $shift1['total_actual']);
        $this->assertEquals(10, $shift1['total_ng']);
        $this->assertEquals(10, $shift1['ng_details']['Scratch']);
    }
}
