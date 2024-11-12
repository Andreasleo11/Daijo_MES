<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\BillOfMaterialController;

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

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
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


Route::get('/production/bom/create', [BillOfMaterialController::class, 'create'])->name('production.bom.create');
Route::post('/production/bom/store', [BillOfMaterialController::class, 'store'])->name('production.bom.store');




require __DIR__.'/auth.php';
