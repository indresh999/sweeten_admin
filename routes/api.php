<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\DeliveryBoyAuthController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DeliveryWalletController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemSubcategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\VendorItemController;
use App\Http\Controllers\Api\VendorComboController;
use App\Http\Controllers\Api\VendorOfferController;
use App\Http\Controllers\Api\VendorOnboardingController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\GooglePlacesController;
use App\Http\Controllers\Api\DeliveryBoyOnboardingController;
use App\Http\Controllers\Api\LocationController;

// ============================================================
// PUBLIC — HOME / DISCOVERY
// ============================================================
Route::get('/home',                [HomeController::class,   'homeData']);
Route::get('/app-settings',        [AppSettingsController::class, 'index']);
Route::get('/splash-config',       [AppSettingsController::class, 'splashConfig']);
Route::get('/payment-qr',          [AppSettingsController::class, 'paymentQr']);
Route::get('/splash-media',        [AppSettingsController::class, 'splashMedia']);
Route::get('/policies',            [AppSettingsController::class, 'policies']);

// ============================================================
// PUBLIC — GOOGLE PLACES (city search)
// ============================================================
Route::get('/places/autocomplete', [GooglePlacesController::class, 'autocomplete']);
Route::get('/places/details',      [GooglePlacesController::class, 'placeDetails']);
Route::get('/shops/featured',      [ShopController::class,   'featured']);
Route::get('/shops/popular',       [ShopController::class,   'popular']);
Route::get('/nearby-shops',        [ShopController::class,   'nearbyShops']);
Route::get('/shop/{id}',           [ShopController::class,   'getShopDetails'])->where('id', '[0-9]+');
Route::get('/shop/{id}/menu',      [ShopController::class,   'shopMenu'])->where('id', '[0-9]+');
Route::get('/shop/{id}/schedule',  [ShopController::class,   'getSchedule'])->where('id', '[0-9]+');
Route::get('/shop/{shopId}/reviews', [UserController::class, 'getShopReviews']);

// ============================================================
// PUBLIC — LOCATION (proxied through backend, key protected)
// ============================================================
Route::prefix('location')->group(function () {
    Route::get('/autocomplete', [LocationController::class, 'autocomplete']);
    Route::get('/details',      [LocationController::class, 'details']);
    Route::get('/reverse',      [LocationController::class, 'reverse']);
});

// ============================================================
// PUBLIC — SEARCH
// ============================================================
Route::get('/search',             [SearchController::class, 'search']);
Route::get('/search/suggestions', [SearchController::class, 'suggestions']);

// ============================================================
// PUBLIC — CATEGORIES & ITEMS
// ============================================================
Route::get('/categories',            [ItemCategoryController::class,    'index']);
Route::get('/subcategories',         [ItemSubcategoryController::class, 'index']);
Route::get('/home-filters',          [ItemController::class, 'getAppHomeFilter']);
Route::get('/items/by-category',      [ItemController::class, 'itemsByCategory']);
Route::get('/items/by-subcategory',  [ItemController::class, 'itemsBySubcategory']);
Route::get('/items/similar',         [ItemController::class, 'similarItems']);
Route::get('/items/by-shop/{id}',    [ItemController::class, 'listByOwner']);
Route::get('/items/{id}',            [ItemController::class, 'show']);

// ============================================================
// PUBLIC — BANNERS & DEALS
// ============================================================
Route::get('/banners',            [BannerController::class, 'activeBanners']);
Route::post('/banners/{id}/click',[BannerController::class, 'trackClick']);
Route::get('/deals',              [DealController::class,   'activeDeals']);
Route::get('/deals/{id}',         [DealController::class,   'getDeal']);

// ============================================================
// PUBLIC — MISC
// ============================================================
Route::get('/cancel-reasons',     [OrderController::class,  'getCancelReasons']);
Route::post('/coupons/validate',  [CouponController::class, 'validateCoupon']);

