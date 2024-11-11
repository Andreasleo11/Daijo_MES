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
Route::get('/production/bom/create', [BillOfMaterialController::class, 'create'])->name('production.bom.create');
Route::post('/production/bom/store', [BillOfMaterialController::class, 'store'])->name('production.bom.store');

require __DIR__.'/auth.php';
