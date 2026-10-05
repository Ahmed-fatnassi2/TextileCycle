<?php

use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\DepositPointController;
use App\Http\Controllers\DepositController;
use Illuminate\Support\Facades\Route;

// ---------- FRONT OFFICE ----------
Route::get('/', function () {
    return view('front.home');
})->name('home');

Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');

// ---------- BACK OFFICE ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('deposit-points', DepositPointController::class);
    Route::resource('deposits', AdminDepositController::class);
});