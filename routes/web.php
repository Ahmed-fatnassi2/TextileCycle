<?php

use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\AssociationController as AdminAssociationController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Association\DonationController as AssociationDonationController;
use App\Http\Controllers\AssociationRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DepositPointController;
use App\Http\Controllers\DepositController;
use Illuminate\Support\Facades\Route;

// ---------- FRONT OFFICE ----------
Route::get('/', function () {
    return view('front.home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'register'])->name('register');
    Route::post('/inscription', [AuthController::class, 'store'])->name('register.store');
    Route::get('/connexion', [AuthController::class, 'login'])->name('login');
    Route::post('/connexion', [AuthController::class, 'authenticate'])->name('login.store');
});

Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
Route::middleware('auth')->group(function () {
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/association/demande', [AssociationRequestController::class, 'create'])->name('associations.create');
    Route::post('/association/demande', [AssociationRequestController::class, 'store'])->name('associations.store');
    Route::get('/association/statut', [AssociationRequestController::class, 'status'])->name('association.status');
    Route::resource('/association/dons', AssociationDonationController::class)
        ->parameters(['dons' => 'donation'])
        ->names('association.donations');
});

// ---------- BACK OFFICE ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('deposit-points', DepositPointController::class);
    Route::resource('deposits', AdminDepositController::class);
    Route::resource('associations', AdminAssociationController::class);
    Route::resource('donations', AdminDonationController::class);
});