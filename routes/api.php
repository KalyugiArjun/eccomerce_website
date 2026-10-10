<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
*/

// Public Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{slug}', [ProductApiController::class, 'show']);
Route::get('/categories', [ProductApiController::class, 'categories']);
Route::get('/sellers', [ProductApiController::class, 'sellers']);

// Protected Endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Cart
    Route::get('/cart', [CartApiController::class, 'getCart']);
    Route::post('/cart/add', [CartApiController::class, 'addItem']);
    Route::delete('/cart/item/{itemId}', [CartApiController::class, 'removeItem']);

    // Orders & Wishlist
    Route::get('/orders', [OrderApiController::class, 'getOrders']);
    Route::get('/orders/{orderNumber}', [OrderApiController::class, 'getOrder']);
    Route::get('/wishlist', [OrderApiController::class, 'getWishlist']);
    Route::get('/notifications', [OrderApiController::class, 'getNotifications']);
});
