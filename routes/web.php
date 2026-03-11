<?php

use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\SellerWebController;
use App\Http\Controllers\Web\SupplierWebController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'supplier' => redirect()->route('supplier.dashboard'),
            'seller'   => redirect()->route('seller.dashboard'),
            default    => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Fournisseur ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'ensure.role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierWebController::class, 'dashboard'])->name('dashboard');

    Route::get('/sellers', [SupplierWebController::class, 'sellers'])->name('sellers');
    Route::post('/sellers', [SupplierWebController::class, 'createSeller'])->name('sellers.create');
    Route::put('/sellers/{sellerId}', [SupplierWebController::class, 'updateSeller'])->name('sellers.update');
    Route::delete('/sellers/{sellerId}', [SupplierWebController::class, 'deleteSeller'])->name('sellers.delete');

    Route::get('/stocks', [SupplierWebController::class, 'stocks'])->name('stocks');
    Route::post('/stocks', [SupplierWebController::class, 'createStock'])->name('stocks.create');
    Route::post('/stocks/{stockId}/allocate', [SupplierWebController::class, 'allocateSeller'])->name('stocks.allocate');

    Route::get('/payments', [SupplierWebController::class, 'payments'])->name('payments');
    Route::put('/payments/{paymentId}', [SupplierWebController::class, 'updatePayment'])->name('payments.update');
});

// ── Vendeur ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'ensure.role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerWebController::class, 'dashboard'])->name('dashboard');

    Route::get('/clients', [SellerWebController::class, 'clients'])->name('clients');
    Route::post('/clients', [SellerWebController::class, 'createClient'])->name('clients.create');
    Route::put('/clients/{clientId}', [SellerWebController::class, 'updateClient'])->name('clients.update');
    Route::delete('/clients/{clientId}', [SellerWebController::class, 'deleteClient'])->name('clients.delete');

    Route::get('/allocations', [SellerWebController::class, 'allocations'])->name('allocations');

    // Commandes pour les clients
    Route::get('/orders', [SellerWebController::class, 'orders'])->name('orders');
    Route::post('/orders', [SellerWebController::class, 'createOrderForClient'])->name('orders.create');
    Route::put('/orders/{orderId}/status', [SellerWebController::class, 'updateOrderStatus'])->name('orders.status');

    Route::get('/payments', [SellerWebController::class, 'payments'])->name('payments');
    Route::put('/payments/{paymentId}', [SellerWebController::class, 'updatePayment'])->name('payments.update');
});

