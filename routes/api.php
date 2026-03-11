<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (pas d'authentification requise)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Routes protégées (token requis)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications (tous les rôles)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllRead']);
    });

    /*
    |----------------------------------------------------------------------
    | Fournisseur
    |----------------------------------------------------------------------
    */
    Route::middleware('role:supplier')->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierController::class, 'dashboard']);

        // Vendeurs
        Route::get('/sellers', [SupplierController::class, 'sellers']);
        Route::post('/sellers', [SupplierController::class, 'createSeller']);
        Route::patch('/sellers/{sellerId}', [SupplierController::class, 'updateSeller']);
        Route::delete('/sellers/{sellerId}', [SupplierController::class, 'deleteSeller']);

        // Stocks hebdomadaires
        Route::get('/stocks', [SupplierController::class, 'stocks']);
        Route::post('/stocks', [SupplierController::class, 'createStock']);
        Route::patch('/stocks/{stockId}', [SupplierController::class, 'updateStock']);

        // Allocations
        Route::post('/stocks/{stockId}/allocate', [SupplierController::class, 'allocateSeller']);

        // Paiements
        Route::get('/payments', [SupplierController::class, 'payments']);
        Route::patch('/payments/{paymentId}', [SupplierController::class, 'updatePaymentStatus']);
    });

    /*
    |----------------------------------------------------------------------
    | Vendeur
    |----------------------------------------------------------------------
    */
    Route::middleware('role:seller')->prefix('seller')->group(function () {
        Route::get('/dashboard', [SellerController::class, 'dashboard']);

        // Clients
        Route::get('/clients', [SellerController::class, 'clients']);
        Route::post('/clients', [SellerController::class, 'createClient']);
        Route::patch('/clients/{clientId}', [SellerController::class, 'updateClient']);
        Route::delete('/clients/{clientId}', [SellerController::class, 'deleteClient']);

        // Stock reçu du fournisseur
        Route::get('/allocations', [SellerController::class, 'myAllocations']);

        // Commande vers le fournisseur
        Route::post('/orders', [SellerController::class, 'placeOrder']);

        // Paiements
        Route::get('/payments', [SellerController::class, 'payments']);
        Route::patch('/payments/{paymentId}', [SellerController::class, 'updateClientPaymentStatus']);
    });

    /*
    |----------------------------------------------------------------------
    | Client
    |----------------------------------------------------------------------
    */
    Route::middleware('role:client')->prefix('client')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'dashboard']);

        // Catalogue disponible
        Route::get('/stock', [ClientController::class, 'availableStock']);

        // Commandes
        Route::post('/orders', [ClientController::class, 'placeOrder']);
        Route::get('/orders', [ClientController::class, 'myOrders']);

        // Paiements
        Route::get('/payments', [ClientController::class, 'myPayments']);
    });
});