// ============================================================
// USER AUTH (public — no token needed)
// ============================================================
Route::prefix('auth')->group(function () {
    Route::post('/send-otp',    [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/verify-otp',  [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
});

// ============================================================
// USER — PROTECTED (token required via api_token header/bearer)
// ============================================================
Route::middleware('auth.token')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout',         [AuthController::class, 'logout']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::get('/profile',         [AuthController::class, 'getProfile']);
        Route::delete('/delete-account',[AuthController::class,'deleteAccount']);
    });

    // Addresses
    Route::prefix('addresses')->group(function () {
        Route::get('/{user_id}',         [UserController::class, 'listAddresses']);
        Route::post('/',                 [UserController::class, 'addAddress']);
        Route::put('/{id}',              [UserController::class, 'updateAddress']);
        Route::delete('/{id}',           [UserController::class, 'deleteAddress']);
        Route::post('/{id}/set-default', [UserController::class, 'setDefaultAddress']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/{user_id}',  [UserController::class, 'getWishlist']);
        Route::post('/',          [UserController::class, 'addToWishlist']);
        Route::delete('/remove',  [UserController::class, 'removeFromWishlist']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/',           [UserController::class, 'getNotifications']);
        Route::post('/read-all',  [UserController::class, 'markAllNotificationsRead']);
        Route::post('/{id}/read', [UserController::class, 'markNotificationRead']);
    });

    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/{user_id}',          [CartController::class, 'listCart']);
        Route::post('/add',               [CartController::class, 'addToCart']);
        Route::put('/{id}',               [CartController::class, 'updateCart']);
        Route::delete('/{id}',            [CartController::class, 'removeFromCart']);
        Route::post('/clear',             [CartController::class, 'clearCart']);
        Route::post('/apply-coupon',      [CartController::class, 'applyCoupon']);
        Route::get('/coupons/available',  [CartController::class, 'availableCoupons']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/',               [OrderController::class,   'listUserOrders']);
        Route::post('/',              [OrderController::class,   'createOrder']);
        Route::get('/{id}',           [OrderController::class,   'getOrderById']);
        Route::post('/{id}/cancel',   [OrderController::class,   'cancelOrder']);
        Route::post('/{id}/rate',     [OrderController::class,   'rateOrder']);
        Route::get('/{id}/timeline',  [OrderController::class,   'getOrderTimeline']);
        Route::get('/{id}/tracking',  [DeliveryController::class,'orderTracking']);
        Route::get('/{id}/status',    [DeliveryController::class,'getOrderStatus']);
        Route::post('/{id}/reorder',  [OrderController::class,   'reorder']);
    });

    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/balance',      [WalletController::class, 'getBalance']);
        Route::get('/transactions', [WalletController::class, 'getTransactions']);
    });

    // Search history
    Route::delete('/search-history', [SearchController::class, 'clearSearchHistory']);
});

// ============================================================
// SHOP OWNER AUTH (public)
// ============================================================
Route::prefix('shop')->group(function () {
    Route::post('/register', [ShopController::class, 'register']);
    Route::post('/login',    [ShopController::class, 'login']);

    // OTP login (existing vendor)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/send-otp',   [ShopController::class, 'sendOtp']);
        Route::post('/auth/verify-otp', [ShopController::class, 'verifyOtp']);
    });

    // ── NEW VENDOR SIGNUP (multi-step onboarding) ──────────────────────────
    // Step 0a + 0b: Email OTP — rate-limited 5/min
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/signup/send-otp',   [VendorOnboardingController::class, 'sendSignupOtp']);
        Route::post('/signup/verify-otp', [VendorOnboardingController::class, 'verifySignupOtp']);
    });
});

// ============================================================
// VENDOR ONBOARDING — protected (draft/pending shops)
// auth.onboarding allows draft + pending, blocks blocked/rejected
// ============================================================
Route::middleware('auth.onboarding')->prefix('shop/onboarding')->group(function () {
    Route::get('/status',         [VendorOnboardingController::class, 'getStatus']);
    Route::put('/details',        [VendorOnboardingController::class, 'updateDetails']);
    Route::put('/payment',        [VendorOnboardingController::class, 'updatePayment']);
    Route::post('/photos',        [VendorOnboardingController::class, 'uploadPhotos']);
    Route::get('/photos',         [VendorOnboardingController::class, 'listPhotos']);
    Route::delete('/photos/{id}', [VendorOnboardingController::class, 'deletePhoto']);
    Route::post('/photos/reorder',[VendorOnboardingController::class, 'reorderPhotos']);
    Route::post('/documents',        [VendorOnboardingController::class, 'uploadDocument']);
    Route::get('/documents',         [VendorOnboardingController::class, 'listDocuments']);
    Route::delete('/documents/{id}', [VendorOnboardingController::class, 'deleteDocument']);
    Route::post('/submit',        [VendorOnboardingController::class, 'submitForReview']);
});

