<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ItemCategory;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AppOwnerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ItemSearchController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DeliveryBoyAuthController;
use App\Http\Controllers\DeliveryBoyProfileController;
use App\Http\Controllers\DeliveryBoyDocumentController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


    Route::post('user/address/add', [UserAddressController::class, 'addAddress']);
    Route::put('user/address/update/{id}', [UserAddressController::class, 'updateAddress']);
    Route::delete('user/address/delete/{id}', [UserAddressController::class, 'deleteAddress']);
    Route::get('user/address/list/{user_id}', [UserAddressController::class, 'listAddresses']);
    Route::post('user/address/set-default/{id}', [UserAddressController::class, 'setDefaultAddress']);


        // Delivery Boys
Route::post('deliveryboy/auto-assign', [DeliveryController::class, 'autoAssign']);
Route::post('deliveryboy/assign', [DeliveryController::class, 'manualAssign']);
Route::post('deliveryboy/accept', [DeliveryController::class, 'accept']);
Route::post('deliveryboy/reject', [DeliveryController::class, 'reject']);
Route::post('deliveryboy/picked', [DeliveryController::class, 'picked']);
Route::post('deliveryboy/delivered', [DeliveryController::class, 'delivered']);

Route::prefix('delivery')->group(function () {

    Route::post('register', [DeliveryBoyAuthController::class, 'register']);
    Route::post('login', [DeliveryBoyAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [DeliveryBoyAuthController::class, 'logout']);
        Route::get('me', [DeliveryBoyProfileController::class, 'me']);
        Route::put('me', [DeliveryBoyProfileController::class, 'update']);
        Route::post('location', [DeliveryBoyProfileController::class, 'updateLocation']);
        Route::post('documents', [DeliveryBoyDocumentController::class, 'upload']);
        Route::get('documents', [DeliveryBoyDocumentController::class, 'list']);
    });
});



// TIMELINE
Route::get('order/timeline/{order_id}', [DeliveryController::class, 'timeline']);

Route::post('/posts', [PostController::class, 'store']);
Route::get('/posts', [PostController::class, 'index']);

Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/update-profile', [AuthController::class, 'updateProfile']);


//vendor api

Route::post('/ownersignup', [AppOwnerController::class, 'store']);
Route::post('/ownerupdate/{id}', [AppOwnerController::class, 'update']);

Route::post('/shop/upload-image', [AppOwnerController::class, 'uploadShopImage']);
Route::post('/shop/delete-image/{id}', [AppOwnerController::class, 'deleteShopImage']);
Route::get('/shop/images/{shop_id}', [AppOwnerController::class, 'listShopImages']);
Route::post('/shop-status/{id}', [AppOwnerController::class, 'toggleStatus']);
Route::get('/shop-details/{id}', [AppOwnerController::class, 'getShopDetails']);
Route::get('/nearby-shops', [AppOwnerController::class, 'nearbyShops']);
//category

Route::get('/get-categories', [ItemCategoryController::class, 'index']);


//items
Route::post('/add-item', [ItemController::class, 'store']);
Route::post('/update-item/{id}', [ItemController::class, 'update']);
Route::post('/delete-item/{id}', [ItemController::class, 'destroy']);
Route::get('/all-items/{id}', [ItemController::class, 'listByOwner']);
Route::post('/item-status/{id}', [ItemController::class, 'toggleStatus']);

//orders
Route::post('/orders/create', [OrderController::class, 'createOrder']);
Route::put('/orders/update/{orderId}', [OrderController::class, 'updateOrder']);
Route::post('/orders/cancel/{orderId}', [OrderController::class, 'cancelOrder']);
Route::get('/orders', [OrderController::class, 'listUserOrders']);

Route::prefix('cart')->group(function () {

    Route::get('/list/{user_id}/{owner_id}', [CartController::class, 'listCart']);
    Route::post('/add', [CartController::class, 'addToCart']);
    Route::put('/update/{id}', [CartController::class, 'updateCart']);
    Route::delete('/remove/{id}', [CartController::class, 'removeFromCart']);
    Route::post('/clear', [CartController::class, 'clearCart']);
});

Route::get('/items/search-nearby', [ItemSearchController::class, 'searchNearbyItems']);

Route::prefix('banners')->group(function () {

    Route::post('/add', [BannerController::class, 'addBanner']);
    Route::put('/update/{id}', [BannerController::class, 'updateBanner']);

    Route::post('/activate/{id}', [BannerController::class, 'activateBanner']);
    Route::post('/deactivate/{id}', [BannerController::class, 'deactivateBanner']);

    Route::get('/active', [BannerController::class, 'listActiveBanners']);
    Route::get('/all', [BannerController::class, 'listAllBanners']);


});