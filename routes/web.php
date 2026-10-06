<?php

use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\AssociationController as AdminAssociationController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\MaterialBatchController;
use App\Http\Controllers\Admin\UpcycledProductController;
use App\Http\Controllers\Association\DonationController as AssociationDonationController;
use App\Http\Controllers\AssociationRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DepositPointController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;
use App\Http\Controllers\Admin\RepairRequestController as AdminRepairRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\UpcycledProductCatalogController;
use App\Models\UpcycledProduct;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// ---------- FRONT OFFICE ----------
Route::get('/', function () {
    $featuredProducts = Schema::hasTable('upcycled_products')
        ? UpcycledProduct::with('materialBatch')->latest()->take(3)->get()
        : collect();

    return view('front.home', compact('featuredProducts'));
})->name('home');

Route::get('/produits-upcycles', [UpcycledProductCatalogController::class, 'index'])
    ->name('products.index');
Route::get('/produits-upcycles/{upcycledProduct}', [UpcycledProductCatalogController::class, 'show'])
    ->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'register'])->name('register');
    Route::post('/inscription', [AuthController::class, 'store'])->name('register.store');
    Route::get('/connexion', [AuthController::class, 'login'])->name('login');
    Route::post('/connexion', [AuthController::class, 'authenticate'])->name('login.store');
});

Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
Route::middleware('auth')->group(function () {
    Route::get('/repairs', [RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{repairRequest}', [RepairController::class, 'show'])->name('repairs.show');
    Route::post('/repairs/{repairRequest}/quote', [RepairController::class, 'respondToQuote'])->name('repairs.quote');
});
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
    Route::resource('workshops', AdminWorkshopController::class);
    Route::resource('repair-requests', AdminRepairRequestController::class);
    Route::resource('associations', AdminAssociationController::class);
    Route::resource('donations', AdminDonationController::class);
    Route::resource('material-batches', MaterialBatchController::class);
    Route::resource('upcycled-products', UpcycledProductController::class);
    Route::resource('users', AdminUserController::class)->only(['index', 'show', 'update', 'destroy']);
});
