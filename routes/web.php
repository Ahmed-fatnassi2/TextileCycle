<?php

use App\Http\Controllers\Admin\AssociationController as AdminAssociationController;
use App\Http\Controllers\Admin\DepositCheckinController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\DepositPointController;
use App\Http\Controllers\Admin\DonationCalendarController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\MaterialBatchController;
use App\Http\Controllers\Admin\RepairRequestController as AdminRepairRequestController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UpcycledProductController;
use App\Http\Controllers\Admin\UpcyclingDataController;
use App\Http\Controllers\Admin\UpcyclingIdeaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Association\DonationController as AssociationDonationController;
use App\Http\Controllers\AssociationRequestController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\UpcycledProductCatalogController;
use App\Http\Controllers\UpcyclingIdeasController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopOwner\RepairRequestController as WorkshopOwnerRepairRequestController;
use App\Models\UpcycledProduct;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
| Authentification : les routes de connexion, inscription, déconnexion, mot de passe oublié,
| vérification d'email et double authentification sont enregistrées par Laravel Fortify
| (voir config/fortify.php et App\Providers\FortifyServiceProvider).
|
| Autorisations : Spatie Permission. Les routes exigent une permission ("permission:...") plutôt
| qu'un rôle, et "verified" impose une adresse e-mail confirmée.
*/

// ---------- FRONT OFFICE (public) ----------
Route::get('/', function () {
    $featuredProducts = Schema::hasTable('upcycled_products')
        ? UpcycledProduct::with('materialBatch')->latest()->take(3)->get()
        : collect();

    return view('front.home', compact('featuredProducts'));
})->name('home');

Route::get('/produits-upcycles', [UpcycledProductCatalogController::class, 'index'])
    ->name('products.index');
// Idées IA "Que créer avec ma matière ?" (public, limité pour maîtriser le coût de l'API)
Route::get('/produits-upcycles/idees', [UpcyclingIdeasController::class, 'index'])->name('products.ideas');
Route::post('/produits-upcycles/idees', [UpcyclingIdeasController::class, 'suggest'])
    ->middleware('throttle:10,1')
    ->name('products.ideas.suggest');
Route::get('/produits-upcycles/{upcycledProduct}', [UpcycledProductCatalogController::class, 'show'])
    ->name('products.show');

// Chatbot "assistant de dépôt" (OpenAI). Limité pour maîtriser le coût de l'API.
Route::post('/assistant/chat', [AssistantController::class, 'chat'])
    ->middleware('throttle:15,1')
    ->name('assistant.chat');

Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');

// ---------- PROFIL (connecté, même sans e-mail vérifié : on doit pouvoir corriger son adresse) ----------
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profil/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::get('/profil/securite', [ProfileController::class, 'confirmSecurity'])->name('profile.security');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/utilisateurs/{user}/photo', [ProfileController::class, 'photo'])->name('profile.photo');
});

