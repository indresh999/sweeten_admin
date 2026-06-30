<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DeliveryBoyAuthController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemSubcategoryController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CommissionController;

// ============================================================
// PUBLIC: HOME / DISCOVERY
// ============================================================
Route::get('/home', [HomeController::class, 'homeData']);
Route::get('/nearby-shops', [ShopController::class, 'nearbyShops']);
Route::get('/shop/{id}', [ShopController::class, 'getShopDetails']);
Route::get('/shop/{id}/schedule', [ShopController::class, 'getSchedule']);
Route::get('/shop/{shopId}/reviews', [UserController::class, 'getShopReviews']);
Route::get('/shop/{id}/orders', [ShopController::class, 'shopOrders']);

// ============================================================
// PUBLIC: SEARCH
// ============================================================
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/suggestions', [SearchController::class, 'suggestions']);

// ============================================================
// PUBLIC: CATEGORIES & ITEMS
// ============================================================
Route::get('/categories', [ItemCategoryController::class, 'index']);
Route::get('/subcategories', [ItemSubcategoryController::class, 'index']);
Route::get('/home-filters', [ItemController::class, 'getAppHomeFilter']);
Route::get('/items/by-subcategory', [ItemController::class, 'itemsBySubcategory']);
Route::get('/items/similar', [ItemController::class, 'similarItems']);
Route::get('/items/by-shop/{id}', [ItemController::class, 'listByOwner']);
Route::get('/items/{id}', [ItemController::class, 'show']);

// ============================================================
// PUBLIC: BANNERS & DEALS
// ============================================================
Route::get('/banners', [BannerController::class, 'activeBanners']);
Route::post('/banners/{id}/click', [BannerController::class, 'trackClick']);
Route::get('/deals', [DealController::class, 'activeDeals']);
Route::get('/deals/{id}', [DealController::class, 'getDeal']);

// ============================================================
// PUBLIC: CANCEL REASONS
// ============================================================
Route::get('/cancel-reasons', [OrderController::class, 'getCancelReasons']);

// ============================================================
// USER AUTH
// ============================================================
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});

// ============================================================
// USER: ADDRESSES
// ============================================================
Route::prefix('addresses')->group(function () {
    Route::get('/{user_id}', [UserController::class, 'listAddresses']);
    Route::post('/', [UserController::class, 'addAddress']);
    Route::put('/{id}', [UserController::class, 'updateAddress']);
    Route::delete('/{id}', [UserController::class, 'deleteAddress']);
    Route::post('/{id}/set-default', [UserController::class, 'setDefaultAddress']);
});

// ============================================================
// USER: WISHLIST
// ============================================================
Route::prefix('wishlist')->group(function () {
    Route::get('/{user_id}', [UserController::class, 'getWishlist']);
    Route::post('/', [UserController::class, 'addToWishlist']);
    Route::delete('/remove', [UserController::class, 'removeFromWishlist']);
});

// ============================================================
// USER: NOTIFICATIONS
// ============================================================
Route::prefix('notifications')->group(function () {
    Route::get('/', [UserController::class, 'getNotifications']);
    Route::post('/read-all', [UserController::class, 'markAllNotificationsRead']);
    Route::post('/{id}/read', [UserController::class, 'markNotificationRead']);
});

// ============================================================
// CART
// ============================================================
Route::prefix('cart')->group(function () {
    Route::get('/{user_id}', [CartController::class, 'listCart']);
    Route::post('/add', [CartController::class, 'addToCart']);
    Route::put('/{id}', [CartController::class, 'updateCart']);
    Route::delete('/{id}', [CartController::class, 'removeFromCart']);
    Route::post('/clear', [CartController::class, 'clearCart']);
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon']);
    Route::get('/coupons/available', [CartController::class, 'availableCoupons']);
});

// ============================================================
// ORDERS
// ============================================================
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'listUserOrders']);
    Route::post('/', [OrderController::class, 'createOrder']);
    Route::get('/{id}', [OrderController::class, 'getOrderById']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/{id}/rate', [OrderController::class, 'rateOrder']);
    Route::get('/{id}/timeline', [OrderController::class, 'getOrderTimeline']);
    Route::get('/{id}/tracking', [DeliveryController::class, 'orderTracking']);
    Route::get('/{id}/status', [DeliveryController::class, 'getOrderStatus']);
    Route::post('/{id}/reorder', [OrderController::class, 'reorder']);
});