// ============================================================
// SHOP OWNER — PROTECTED (requires Bearer shop token)
// ============================================================
Route::middleware('auth.shop')->prefix('shop')->group(function () {
    // Auth management
    Route::post('/logout',          [ShopController::class, 'logout']);
    Route::post('/change-password', [ShopController::class, 'changePassword']);

    // Profile — identity comes from the auth token, no ID in URL
    Route::get('/me',  [ShopController::class, 'me']);
    Route::put('/me',  [ShopController::class, 'update']);

    // Operations — scoped to the authenticated shop automatically
    Route::get('/orders',         [ShopController::class, 'shopOrders']);
    Route::post('/orders/{id}/update-status', [ShopController::class, 'updateOrderStatus']);
    Route::get('/orders/stats',   [ShopController::class, 'orderStats']);
    Route::get('/dashboard',      [ShopController::class, 'dashboard']);
    Route::post('/toggle-status', [ShopController::class, 'toggleStatus']);
    Route::put('/schedule',       [ShopController::class, 'updateSchedule']);
    Route::get('/schedule',       [ShopController::class, 'getSchedule']);

    // Images — shop_id inferred from token
    Route::post('/images',        [ShopController::class, 'uploadImage']);
    Route::delete('/images/{id}', [ShopController::class, 'deleteImage']);
    Route::get('/images',         [ShopController::class, 'listImages']);

    // ── Products (regular items) ─────────────────────────────────────────────
    Route::prefix('products')->group(function () {
        Route::get('/',                              [VendorItemController::class, 'index']);
        Route::post('/',                             [VendorItemController::class, 'store']);
        Route::post('/bulk',                         [VendorItemController::class, 'bulkAction']);
        Route::get('/units',                         [VendorItemController::class, 'units']);
        Route::get('/categories',                    [VendorItemController::class, 'categoryTree']);
        Route::get('/categories/{categoryId}/subcategories', [VendorItemController::class, 'subcategoriesByCategory']);
        Route::get('/{id}',                          [VendorItemController::class, 'show']);
        Route::put('/{id}',                          [VendorItemController::class, 'update']);
        Route::delete('/{id}',                       [VendorItemController::class, 'destroy']);
        Route::post('/{id}/toggle-status',           [VendorItemController::class, 'toggleStatus']);

        // Media per product
        Route::get('/{id}/media',                    [VendorItemController::class, 'listMedia']);
        Route::post('/{id}/media',                   [VendorItemController::class, 'uploadMedia']);
        Route::post('/{id}/media/reorder',           [VendorItemController::class, 'reorderMedia']);
        Route::post('/media/{mediaId}/set-thumbnail',[VendorItemController::class, 'setThumbnail']);
        Route::delete('/media/{mediaId}',            [VendorItemController::class, 'deleteMedia']);
    });

    // ── Combos ───────────────────────────────────────────────────────────────
    Route::prefix('combos')->group(function () {
        Route::get('/',                    [VendorComboController::class, 'index']);
        Route::post('/',                   [VendorComboController::class, 'store']);
        Route::get('/{id}',                [VendorComboController::class, 'show']);
        Route::put('/{id}',                [VendorComboController::class, 'update']);
        Route::delete('/{id}',             [VendorComboController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [VendorComboController::class, 'toggleStatus']);
    });

    // ── Offers ───────────────────────────────────────────────────────────────
    Route::prefix('offers')->group(function () {
        Route::get('/',          [VendorOfferController::class, 'index']);
        Route::get('/active',    [VendorOfferController::class, 'active']);
        Route::post('/',         [VendorOfferController::class, 'store']);
        Route::get('/{id}',      [VendorOfferController::class, 'show']);
        Route::put('/{id}',      [VendorOfferController::class, 'update']);
        Route::delete('/{id}',   [VendorOfferController::class, 'destroy']);
        Route::post('/{id}/toggle', [VendorOfferController::class, 'toggle']);
    });
});

// ============================================================
// DELIVERY BOY
// ============================================================
Route::prefix('delivery')->group(function () {
    // Email OTP auth (rate-limited)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/send-otp',   [DeliveryBoyAuthController::class, 'sendOtp']);
        Route::post('/auth/verify-otp', [DeliveryBoyAuthController::class, 'verifyOtp']);
    });

    // Legacy password-based auth
    Route::post('/register', [DeliveryBoyAuthController::class, 'register']);
    Route::post('/login',    [DeliveryBoyAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',          [DeliveryBoyAuthController::class, 'logout']);
        Route::get('/me',               [DeliveryBoyAuthController::class, 'me']);
        Route::put('/me',               [DeliveryBoyAuthController::class, 'update']);
        Route::post('/photo',           [DeliveryBoyAuthController::class, 'uploadPhoto']);
        Route::post('/location',        [DeliveryBoyAuthController::class, 'updateLocation']);
        Route::post('/availability',    [DeliveryBoyAuthController::class, 'toggleAvailability']);
        Route::post('/documents',       [DeliveryBoyAuthController::class, 'uploadDocument']);
        Route::get('/documents',        [DeliveryBoyAuthController::class, 'listDocuments']);
        Route::get('/document-status',  [DeliveryBoyAuthController::class, 'documentStatus']);
        Route::get('/earnings',         [DeliveryController::class, 'deliveryBoyEarnings']);
        Route::get('/orders',           [DeliveryController::class, 'deliveryBoyOrders']);

        // Onboarding flow
        Route::prefix('onboarding')->group(function () {
            Route::get('/status',       [DeliveryBoyOnboardingController::class, 'getStatus']);
            Route::put('/details',      [DeliveryBoyOnboardingController::class, 'updateDetails']);
            Route::post('/photo',       [DeliveryBoyOnboardingController::class, 'uploadPhoto']);
            Route::post('/document',    [DeliveryBoyOnboardingController::class, 'uploadDocument']);
            Route::post('/submit',      [DeliveryBoyOnboardingController::class, 'submitForReview']);
        });

        // Wallet & Cash Submission
        Route::get('/wallet',            [DeliveryWalletController::class, 'walletInfo']);
        Route::post('/submit-cash',      [DeliveryWalletController::class, 'submitCash']);
    });
});

