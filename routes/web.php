<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SettingsController; 

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    Route::get('/items/export-resume-detail', [ItemController::class, 'exportResumeDetail'])->name('items.exportResumeDetail');
    Route::post('/items/export-selected', [ItemController::class, 'exportSelected'])->name('items.exportSelected');

    Route::resource('items', ItemController::class); 
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index'); 
    
    Route::get('/settings/appearance', [SettingsController::class, 'editAppearance'])->name('settings.appearance');
    Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');
});