// ============================================================
// WALLET
// ============================================================
Route::prefix('wallet')->group(function () {
    Route::get('/balance', [WalletController::class, 'getBalance']);
    Route::get('/transactions', [WalletController::class, 'getTransactions']);
});

// ============================================================
// SEARCH HISTORY
// ============================================================
Route::delete('/search-history', [SearchController::class, 'clearSearchHistory']);

// ============================================================
// COUPONS (Admin)
// ============================================================
Route::prefix('admin/coupons')->group(function () {
    Route::get('/', [CouponController::class, 'adminIndex']);
    Route::post('/', [CouponController::class, 'adminCreate']);
    Route::put('/{id}', [CouponController::class, 'adminUpdate']);
    Route::delete('/{id}', [CouponController::class, 'adminDelete']);
});
Route::post('/coupons/validate', [CouponController::class, 'validate']);

// ============================================================
// DEALS (Admin)
// ============================================================
Route::prefix('admin/deals')->group(function () {
    Route::post('/', [DealController::class, 'adminCreate']);
    Route::put('/{id}', [DealController::class, 'adminUpdate']);
    Route::delete('/{id}', [DealController::class, 'adminDelete']);
});

// ============================================================
// BANNERS (Admin)
// ============================================================
Route::prefix('admin/banners')->group(function () {
    Route::get('/', [BannerController::class, 'activeBanners']);
    Route::post('/', [BannerController::class, 'adminCreate']);
    Route::put('/{id}', [BannerController::class, 'adminUpdate']);
    Route::delete('/{id}', [BannerController::class, 'adminDelete']);
});

// ============================================================
// SHOP OWNER
// ============================================================
Route::prefix('shop')->group(function () {
    Route::post('/register', [ShopController::class, 'register']);
    Route::post('/login', [ShopController::class, 'login']);
    Route::put('/{id}', [ShopController::class, 'update']);
    Route::post('/{id}/toggle-status', [ShopController::class, 'toggleStatus']);
    Route::get('/{id}/dashboard', [ShopController::class, 'dashboard']);
    Route::put('/{id}/schedule', [ShopController::class, 'updateSchedule']);

    Route::post('/images/upload', [ShopController::class, 'uploadImage']);
    Route::delete('/images/{id}', [ShopController::class, 'deleteImage']);
    Route::get('/{shop_id}/images', [ShopController::class, 'listImages']);
});

// ============================================================
// SHOP ITEMS (Owner)
// ============================================================
Route::prefix('items')->group(function () {
    Route::post('/', [ItemController::class, 'store']);
    Route::put('/{id}', [ItemController::class, 'update']);
    Route::delete('/{id}', [ItemController::class, 'destroy']);
    Route::post('/{id}/toggle-status', [ItemController::class, 'toggleStatus']);
});

// ============================================================
// DELIVERY BOY
// ============================================================
Route::prefix('delivery')->group(function () {
    Route::post('/register', [DeliveryBoyAuthController::class, 'register']);
    Route::post('/login', [DeliveryBoyAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [DeliveryBoyAuthController::class, 'logout']);
        Route::get('/me', [DeliveryBoyAuthController::class, 'me']);
        Route::put('/me', [DeliveryBoyAuthController::class, 'update']);
        Route::post('/location', [DeliveryBoyAuthController::class, 'updateLocation']);
        Route::post('/availability', [DeliveryBoyAuthController::class, 'toggleAvailability']);
        Route::post('/documents', [DeliveryBoyAuthController::class, 'uploadDocument']);
        Route::get('/documents', [DeliveryBoyAuthController::class, 'listDocuments']);
        Route::get('/earnings', [DeliveryController::class, 'deliveryBoyEarnings']);
        Route::get('/orders', [DeliveryController::class, 'deliveryBoyOrders']);
    });
});

// ============================================================
// DELIVERY MANAGEMENT (Admin / Shop)
// ============================================================
Route::prefix('delivery-ops')->group(function () {
    Route::post('/auto-assign', [DeliveryController::class, 'autoAssign']);
    Route::post('/manual-assign', [DeliveryController::class, 'manualAssign']);
    Route::post('/accept', [DeliveryController::class, 'accept']);
    Route::post('/reject', [DeliveryController::class, 'reject']);
    Route::post('/picked', [DeliveryController::class, 'picked']);
    Route::post('/delivered', [DeliveryController::class, 'delivered']);
    Route::get('/timeline/{orderId}', [DeliveryController::class, 'timeline']);
});

// ============================================================
// COMMISSIONS
// ============================================================
Route::post('/commission/calculate', [CommissionController::class, 'getCommission']);