// ============================================================
// DELIVERY OPS (delivery boy — auth:sanctum, guard: delivery)
// ============================================================
Route::prefix('delivery-ops')->middleware('auth:sanctum')->group(function () {
    Route::post('/accept',            [DeliveryController::class, 'accept']);
    Route::post('/reject',            [DeliveryController::class, 'reject']);
    Route::post('/picked',            [DeliveryController::class, 'picked']);
    Route::post('/delivered',         [DeliveryController::class, 'delivered']);
    Route::get('/timeline/{orderId}', [DeliveryController::class, 'timeline']);
    Route::get('/pending',            [DeliveryController::class, 'pendingAssignment']);
    Route::get('/reject-reasons',     [DeliveryController::class, 'getRejectReasons']);
});

// Admin-only delivery ops (protected by admin middleware)
Route::prefix('delivery-ops')->middleware('auth.shop')->group(function () {
    Route::post('/auto-assign',   [DeliveryController::class, 'autoAssign']);
    Route::post('/manual-assign', [DeliveryController::class, 'manualAssign']);
});

// ============================================================
// ADMIN — DELIVERY WALLET MANAGEMENT
// ============================================================
Route::prefix('admin/delivery-wallet')->middleware('auth.shop')->group(function () {
    Route::get('/submissions',                [DeliveryWalletController::class, 'adminListSubmissions']);
    Route::post('/submissions/{id}/verify',   [DeliveryWalletController::class, 'adminVerifySubmission']);
    Route::get('/boys',                       [DeliveryWalletController::class, 'adminListBoys']);
    Route::put('/boys/{boyId}/wallet-limit',  [DeliveryWalletController::class, 'adminSetWalletLimit']);
});

// ============================================================
// ADMIN — COUPONS / DEALS / BANNERS
// ============================================================
Route::prefix('admin')->group(function () {
    Route::prefix('coupons')->group(function () {
        Route::get('/',      [CouponController::class, 'adminIndex']);
        Route::post('/',     [CouponController::class, 'adminCreate']);
        Route::put('/{id}',  [CouponController::class, 'adminUpdate']);
        Route::delete('/{id}',[CouponController::class,'adminDelete']);
    });
    Route::prefix('deals')->group(function () {
        Route::post('/',     [DealController::class, 'adminCreate']);
        Route::put('/{id}',  [DealController::class, 'adminUpdate']);
        Route::delete('/{id}',[DealController::class,'adminDelete']);
    });
    Route::prefix('banners')->group(function () {
        Route::get('/',      [BannerController::class, 'activeBanners']);
        Route::post('/',     [BannerController::class, 'adminCreate']);
        Route::put('/{id}',  [BannerController::class, 'adminUpdate']);
        Route::delete('/{id}',[BannerController::class,'adminDelete']);
    });
});

Route::post('/commission/calculate', [CommissionController::class, 'getCommission']);
