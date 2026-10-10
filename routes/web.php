<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FastDeliveryController;
use App\Http\Controllers\GroupBuyingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NearbySellerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Seller;
use App\Http\Controllers\VisualSearchController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes for ShopKart Indian Marketplace
|--------------------------------------------------------------------------
*/

// Public Customer Marketplace Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/set-location', [HomeController::class, 'setLocation'])->name('set-location');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/alert', [ProductController::class, 'setPriceAlert'])->name('products.price-alert');

Route::get('/nearby-sellers', [NearbySellerController::class, 'index'])->name('nearby.index');
Route::get('/fast-delivery', [FastDeliveryController::class, 'index'])->name('fast-delivery.index');

Route::get('/group-buying', [GroupBuyingController::class, 'index'])->name('group-buying.index');
Route::get('/group-buying/{groupCode}', [GroupBuyingController::class, 'show'])->name('group-buying.show');
Route::post('/group-buying/create/{product}', [GroupBuyingController::class, 'create'])->name('group-buying.create');
Route::post('/group-buying/join/{groupCode}', [GroupBuyingController::class, 'join'])->name('group-buying.join');

Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai.index');
Route::post('/ai-assistant/query', [AiAssistantController::class, 'query'])->name('ai.query');

Route::get('/visual-search', [VisualSearchController::class, 'index'])->name('visual.index');
Route::post('/visual-search', [VisualSearchController::class, 'search'])->name('visual.search');

Route::get('/store/{slug}', [Seller\StorefrontController::class, 'show'])->name('seller.storefront');
Route::get('/wishlist/shared/{token}', [WishlistController::class, 'shared'])->name('wishlist.shared');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
Route::post('/cart/update/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::post('/cart/remove/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{orderNumber}/refund', [OrderController::class, 'requestRefund'])->name('orders.refund');
    Route::post('/orders/{orderNumber}/review', [OrderController::class, 'storeReview'])->name('orders.review');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart/{item}', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
    Route::post('/wishlist/settings', [WishlistController::class, 'updateSocialDetails'])->name('wishlist.update-settings');

    // Seller Onboarding
    Route::get('/seller/register', [Seller\RegistrationController::class, 'showRegistrationForm'])->name('seller.register');
    Route::post('/seller/register', [Seller\RegistrationController::class, 'register'])->name('seller.register.submit');
    Route::get('/seller/status', [Seller\RegistrationController::class, 'status'])->name('seller.status');
});

// Seller Management Portal (Protected by seller middleware)
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [Seller\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [Seller\DashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [Seller\DashboardController::class, 'updateSettings'])->name('settings.update');

    Route::get('/products', [Seller\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [Seller\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [Seller\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [Seller\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [Seller\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [Seller\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders', [Seller\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{subOrderNumber}', [Seller\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{subOrderNumber}/status', [Seller\OrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('/inventory', [Seller\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{inventory}', [Seller\InventoryController::class, 'update'])->name('inventory.update');

    Route::get('/payouts', [Seller\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/request', [Seller\PayoutController::class, 'requestPayout'])->name('payouts.request');
});

// Admin Management Portal (Protected by admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sellers', [Admin\SellerManagementController::class, 'index'])->name('sellers.index');
    Route::get('/sellers/{seller}', [Admin\SellerManagementController::class, 'show'])->name('sellers.show');
    Route::post('/sellers/{seller}/status', [Admin\SellerManagementController::class, 'updateStatus'])->name('sellers.update-status');

    Route::get('/products', [Admin\ProductManagementController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/featured', [Admin\ProductManagementController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::post('/products/{product}/status', [Admin\ProductManagementController::class, 'updateStatus'])->name('products.update-status');

    Route::get('/orders', [Admin\OrderManagementController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderManagementController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [Admin\OrderManagementController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('/commissions', [Admin\CommissionPayoutController::class, 'index'])->name('commissions.index');
    Route::post('/payouts/{payout}/process', [Admin\CommissionPayoutController::class, 'processPayout'])->name('payouts.process');

    Route::get('/categories', [Admin\CategoryBrandController::class, 'index'])->name('categories.index');
    Route::post('/categories', [Admin\CategoryBrandController::class, 'storeCategory'])->name('categories.store');
    Route::post('/brands', [Admin\CategoryBrandController::class, 'storeBrand'])->name('brands.store');

    Route::get('/refunds', [Admin\RefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refund}', [Admin\RefundController::class, 'update'])->name('refunds.update');
});
