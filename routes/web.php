<?php

use App\Http\Controllers\AssemblyDailyController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\DailyItemCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\BillOfMaterialController;
use App\Http\Controllers\Production\WorkshopController;
use App\Http\Controllers\Production\ForecastProductionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InitialBarcodeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MasterItemController;
use App\Http\Controllers\DeliveryScheduleController;
use App\Http\Controllers\UpdateDailyController;
use App\Http\Controllers\NotificationRecepientController;
use App\Http\Controllers\SecondDailyController;
use App\Http\Controllers\Store\SOController;
use App\Http\Controllers\WaitingPurchaseOrderController;
use App\Http\Controllers\InvLineListController;
use App\Http\Controllers\Setting\HolidayScheduleController;
use App\Http\Controllers\CapacityByForecastController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OperatorUserController;
use App\Http\Controllers\ProductionDashboardController;
use App\Http\Controllers\MasterListItemController;
use App\Http\Controllers\DaijoMesHomeController;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Services\BaseSapService;
use App\Services\SpkMasterService;

use App\Livewire\LoginSwitcher as LivewireLoginSwitcher;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/{user}', [DashboardController::class, 'autoLogin']);\

Route::get('test/spk/1', [SpkMasterService::class, 'getAll']);

Route::post('/admin/dailyitemcodes/{id}/set-status', [ProductionDashboardController::class, 'setStatus'])->name('dailyitemcodes.set-status');
Route::delete('/hourly-remarks/{id}', [ProductionDashboardController::class, 'destroyHourlyRemark'])->name('hourly-remarks.destroy');

Route::get('/admin/dailyitemcodes', [ProductionDashboardController::class, 'adminView'])->name('admin.dailyitemcodes');


Route::get('/test/ROPdata', [DashboardController::class, 'showROPData']);

Route::get('/daily-item-code/get-item-codes', [DailyItemCodeController::class, 'getItemCodes'])
    ->name('daily-item-code.get-item-codes');

Route::get('/daily-item-code/max-quantity', [DailyItemCodeController::class, 'getMaxQuantity'])
    ->name('daily-item-code.get-max-quantity');

Route::get('/send-api', [DailyItemCodeController::class, 'generateDataForSap'])->name('send.api');
Route::get('/search-item-codes', [DailyItemCodeController::class, 'search']);


Route::get('/daijo-mes-home', [DaijoMesHomeController::class, 'index'])
    ->name('daijo.mes.home');

Route::get('/production-day-dashboard', [ProductionDashboardController::class, 'index'])->name('djoni.dashboard');
Route::get('/api-log-dashboard', [DashboardController::class, 'apiLog'])->name('api.dashboard');
Route::get('/get-machines-by-item', [ProductionDashboardController::class, 'getMachinesByItem']);


Route::get('/id-card/operator', [OperatorUserController::class, 'showIdCard']);
Route::get('/operator-users/qr-codes', [OperatorUserController::class, 'showQr']);
Route::get('/operator-users/upload', [OperatorUserController::class, 'uploadForm'])->name('operator-users.upload');
Route::post('/operator-users/import', [OperatorUserController::class, 'import'])->name('operator-users.import');
Route::get('/operator-users/show-all', [OperatorUserController::class, 'showAllOperator'])->name('show.all.operators');
Route::get('/operator/create', [OperatorUserController::class, 'createOperator'])->name('operator.create');
Route::post('/operator/store', [OperatorUserController::class, 'storeOperator'])->name('operator.store');

Route::get('/external-users', [UpdateDailyController::class, 'getUsers']);

Route::get('/capacity-forecast-dashboard', [CapacityByForecastController::class, 'dashboard'])->name('capacity_forecast_dashboard');

Route::put('/operator/update-profile-picture', [OperatorUserController::class, 'updateProfilePicture'])->name('operator.updateProfilePicture');
Route::get('/operator-users', [OperatorUserController::class, 'index'])->name('operator.index');

Route::get('/zone/edit', [OperatorUserController::class, 'editZone'])->name('zone.edit');

// Handle the POST request to update a Master Zone
Route::post('/zone/update', [OperatorUserController::class, 'updateZone'])->name('zone.update');



