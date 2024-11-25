<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\BillOfMaterialController;
use App\Http\Controllers\Production\WorkshopController;
use App\Http\Controllers\DashboardController;
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

Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('dashboard', [DashboardController::class,'index'])->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get('/production/bom/index', [BillOfMaterialController::class, 'index'])->name('production.bom.index');

Route::get('/production/bill-of-material/{id}', [BillOfMaterialController::class, 'show'])->name('production.bom.show');
Route::delete('/production/delete-bill-of-material/{id}', [BillOfMaterialController::class, 'destroy'])->name('production.bom.destroy');
Route::put('/production/edit-bill-of-material/{id}', [BillOfMaterialController::class, 'update'])->name('production.bom.update');
Route::put('/production/child-edit-bill-of-material/{id}', [BillOfMaterialController::class, 'updateChild'])->name('production.bom.child.update');
Route::delete('/production/delete-child-bill-of-material/child/{id}', [BillOfMaterialController::class, 'destroyChild'])->name('production.bom.child.destroy');
Route::post('/production/add-new-child/{bomParent}', [BillOfMaterialController::class, 'storeChild'])->name('production.bom.child.store');
Route::post('/production/add-new-child-excel/{bomParentId}', [BillOfMaterialController::class, 'uploadChildItems'])->name('production.bom.child.upload');
Route::put('/production/bom/child/{child}/assign_type', [BillOfMaterialController::class, 'assignType'])->name('production.bom.child.assign_type');
Route::put('/production/bom/child/{id}/updateStatus', [BillOfMaterialController::class, 'updateStatusChild'])->name('production.bom.child.updateStatus');
Route::post('/production/bom/child/{id}/assign_process', [BillOfMaterialController::class, 'assignProcess'])->name('production.bom.child.assign_process');
Route::get('/production/material-detail-child/{id}/show', [BillOfMaterialController::class, 'materialDetail'])->name('production.child.detail.material');


Route::get('/production/bom/create', [BillOfMaterialController::class, 'create'])->name('production.bom.create');
Route::post('/production/bom/store', [BillOfMaterialController::class, 'store'])->name('production.bom.store');


Route::post('/workshop/update-username', [WorkshopController::class, 'updateUsername'])->name('update.username');
Route::post('/workshop/scan-start', [WorkshopController::class, 'handleScanStart'])->name('workshop.scan');
Route::get('/workshop/index/{id}', [WorkshopController::class, 'index'])->name('workshop.index');
Route::post('/workshop/scanout', [WorkshopController::class, 'handeScanOut'])->name('workshop.scan_out');
Route::get('/workshop/mainmenu', [WorkshopController::class, 'mainMenuByWorkshop'])->name('workshop.main.menu');
Route::post('/workshop/addworker', [WorkshopController::class, 'addworker'])->name('workshop.add.worker');

Route::get('/workshop/summary', [WorkshopController::class, 'summaryDashboard'])->name('workshop.summary.dashboard');


require __DIR__.'/auth.php';