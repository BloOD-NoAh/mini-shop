<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\ProductAdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public product routes
Route::get('/', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);
Route::get('/category/{name}', [ProductController::class, 'category']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'view']);
    Route::post('/cart/add/{product}', [CartController::class, 'add']);
    Route::post('/cart/update/{product}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove']);

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'page']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm']);
    Route::get('/checkout/success', [CheckoutController::class, 'success']);
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel']);
    Route::get('/orders/{order}', [CheckoutController::class, 'show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin product resource (auth + is_admin gate)
Route::middleware(['auth', 'can:is_admin'])->group(function () {
    Route::resource('/admin/products', ProductAdminController::class)->except(['show']);
});

// Vue SPA mounted under /app
Route::get('/app/{any?}', function () {
    return view('spa');
})->where('any', '.*');

require __DIR__.'/auth.php';
