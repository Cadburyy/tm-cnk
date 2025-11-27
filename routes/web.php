<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BudgetController; 
use App\Http\Controllers\SettingsController; 
use App\Http\Controllers\OutingController; 

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    Route::resource('items', ItemController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::get('/items/export-resume-detail', [ItemController::class, 'exportResumeDetail'])->name('items.exportResumeDetail');
    Route::post('/items/bulk-destroy', [ItemController::class, 'bulkDestroy'])->name('items.bulkDestroy');
});

Route::middleware(['auth', 'role:AdminIT|Admin'])->group(function () {
    
    Route::resource('outings', OutingController::class);
    Route::post('/outings/bulk-destroy', [OutingController::class, 'bulkDestroy'])->name('outings.bulkDestroy');

    Route::resource('budget', BudgetController::class)->only(['index', 'store', 'edit', 'update']); 
    Route::post('/budget/bulk-destroy', [BudgetController::class, 'bulkDestroy'])->name('budget.bulkDestroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index'); 
    Route::get('/settings/appearance', [SettingsController::class, 'editAppearance'])->name('settings.appearance');
    Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');

    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'role:AdminIT'])->group(function () {
    Route::resource('roles', RoleController::class);
});
