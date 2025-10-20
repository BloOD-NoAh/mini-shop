<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AddressController;

Route::prefix('mobile')->group(function () {
    // Public auth
    Route::post('/register', [MobileAuthController::class, 'register']);
    Route::post('/login', [MobileAuthController::class, 'login']);

    // Public catalog
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/category/{name}', [ProductController::class, 'category']);

    // Protected customer routes
    Route::middleware('auth:sanctum')->group(function () {
        // Session
        Route::get('/me', [MobileAuthController::class, 'me']);
        Route::put('/profile', [MobileAuthController::class, 'updateProfile']);
        Route::put('/password', [MobileAuthController::class, 'updatePassword']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);

        // Addresses
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
        Route::post('/addresses/{address}/default', [AddressController::class, 'makeDefault']);

        // Cart
        Route::get('/cart', [CartController::class, 'view']);
        Route::post('/cart/add/{product}', [CartController::class, 'add']);
        Route::post('/cart/update/{product}', [CartController::class, 'update']);
        Route::delete('/cart/remove/{product}', [CartController::class, 'remove']);
        Route::post('/cart/update-item/{item}', [CartController::class, 'updateItem']);
        Route::delete('/cart/remove-item/{item}', [CartController::class, 'removeItem']);

        // Checkout & Orders
        Route::post('/checkout/intent', [CheckoutController::class, 'create']);
        Route::post('/checkout/confirm', [CheckoutController::class, 'confirm']);
        Route::get('/orders', [CheckoutController::class, 'index']);
        Route::get('/orders/{order}', [CheckoutController::class, 'show']);
    });
});