Route::redirect('/', '/login');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('dashboard', [DashboardController::class,'index'])->middleware(['auth', 'verified'])
    ->name('dashboard');

    

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Auto-login route (must not interfere with other routes)
Route::get('/auto-login/{user_id}', function ($user_id) {
    // dd($user_id);
    $user = User::where('name', $user_id)->first();

    if ($user) {
        Auth::login($user);
        return redirect()->route('dashboard');
    }

    return redirect('/login')->withErrors(['error' => 'User not found']);
})->where('user_id', '[0-9A-Za-z]+'); // Ensure only valid user IDs

Route::middleware('auth')->group(function (){
    //-- Production project route
    Route::post('/dashboard/update-machine-job', [DashboardController::class, 'updateMachineJob'])->name('update.machine_job');
    Route::get('/generate-barcode/{item_code}/{quantity}', [DashboardController::class, 'itemCodeBarcode'])->name('generate.itemcode.barcode');
    Route::post('/process/itemproduction', [DashboardController::class, 'procesProductionBarcodes'])->name('process.productionbarcode');
    Route::post('/process/itemproduction/losspackage', [DashboardController::class, 'procesProductionBarcodesLoss'])->name('process.productionbarcodeloss');
    Route::get('/reset-jobs', [DashboardController::class, 'resetJobs'])->name('reset.jobs');
    Route::post('/update-employee-name', [DashboardController::class, 'updateEmployeeName'])->name('updateEmployeeName');
    Route::post('/verify-nik-password', [DashboardController::class, 'verifyNIKPassword'])->name('verify.nik.password');
    Route::post('/verify-nik-mould-change', [DashboardController::class, 'verifyNik'])->name('verify.nik');
    Route::post('/hourly-remarks/{id}/update-remark', [DashboardController::class, 'updateRemark']);
    Route::delete('/spk-scan/{id}', [DashboardController::class, 'deleteScanData'])->name('spk-scan.destroy');
    Route::put('/daily-item-codes/{id}/temporal-cycle-time', [DashboardController::class, 'updateCycleTime'])
    ->name('daily-item-codes.updateCycleTime');
    Route::post('/hourly-remarks', [DashboardController::class, 'storeHourlyRemark'])->name('hourly-remarks.store');

    Route::post('/daily-item-codes/update-remark/{id}', [DashboardController::class, 'updateRemarkDIC']);

    Route::put('/hourly-remarks/{id}/update-actual-production', [DashboardController::class, 'updateActualProduction'])
    ->name('hourly-remarks.updateActualProduction');
    Route::put('/hourly-remarks/{id}/ng', [DashboardController::class, 'updateNgProduction'])
    ->name('hourly-remarks.updateNg');


    Route::post('/mould-change/start', [DashboardController::class, 'startMouldChange'])->name('mould.change.start');
    Route::post('/mould-change/end', [DashboardController::class, 'endMouldChange'])->name('mould.change.end');
    Route::post('/adjust-machine/start', [DashboardController::class, 'startAdjustMachine'])->name('adjust.machine.start');
    Route::post('/adjust-machine/end', [DashboardController::class, 'endAdjustMachine'])->name('adjust.machine.end');
    Route::post('/repair-machine/start', [DashboardController::class, 'startRepairMachine'])->name('repair.machine.start');
    Route::post('/repair-machine/end', [DashboardController::class, 'endRepairMachine'])->name('repair.machine.end');

    Route::get('/dashboardplastic', [DashboardController::class, 'dashboardPlastic']);
    Route::get('/reset-job', [DashboardController::class, 'resetJob'])->name('reset.job');

    Route::get('barcode/index', [BarcodeController::class, 'index'])->name('barcode.base.index');
    Route::get('barcode/inandout/index', [BarcodeController::class, 'inandoutpage'])->name('inandout.index');
    Route::get('barcode/missing/index', [BarcodeController::class, 'missingbarcodeindex'])->name('missingbarcode.index');
    Route::post('barcode/missing/generate', [BarcodeController::class, 'missingbarcodegenerator'])->name('generateBarcodeMissing');
    Route::post('barcode/process/save', [BarcodeController::class, 'processInAndOut'])->name('process.in.and.out');
    Route::post('process/inandoutbarcode', [BarcodeController::class, 'storeInAndOut'])->name('processbarcodeinandout');
    Route::get('indexbarcode', [BarcodeController::class, 'indexBarcode'])->name('barcodeindex');
    Route::post('packaging-barcode-generate', [BarcodeController::class, 'generateBarcode'])->name('generatepackagingbarcode');
    Route::get('barcode/list', [BarcodeController::class, 'barcodelist'])->name('list.barcode');
    Route::get('barcode/latest/item', [BarcodeController::class, 'latestitemdetails'])->name('updated.barcode.item.position');
    Route::get('barcode/historytable', [BarcodeController::class, 'historybarcodelist'])->name('barcode.historytable');
    Route::get('/barcode/filter', [BarcodeController::class, 'filter'])->name('barcode.filter');
    Route::get('barcode/latest/item', [BarcodeController::class, 'latestitemdetails'])->name('updated.barcode.item.position');
    Route::get('barcode/stockall/{location?}', [BarcodeController::class, 'stockall'])->name('stockallbarcode');
    Route::get('barcode/summary', [BarcodeController::class, 'summaryDashboard'])->name('summaryDashboard');

    

    Route::get('/add-customer', [BarcodeController::class, 'addCustomer'])->name('customer.add');
    Route::post('/add-customer', [BarcodeController::class, 'storeCustomer'])->name('customer.store');
    Route::delete('/customer/{id}', [BarcodeController::class, 'destroyCustomer'])->name('customer.destroy');

    Route::get('master-item', [MasterItemController::class, 'index'])->middleware(['auth', 'verified'])->name('master-item.index');

    Route::get('/initialbarcode', [InitialBarcodeController::class, 'index'])->name('barcode.index');
    Route::post('/barcodes/generate', [InitialBarcodeController::class, 'generate'])->name('barcode.generate');
    Route::get('/manualbarcodes', [InitialBarcodeController::class, 'manualgenerate'])->name('manualbarcode.index');
    Route::post('/generate-barcode', [InitialBarcodeController::class, 'generateBarcode'])->name('generate.barcode');

    Route::get('/daily-item-codes', [DailyItemCodeController::class, 'index'])->name('daily-item-code.index');
    Route::post('/daily-item-code', [DailyItemCodeController::class, 'store'])->name('daily-item-code.store');
    Route::get('/daily-item-code', [DailyItemCodeController::class, 'create'])->name('daily-item-code.create');
    Route::post('/calculate-item', [DailyItemCodeController::class, 'calculateItem'])->name('calculate.item');
    Route::get('/daily-item-codes', [DailyItemCodeController::class, 'index'])->name('daily-item-code.index');
    Route::post('/apply-item-code/{machine_id}', [DailyItemCodeController::class, 'applyItemCode'])->name('apply-item-code');
    Route::get('/daily-item-codes/daily', [DailyItemCodeController::class, 'daily'])->name('daily-item-code.daily');
    Route::put('/daily-item-codes/{id}', [DailyItemCodeController::class, 'update'])->name('daily-item-code.update');
    Route::delete('/daily-item-codes/{id}', [DailyItemCodeController::class, 'destroy'])->name('daily-item-code.destroy');

    Route::get('/maintenance/index', [MaintenanceController::class, 'index'])->name('maintenance.index');

    Route::get('/so/index', [SOController::class, 'index'])->name('so.index');
    Route::get('/so/filter', [SOController::class, 'index'])->name('so.filter');
    Route::get('/so/filterauto', [SoController::class, 'filter'])->name('so.filterauto');
    Route::get('/so/process/{docNum}', [SOController::class, 'process'])->name('so.process');
    Route::post('/so/scan', [SOController::class, 'scanBarcode'])->name('so.scanBarcode');
    Route::get('/update-so-data/{docNum}', [SOController::class, 'updateSoData'])->name('update.so.data');
    Route::post('/import-excel', [SOController::class, 'import'])->name('import.so.data');

    Route::get('/second-daily-process', [SecondDailyController::class, 'index'])->name('second.daily.process.index');
    Route::get('/second-daily-process/create', [SecondDailyController::class, 'create'])->name('second.daily.process.create');
    Route::post('/second-daily-process/store', [SecondDailyController::class, 'store'])->name('second.daily.process.store');
    Route::get('/api/items', [SecondDailyController::class, 'searchItems'])->name('api.items');
    Route::get('/api/item/description', [SecondDailyController::class, 'getItemDescription'])->name('api.item.description');

    // belum dipake sepertinya
    Route::get('/assembly-daily-process', [AssemblyDailyController::class, 'index'])->name('assembly.daily.process.index');
    Route::get('/assembly-daily-process/create', [AssemblyDailyController::class, 'create'])->name('assembly.daily.process.create');
    //  belum dipake sepertinya

    Route::post('/assembly-daily-process/store', [AssemblyDailyController::class, 'store'])->name('assembly.daily.process.store');

    //-- Production project route

    Route::get('/production/bom/index', [BillOfMaterialController::class, 'index'])->name('production.bom.index');
    Route::get('/production/bill-of-material/{id}', [BillOfMaterialController::class, 'show'])->name('production.bom.show');
    Route::delete('/production/delete-bill-of-material/{id}', [BillOfMaterialController::class, 'destroy'])->name('production.bom.destroy');
    Route::put('/production/edit-bill-of-material/{id}', [BillOfMaterialController::class, 'update'])->name('production.bom.update');
    Route::put('/production/bom/child/{id}/cancel', [BillOfMaterialController::class, 'cancel'])->name('production.bom.child.cancel');
    Route::put('/production/child-edit-bill-of-material/{id}', [BillOfMaterialController::class, 'updateChild'])->name('production.bom.child.update');
    Route::delete('/production/delete-child-bill-of-material/child/{id}', [BillOfMaterialController::class, 'destroyChild'])->name('production.bom.child.destroy');
    Route::post('/production/add-new-child/{bomParent}', [BillOfMaterialController::class, 'storeChild'])->name('production.bom.child.store');
    Route::post('/production/add-new-child-excel/{bomParentId}', [BillOfMaterialController::class, 'uploadChildItems'])->name('production.bom.child.upload');
    Route::put('/production/bom/child/{child}/assign_type', [BillOfMaterialController::class, 'assignType'])->name('production.bom.child.assign_type');
    Route::put('/production/bom/child/{id}/updateStatus', [BillOfMaterialController::class, 'updateStatusChild'])->name('production.bom.child.updateStatus');
    Route::post('/production/bom/child/{id}/assign_process', [BillOfMaterialController::class, 'assignProcess'])->name('production.bom.child.assign_process');
    Route::get('/production/material-detail-child/{id}/show', [BillOfMaterialController::class, 'materialDetail'])->name('production.child.detail.material');
    Route::post('/production/bom/child/{childId}/addBrokenQuantity', [BillOfMaterialController::class, 'addBrokenQuantity'])->name('production.bom.child.addBrokenQuantity');
    Route::delete('/production/process-delete/{id}', [BillOfMaterialController::class, 'destroyProcess'])->name('production.process.delete');
    Route::post('/production/process/accept/{id}', [BillOfMaterialController::class, 'accept'])->name('production.process.accept');

    Route::post('/excel-bom-upload', [BillOfMaterialController::class, 'uploadExcelBom'])->name('excel.bom.upload');
    

    Route::get('/print-all-material/{id}', [BillOfMaterialController::class, 'printAllMaterial'])->name('printAllMaterial');


    Route::get('/dashboard/tv', [BillOfMaterialController::class, 'dashboardTv'])->name('dashboard.moulding.tv');

    Route::get('/production/bom/create', [BillOfMaterialController::class, 'create'])->name('production.bom.create');
    Route::get('/get-item-codes', [BillOfMaterialController::class, 'getItemCodes'])->name('get-item-codes');

    Route::post('/production/bom/store', [BillOfMaterialController::class, 'store'])->name('production.bom.store');

    Route::post('/workshop/update-username', [WorkshopController::class, 'updateUsername'])->name('update.username');
    Route::post('/workshop/set-scan-start', [WorkshopController::class, 'setScanStart'])->name('workshop.set_scan_start');
    Route::get('/workshop/remove-scan-in/{id}', [WorkshopController::class, 'removeScanIn'])->name('workshop.removeScanIn');
    Route::post('/workshop/scan-start', [WorkshopController::class, 'handleScanStart'])->name('workshop.scan');
    Route::get('/workshop/index/{id}', [WorkshopController::class, 'index'])->name('workshop.index');
    Route::post('/workshop/scanout', [WorkshopController::class, 'handeScanOut'])->name('workshop.scan_out');
    Route::get('/workshop/mainmenu', [WorkshopController::class, 'mainMenuByWorkshop'])->name('workshop.main.menu');
    Route::post('/workshop/addworker', [WorkshopController::class, 'addworker'])->name('workshop.add.worker');
    Route::post('/workshop/remark/store/{log_id}', [WorkshopController::class, 'storeRemark'])->name('remark.store');
    Route::put('/workshop/update-worker', [WorkshopController::class, 'updateWorker'])->name('workshop.update.worker');

    Route::get('/workshop/summary', [WorkshopController::class, 'summaryDashboard'])->name('workshop.summary.dashboard');

    //add manual
    Route::get('/workshop/add-manual', [WorkshopController::class, 'addManualWorkshop'])->name('workshop.addManual');
    Route::get('/workshop/children/{parentId}', [WorkshopController::class, 'getChildren'])->name('workshop.children');
    Route::post('/workshop/scan-manual', [WorkshopController::class, 'handleScanManual'])->name('workshop.scan.manual');
    //add manual

    Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
    Route::delete('/file/{id}', [FileController::class, 'destroy'])->name('file.delete');

    Route::resource('waiting_purchase_orders', WaitingPurchaseOrderController::class);
    Route::patch('waiting_purchase_orders/{waiting_purchase_order}/change-status', [WaitingPurchaseOrderController::class, 'changeStatus'])->name('waiting_purchase_orders.changeStatus');

    Route::get('/daily-waiting-purchase-orders/notification', function(){
        $waitingPurchaseOrders = \App\Models\WaitingPurchaseOrder::all();
        return new \App\Mail\DailyWaitingPurchaseOrders($waitingPurchaseOrders);
    });

    Route::get('deliveryschedule/averagemonth', [DeliveryScheduleController::class, 'averageschedule'])->name('delsched.averagemonth');
    Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])->name('indexds');
    Route::get("deliveryschedule/raw", [DeliveryScheduleController::class, "indexraw"])->name("rawdelsched");
    Route::get('deliveryschedule/wip', [DeliveryScheduleController::class, 'indexfinal'])->name('indexfinalwip');
    Route::get("delsched/start1", [DeliveryScheduleController::class, "step1"])->name("deslsched.step1");
    Route::get("delsched/start2", [DeliveryScheduleController::class, "step2"])->name("deslsched.step2");
    Route::get("delsched/start3", [DeliveryScheduleController::class, "step3"])->name("deslsched.step3");
    Route::get("delsched/start4", [DeliveryScheduleController::class, "step4"])->name("deslsched.step4");
    Route::get("delsched/wip/step1", [DeliveryScheduleController::class, "step1wip"])->name("delschedwip.step1");
    Route::get("delsched/wip/step2", [DeliveryScheduleController::class, "step2wip"])->name("delschedwip.step2");


        
    Route::get('new-delivery-schedule', [DeliveryScheduleController::class, 'deliveryScheduleNewIndex'])->name('testnewdelivery');
    // Route::get('/login', LivewireLoginSwitcher::class)->name('login');

    Route::get('/export-delivery-schedule', [DeliveryScheduleController::class, 'exportDeliverySchedule'])->name('export.delivery.schedule');
    Route::get('/export-delivery-schedule-template', [DeliveryScheduleController::class, 'exportTemplate'])
        ->name('export.delivery.schedule.template');

    Route::post('/import-delivery-schedule', [DeliveryScheduleController::class, 'importDeliverySchedule'])
        ->name('import.delivery.schedule');


    Route::get('export-delschedfinal', [DeliveryScheduleController::class, 'exportToExcel'])->name('export.delschedfinal');
    Route::get('delschedfinal/dashboard', [DeliveryScheduleController::class, 'dashboardUser'])->name('delschedfinal.dashboard');

    Route::get('management/delivery-schedule', [DeliveryScheduleController::class, 'deliveryManagement'])->name('management.delivery.index');
    Route::get('delete/delivery-schedule-data',[DeliveryScheduleController::class, 'deleteDeliveryScheduleData'])->name('delete.delivery.data');

    Route::delete('/image/{id}', [BillOfMaterialController::class, 'destroyImage'])->name('image.delete');

    Route::get('production/forecast', [ForecastProductionController::class, 'index'])->name('production.forecast.index');

    Route::get("updatepage/index", [UpdateDailyController::class, "index"])->name("indexupdatepage");
    Route::post("/processdailydata", [UpdateDailyController::class, 'update'])->name("updatedata");

    Route::resource('notification_recipients', NotificationRecepientController::class);

    Route::get('setting/holiday-schedule', [HolidayScheduleController::class, 'index'])->name('setting.holiday-schedule.index');
    Route::get('setting/holiday-schedule/create', [HolidayScheduleController::class, 'create'])->name('holiday-schedule.create');
    Route::post('setting/holiday-schedule/store', [HolidayScheduleController::class, 'store'])->name('holiday-schedule.store');

    Route::put('/holiday-schedule/{id}', [HolidayScheduleController::class, 'update'])->name('holiday-schedule.update');
    Route::get('holiday-schedule/export', [HolidayScheduleController::class, 'export'])->name('holiday-schedule.export');
    Route::post('holiday-schedule/import', [HolidayScheduleController::class, 'import'])->name('holiday-schedule.import');

    Route::get('/inventory/mtr', [InventoryController::class, 'showMtrInventory'])->name('inventory.mtr');
    Route::get('/inventory/fg', [InventoryController::class, 'showFgInventory'])->name('inventory.fg');
    Route::get('/inventory/line-list',  [InvLineListController::class, "index"])->name('invlinelist');
    Route::post("/add/line", [InvLineListController::class, "addline"])->name('addline');
    Route::put("/edit/line/{id}", [InvLineListController::class, "editline"])->name('editline');
    Route::delete("/delete/line/{linecode}", [InvLineListController::class, "deleteline"])->name('deleteline');

    Route::get("/production/capacity-forecast", [CapacityByForecastController::class, "index"])->name('capacityforecastindex');
    Route::get("/production/capacity-line", [CapacityByForecastController::class, "line"])->name('capacityforecastline');
    Route::get("/production/capacity-distribution", [CapacityByForecastController::class, "distribution"])->name('capacityforecastdistribution');
    Route::get("/production/capacity-detail", [CapacityByForecastController::class, "detail"])->name('capacityforecastdetail');

    Route::get("/production/capacity-forecast/view-step", [CapacityByForecastController::class, "viewstep1"])->name('viewstep1');
    Route::get("/production/capacity-forecast/step1", [CapacityByForecastController::class, "step1"])->name('step1');
    Route::get("/production/capacity-forecast/step1second", [CapacityByForecastController::class, "step1_second"])->name('step1second');

    Route::get("/production/capacity-forecast/step2", [CapacityByForecastController::class, "step2"])->name('step2');
    Route::get("/production/capacity-forecast/step2logic", [CapacityByForecastController::class, "step2logic"])->name('step2logic');

    Route::get("/production/capacity-forecast/step3", [CapacityByForecastController::class, "step3"])->name('step3');
    Route::get("/production/capacity-forecast/step3logic", [CapacityByForecastController::class, "step3logic"])->name('step3logic');
    Route::get("/production/capacity-forecast/step3last", [CapacityByForecastController::class, "step3logiclast"])->name('step3logiclast');



    Route::get('/master-list-item', [MasterListItemController::class, 'index'])->name('master.list.item');
    Route::post('/generate-machine-list', [MasterListItemController::class, 'generateMachineList'])
    ->name('generate.machine.list');


    Route::post('/submit/spk', [DashboardController::class, 'submitSPK'])->name('submit.spk');

});


require __DIR__.'/auth.php';
