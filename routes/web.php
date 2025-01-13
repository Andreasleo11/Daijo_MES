<?php

use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\DailyItemCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\BillOfMaterialController;
use App\Http\Controllers\Production\WorkshopController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InitialBarcodeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MasterItemController;
use App\Http\Controllers\NotificationRecepientController;
use App\Http\Controllers\SecondDailyController;
use App\Http\Controllers\Store\SOController;
use App\Http\Controllers\WaitingPurchaseOrderController;
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

Route::redirect('/', '/login');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('dashboard', [DashboardController::class,'index'])->middleware(['auth', 'verified'])
    ->name('dashboard');


// Route::get('/login', LivewireLoginSwitcher::class)->name('login');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function (){
    Route::post('/dashboard/update-machine-job', [DashboardController::class, 'updateMachineJob'])->name('update.machine_job');
    Route::get('/generate-barcode/{item_code}/{quantity}', [DashboardController::class, 'itemCodeBarcode'])->name('generate.itemcode.barcode');
    Route::post('/process/itemproduction', [DashboardController::class, 'procesProductionBarcodes'])->name('process.productionbarcode');
    Route::get('/reset-jobs', [DashboardController::class, 'resetJobs'])->name('reset.jobs');
    Route::post('/update-employee-name', [DashboardController::class, 'updateEmployeeName'])->name('updateEmployeeName');
    Route::get('/dashboardplastic', [DashboardController::class, 'dashboardPlastic']);
    Route::get('/reset-job', [DashboardController::class, 'resetJob'])->name('reset.job');

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

    Route::get('/dashboard/tv', [BillOfMaterialController::class, 'dashboardTv']);

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

    Route::get('/so/index', [SOController::class, 'index'])->name('so.index');
    Route::get('/so/filter', [SOController::class, 'index'])->name('so.filter');
    Route::get('/so/filterauto', [SoController::class, 'filter'])->name('so.filterauto');
    Route::get('/so/process/{docNum}', [SOController::class, 'process'])->name('so.process');
    Route::post('/so/scan', [SOController::class, 'scanBarcode'])->name('so.scanBarcode');
    Route::get('/update-so-data/{docNum}', [SOController::class, 'updateSoData'])->name('update.so.data');
    Route::post('/import-excel', [SOController::class, 'import'])->name('import.so.data');

    Route::get('/initialbarcode', [InitialBarcodeController::class, 'index'])->name('barcode.index');
    Route::post('/barcodes/generate', [InitialBarcodeController::class, 'generate'])->name('barcode.generate');
    Route::get('/manualbarcodes', [InitialBarcodeController::class, 'manualgenerate'])->name('manualbarcode.index');
    Route::post('/generate-barcode', [InitialBarcodeController::class, 'generateBarcode'])->name('generate.barcode');

    Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
    Route::delete('/file/{id}', [FileController::class, 'destroy'])->name('file.delete');

    Route::get('master-item', [MasterItemController::class, 'index'])->middleware(['auth', 'verified'])->name('master-item.index');

    Route::get('/daily-item-codes', [DailyItemCodeController::class, 'index'])->name('daily-item-code.index');
    Route::post('/daily-item-code', [DailyItemCodeController::class, 'store'])->name('daily-item-code.store');
    Route::get('/daily-item-code', [DailyItemCodeController::class, 'create'])->name('daily-item-code.create');
    Route::post('/calculate-item', [DailyItemCodeController::class, 'calculateItem'])->name('calculate.item');
    Route::get('/daily-item-codes', [DailyItemCodeController::class, 'index'])->name('daily-item-code.index');
    Route::post('/apply-item-code/{machine_id}', [DailyItemCodeController::class, 'applyItemCode'])->name('apply-item-code');
    Route::get('/daily-item-codes/daily', [DailyItemCodeController::class, 'daily'])->name('daily-item-code.daily');
    Route::put('/daily-item-codes/{id}', [DailyItemCodeController::class, 'update'])->name('daily-item-code.update');

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

    Route::get('/maintenance/index', [MaintenanceController::class, 'index'])->name('maintenance.index');

    Route::get('/second-daily-process', [SecondDailyController::class, 'index'])->name('second.daily.process.index');
    Route::get('/second-daily-process/create', [SecondDailyController::class, 'create'])->name('second.daily.process.create');
    Route::post('/second-daily-process/store', [SecondDailyController::class, 'store'])->name('second.daily.process.store');
    Route::get('/api/items', [SecondDailyController::class, 'searchItems'])->name('api.items');
    Route::get('/api/item/description', [SecondDailyController::class, 'getItemDescription'])->name('api.item.description');

    Route::resource('waiting_purchase_orders', WaitingPurchaseOrderController::class);
    Route::patch('waiting_purchase_orders/{waiting_purchase_order}/change-status', [WaitingPurchaseOrderController::class, 'changeStatus'])->name('waiting_purchase_orders.changeStatus');

    // Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])->name('indexds');
    // Route::get("deliveryschedule/raw", [DeliveryScheduleController::class, "indexraw"])->name("rawdelsched");
    // Route::get('deliveryschedule/wip', [DeliveryScheduleController::class, 'indexfinal'])->name('indexfinalwip');
    // Route::get("delsched/start1", [DeliveryScheduleController::class, "step1"])->name("deslsched.step1");
    // Route::get("delsched/start2", [DeliveryScheduleController::class, "step2"])->name("deslsched.step2");
    // Route::get("delsched/start3", [DeliveryScheduleController::class, "step3"])->name("deslsched.step3");
    // Route::get("delsched/start4", [DeliveryScheduleController::class, "step4"])->name("deslsched.step4");
    // Route::get("delsched/wip/step1", [DeliveryScheduleController::class, "step1wip"])->name("delschedwip.step1");
    // Route::get("delsched/wip/step2", [DeliveryScheduleController::class, "step2wip"])->name("delschedwip.step2");

    Route::resource('notification_recipients', NotificationRecepientController::class);

    Route::get('/daily-waiting-purchase-orders/notification', function(){
        $waitingPurchaseOrders = \App\Models\WaitingPurchaseOrder::all();
        return new \App\Mail\DailyWaitingPurchaseOrders($waitingPurchaseOrders);
    });
});


require __DIR__.'/auth.php';
