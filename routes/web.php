<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSalesController;
use App\Http\Controllers\Admin\CustomerAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public product routes
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);
Route::get('/category/{name}', [ProductController::class, 'category']);

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'view']);
    Route::post('/cart/add/{product}', [CartController::class, 'add']);
    Route::post('/cart/update/{product}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove']);
    Route::post('/cart/update-item/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/remove-item/{item}', [CartController::class, 'removeItem']);

    // Checkout (Inertia + Stripe intent)
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    Route::get('/orders', [CheckoutController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin auth
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');
});

// Admin area (auth + is_admin gate)
Route::middleware(['auth', 'can:is_admin'])->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::resource('/admin/admins', AdminUserController::class)->parameters([
        'admins' => 'admin'
    ])->except(['show']);
    Route::get('/admin/sales', [AdminSalesController::class, 'index'])->name('admin.sales');
    Route::get('/admin/sales/export', [AdminSalesController::class, 'export'])->name('admin.sales.export');
    Route::get('/admin/customers', [CustomerAdminController::class, 'index'])->name('admin.customers');
    Route::get('/admin/customers/export', [CustomerAdminController::class, 'export'])->name('admin.customers.export');
    Route::get('/admin/customers/{user}', [CustomerAdminController::class, 'show'])->name('admin.customers.show');
    Route::get('/admin/customers/{user}/export-orders', [CustomerAdminController::class, 'exportOrders'])->name('admin.customers.exportOrders');
    Route::get('/admin/orders', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/export', [OrderAdminController::class, 'export'])->name('admin.orders.export');
    Route::get('/admin/orders/{order}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::put('/admin/orders/{order}', [OrderAdminController::class, 'update'])->name('admin.orders.update');
    Route::post('/admin/orders/{order}/refund', [OrderAdminController::class, 'refund'])->name('admin.orders.refund');
    Route::resource('/admin/products', ProductAdminController::class)->except(['show']);
});

// Vue SPA mounted under /app
Route::get('/app/{any?}', function () {
    return view('spa');
})->where('any', '.*');

require __DIR__.'/auth.php';

// Stripe webhook endpoint
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');
