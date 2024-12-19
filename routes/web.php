<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\BillOfMaterialController;
use App\Http\Controllers\Production\WorkshopController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Store\SOController;
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

    Route::get('/production/bom/create', [BillOfMaterialController::class, 'create'])->name('production.bom.create');
    Route::get('/get-item-codes', [BillOfMaterialController::class, 'getItemCodes'])->name('get-item-codes');

    Route::post('/production/bom/store', [BillOfMaterialController::class, 'store'])->name('production.bom.store');

    Route::post('/workshop/update-username', [WorkshopController::class, 'updateUsername'])->name('update.username');
    Route::post('/workshop/scan-start', [WorkshopController::class, 'handleScanStart'])->name('workshop.scan');
    Route::get('/workshop/index/{id}', [WorkshopController::class, 'index'])->name('workshop.index');
    Route::post('/workshop/scanout', [WorkshopController::class, 'handeScanOut'])->name('workshop.scan_out');
    Route::get('/workshop/mainmenu', [WorkshopController::class, 'mainMenuByWorkshop'])->name('workshop.main.menu');
    Route::post('/workshop/addworker', [WorkshopController::class, 'addworker'])->name('workshop.add.worker');

    Route::get('/workshop/summary', [WorkshopController::class, 'summaryDashboard'])->name('workshop.summary.dashboard');

    Route::get('/so/index', [SOController::class, 'index'])->name('so.index');
    Route::get('/so/filter', [SOController::class, 'index'])->name('so.filter');
    Route::get('/so/filterauto', [SoController::class, 'filter'])->name('so.filterauto');
    Route::get('/so/process/{docNum}', [SOController::class, 'process'])->name('so.process');
    Route::post('/so/scan', [SOController::class, 'scanBarcode'])->name('so.scanBarcode');
    Route::get('/update-so-data/{docNum}', [SOController::class, 'updateSoData'])->name('update.so.data');

    Route::post('/import-excel', [SOController::class, 'import'])->name('import.so.data');
});

require __DIR__.'/auth.php';