// ---------- ESPACE CITOYEN (connecté + e-mail vérifié) ----------
Route::middleware(['auth', 'verified', 'permission:repairs.request'])->group(function () {
    Route::get('/repairs', [RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{repairRequest}', [RepairController::class, 'show'])->name('repairs.show');
    Route::post('/repairs/{repairRequest}/quote', [RepairController::class, 'respondToQuote'])->name('repairs.quote');
    Route::post('/repairs/{repairRequest}/pickup', [RepairController::class, 'confirmPickup'])->name('repairs.pickup');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('permission:deposits.create')->group(function () {
        Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
    });
    Route::get('/mes-depots', [DepositController::class, 'mine'])->name('deposits.mine');
    Route::get('/mes-depots/{deposit}', [DepositController::class, 'show'])->name('deposits.mine.show');
    Route::get('/mes-depots/{deposit}/qr', [DepositController::class, 'downloadQr'])->name('deposits.mine.qr');

    Route::get('/association/demande', [AssociationRequestController::class, 'create'])->name('associations.create');
    Route::post('/association/demande', [AssociationRequestController::class, 'store'])->name('associations.store');
    Route::get('/association/statut', [AssociationRequestController::class, 'status'])->name('association.status');
    Route::resource('/association/dons', AssociationDonationController::class)
        ->parameters(['dons' => 'donation'])
        ->names('association.donations');
});

// ---------- ESPACE ATELIER ----------
Route::prefix('atelier')->name('workshop-owner.')->middleware(['auth', 'verified', 'permission:workshop.repairs'])->group(function () {
    Route::get('/', [WorkshopOwnerRepairRequestController::class, 'dashboard'])->name('dashboard');
    Route::get('/reparations', [WorkshopOwnerRepairRequestController::class, 'index'])->name('repairs.index');
    Route::get('/reparations/{repairRequest}', [WorkshopOwnerRepairRequestController::class, 'show'])->name('repairs.show');
    Route::put('/reparations/{repairRequest}/devis', [WorkshopOwnerRepairRequestController::class, 'quote'])->name('repairs.quote');
    Route::patch('/reparations/{repairRequest}/statut', [WorkshopOwnerRepairRequestController::class, 'updateStatus'])->name('repairs.status');
});

// ---------- BACK OFFICE : chaque module exige sa propre permission ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'permission:admin.access'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Collecte : points, dépôts, validation par QR code
    Route::middleware('permission:deposits.manage')->group(function () {
        Route::resource('deposit-points', DepositPointController::class);

        // Déclarées avant la resource pour ne pas être capturées par deposits/{deposit}
        Route::get('deposits/scan', [DepositCheckinController::class, 'scan'])->name('deposits.scan');
        Route::get('deposits/lookup', [DepositCheckinController::class, 'lookup'])->name('deposits.lookup');
        Route::get('deposits/checkin/{code}', [DepositCheckinController::class, 'show'])->name('deposits.checkin');
        Route::post('deposits/checkin/{code}', [DepositCheckinController::class, 'confirm'])->name('deposits.checkin.confirm');

        Route::resource('deposits', AdminDepositController::class);
    });

    // Réparation : ateliers et demandes
    Route::middleware('permission:repairs.manage')->group(function () {
        Route::resource('workshops', AdminWorkshopController::class);
        Route::resource('repair-requests', AdminRepairRequestController::class);
    });

    // Associations et dons
    Route::middleware('permission:donations.manage')->group(function () {
        Route::resource('associations', AdminAssociationController::class);

        // Calendrier des livraisons (avant la resource pour ne pas être capturé par donations/{donation})
        Route::get('donations/calendar', [DonationCalendarController::class, 'index'])->name('donations.calendar');
        Route::get('donations/calendar/events', [DonationCalendarController::class, 'events'])->name('donations.calendar.events');
        Route::post('donations/calendar/optimize', [DonationCalendarController::class, 'optimize'])
            ->middleware('throttle:5,1')
            ->name('donations.calendar.optimize');
        Route::patch('donations/{donation}/reschedule', [DonationCalendarController::class, 'reschedule'])->name('donations.reschedule');

        Route::resource('donations', AdminDonationController::class);
    });

    // Transformation : lots de matière, produits upcyclés, import / export, PDF, idées IA
    Route::middleware('permission:upcycling.manage')->group(function () {
        Route::prefix('upcycling')->name('upcycling.')->controller(UpcyclingDataController::class)->group(function () {
            Route::get('export', 'export')->name('export');
            Route::get('rapport', 'report')->name('report');
            Route::get('import', 'importForm')->name('import');
            Route::post('import/apercu', 'preview')->name('import.preview');
            Route::post('import/confirmer', 'confirm')->name('import.confirm');
            Route::post('import/annuler', 'cancel')->name('import.cancel');
            Route::get('import/modele/{type}', 'template')->name('template');
        });
        Route::get('material-batches/{material_batch}/pdf', [UpcyclingDataController::class, 'batchReport'])->name('material-batches.pdf');
        Route::get('upcycling/idees', [UpcyclingIdeaController::class, 'index'])->name('upcycling.ideas');
        Route::post('upcycling/idees', [UpcyclingIdeaController::class, 'suggest'])
            ->middleware('throttle:10,1')
            ->name('upcycling.ideas.suggest');

        Route::resource('material-batches', MaterialBatchController::class);
        Route::resource('upcycled-products', UpcycledProductController::class);
    });

    // Utilisateurs, rôles et permissions
    Route::resource('users', AdminUserController::class)
        ->only(['index', 'create', 'store', 'show', 'update', 'destroy'])
        ->middleware('permission:users.manage');
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::put('roles', [RolePermissionController::class, 'update'])->name('roles.update');
    });
});
