<?php

use App\Http\Controllers\Web\ClientWebController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\SellerWebController;
use App\Http\Controllers\Web\SupplierWebController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

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
    Route::post('/orders', [SellerWebController::class, 'placeOrder'])->name('orders.place');

    Route::get('/payments', [SellerWebController::class, 'payments'])->name('payments');
    Route::put('/payments/{paymentId}', [SellerWebController::class, 'updatePayment'])->name('payments.update');
});

// ── Client ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'ensure.role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [ClientWebController::class, 'orders'])->name('orders');
    Route::post('/orders', [ClientWebController::class, 'placeOrder'])->name('orders.place');
});
