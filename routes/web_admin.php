<?php
// ============================================================
// SWEETAN ADMIN PANEL — ALL ROUTES
// Add this file: require __DIR__.'/web_admin.php'; in routes/web.php
// ============================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminItemController;
use App\Http\Controllers\Admin\AdminDeliveryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminEarningsController;
use App\Http\Controllers\Admin\AdminTaxController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminMonitorController;
use App\Http\Controllers\Admin\AdminSplashController;
use App\Http\Controllers\Admin\AdminPolicyController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',           [AdminDashboardController::class,'index'])->name('dashboard');
    Route::get('/dashboard/stats',     [AdminDashboardController::class,'stats'])->name('dashboard.stats');
    Route::get('/dashboard/chart',     [AdminDashboardController::class,'chart'])->name('dashboard.chart');

    // Vendors
    Route::get('/vendors',             [AdminVendorController::class,'index'])->name('vendors.index');
    Route::get('/vendors/pending',     [AdminVendorController::class,'pending'])->name('vendors.pending');
    Route::get('/vendors/{id}',        [AdminVendorController::class,'show'])->name('vendors.show');
    Route::put('/vendors/{id}',        [AdminVendorController::class,'update'])->name('vendors.update');
    Route::post('/vendors/{id}/toggle',[AdminVendorController::class,'toggle'])->name('vendors.toggle');
    Route::post('/vendors/{id}/approve',[AdminVendorController::class,'approve'])->name('vendors.approve');
    Route::post('/vendors/{id}/reject',[AdminVendorController::class,'reject'])->name('vendors.reject');
    Route::post('/vendors/{id}/email', [AdminVendorController::class,'sendEmail'])->name('vendors.email');
    Route::get('/vendors/{id}/orders', [AdminVendorController::class,'orders'])->name('vendors.orders');
    Route::get('/vendors/{id}/earnings',[AdminVendorController::class,'earnings'])->name('vendors.earnings');

    // Customers
    Route::get('/customers',           [AdminCustomerController::class,'index'])->name('customers.index');
    Route::get('/customers/{id}',      [AdminCustomerController::class,'show'])->name('customers.show');
    Route::post('/customers/{id}/toggle',[AdminCustomerController::class,'toggle'])->name('customers.toggle');
    Route::post('/customers/{id}/email',[AdminCustomerController::class,'sendEmail'])->name('customers.email');
    Route::get('/customers/{id}/orders',[AdminCustomerController::class,'orders'])->name('customers.orders');
    Route::post('/customers/{id}/wallet',[AdminCustomerController::class,'walletCredit'])->name('customers.wallet');

    // Orders
    Route::get('/orders',              [AdminOrderController::class,'index'])->name('orders.index');
    Route::get('/orders/export',       [AdminOrderController::class,'export'])->name('orders.export');
    Route::get('/orders/{id}',         [AdminOrderController::class,'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [AdminOrderController::class,'updateStatus'])->name('orders.status');
    Route::post('/orders/{id}/assign', [AdminOrderController::class,'assignDelivery'])->name('orders.assign');
    Route::post('/orders/{id}/auto-assign',[AdminOrderController::class,'autoAssign'])->name('orders.auto-assign');
    Route::post('/orders/{id}/cancel', [AdminOrderController::class,'cancel'])->name('orders.cancel');
    Route::get('/orders/{id}/timeline',[AdminOrderController::class,'timeline'])->name('orders.timeline');
    Route::get('/orders/{id}/invoice', [AdminTaxController::class,'invoice'])->name('orders.invoice');

    // Items
    Route::get('/items',               [AdminItemController::class,'index'])->name('items.index');
    Route::get('/items/create',        [AdminItemController::class,'create'])->name('items.create');
    Route::post('/items',              [AdminItemController::class,'store'])->name('items.store');
    Route::get('/items/{id}',          [AdminItemController::class,'show'])->name('items.show');
    Route::get('/items/{id}/edit',     [AdminItemController::class,'edit'])->name('items.edit');
    Route::put('/items/{id}',          [AdminItemController::class,'update'])->name('items.update');
    Route::delete('/items/{id}',       [AdminItemController::class,'destroy'])->name('items.destroy');
    Route::post('/items/{id}/toggle',  [AdminItemController::class,'toggle'])->name('items.toggle');
    Route::get('/subcategories-by-cat/{id}',[AdminItemController::class,'subcategories'])->name('items.subcategories');

    // Delivery Boys
    Route::get('/delivery',            [AdminDeliveryController::class,'index'])->name('delivery.index');
    Route::get('/delivery/docs/pending',[AdminDeliveryController::class,'pendingDocs'])->name('delivery.docs.pending');
    Route::get('/delivery/{id}',       [AdminDeliveryController::class,'show'])->name('delivery.show');
    Route::post('/delivery/{id}/toggle',[AdminDeliveryController::class,'toggle'])->name('delivery.toggle');
    Route::post('/delivery/{id}/activate',[AdminDeliveryController::class,'activate'])->name('delivery.activate');
    Route::post('/delivery/{id}/deactivate',[AdminDeliveryController::class,'deactivate'])->name('delivery.deactivate');
    Route::post('/delivery/{id}/verify',[AdminDeliveryController::class,'verify'])->name('delivery.verify');
    Route::post('/delivery/{id}/email',[AdminDeliveryController::class,'sendEmail'])->name('delivery.email');
    Route::get('/delivery/{id}/orders',[AdminDeliveryController::class,'orders'])->name('delivery.orders');
    Route::get('/delivery/{id}/earnings',[AdminDeliveryController::class,'earnings'])->name('delivery.earnings');
    Route::post('/delivery/docs/{id}/approve',[AdminDeliveryController::class,'approveDoc'])->name('delivery.docs.approve');
    Route::post('/delivery/docs/{id}/reject',[AdminDeliveryController::class,'rejectDoc'])->name('delivery.docs.reject');
    Route::post('/delivery/payouts/mark-paid',[AdminDeliveryController::class,'markPaid'])->name('delivery.payouts.paid');

    // Delivery Wallet Management
    Route::get('/delivery-wallet',                    [AdminDeliveryController::class,'walletIndex'])->name('delivery.wallet.index');
    Route::get('/delivery-wallet/submissions',         [AdminDeliveryController::class,'walletSubmissions'])->name('delivery.wallet.submissions');
    Route::post('/delivery-wallet/submissions/{id}/verify', [AdminDeliveryController::class,'walletVerify'])->name('delivery.wallet.verify');
    Route::put('/delivery-wallet/boys/{boyId}/wallet-limit', [AdminDeliveryController::class,'walletSetLimit'])->name('delivery.wallet.limit');

    // Coupons
    Route::get('/coupons',             [AdminCouponController::class,'index'])->name('coupons.index');
    Route::get('/coupons/create',      [AdminCouponController::class,'create'])->name('coupons.create');
    Route::post('/coupons',            [AdminCouponController::class,'store'])->name('coupons.store');
    Route::get('/coupons/{id}/edit',   [AdminCouponController::class,'edit'])->name('coupons.edit');
    Route::put('/coupons/{id}',        [AdminCouponController::class,'update'])->name('coupons.update');
    Route::delete('/coupons/{id}',     [AdminCouponController::class,'destroy'])->name('coupons.destroy');
    Route::post('/coupons/{id}/toggle',[AdminCouponController::class,'toggle'])->name('coupons.toggle');
    Route::get('/coupons/{id}/usage',  [AdminCouponController::class,'usage'])->name('coupons.usage');

    // Banners
    Route::get('/banners',             [AdminBannerController::class,'index'])->name('banners.index');
    Route::get('/banners/create',      [AdminBannerController::class,'create'])->name('banners.create');
    Route::post('/banners',            [AdminBannerController::class,'store'])->name('banners.store');
    Route::get('/banners/{id}/edit',   [AdminBannerController::class,'edit'])->name('banners.edit');
    Route::put('/banners/{id}',        [AdminBannerController::class,'update'])->name('banners.update');
    Route::delete('/banners/{id}',     [AdminBannerController::class,'destroy'])->name('banners.destroy');

    // Policies
    Route::get('/policies',             [AdminPolicyController::class,'index'])->name('policies.index');
    Route::get('/policies/create',      [AdminPolicyController::class,'create'])->name('policies.create');
    Route::post('/policies',            [AdminPolicyController::class,'store'])->name('policies.store');
    Route::get('/policies/{id}/edit',   [AdminPolicyController::class,'edit'])->name('policies.edit');
    Route::put('/policies/{id}',        [AdminPolicyController::class,'update'])->name('policies.update');
    Route::delete('/policies/{id}',     [AdminPolicyController::class,'destroy'])->name('policies.destroy');

    // Push Notifications
    Route::get('/notifications',       [AdminNotificationController::class,'index'])->name('notifications.index');
    Route::post('/notifications/send', [AdminNotificationController::class,'send'])->name('notifications.send');
    Route::get('/notifications/history',[AdminNotificationController::class,'history'])->name('notifications.history');

    // Earnings
    Route::get('/earnings',            [AdminEarningsController::class,'index'])->name('earnings.index');
    Route::get('/earnings/platform',   [AdminEarningsController::class,'platform'])->name('earnings.platform');
    Route::get('/earnings/vendors',    [AdminEarningsController::class,'vendors'])->name('earnings.vendors');
    Route::get('/earnings/delivery',   [AdminEarningsController::class,'delivery'])->name('earnings.delivery');
    Route::post('/earnings/payout',    [AdminEarningsController::class,'payout'])->name('earnings.payout');
    Route::get('/earnings/export',     [AdminEarningsController::class,'export'])->name('earnings.export');

    // Tax & Billing
    Route::get('/tax',                 [AdminTaxController::class,'index'])->name('tax.index');
    Route::post('/tax',                [AdminTaxController::class,'update'])->name('tax.update');
    Route::get('/billing/{id}/pdf',    [AdminTaxController::class,'invoicePdf'])->name('billing.pdf');

    // Reports
    Route::get('/reports',             [AdminReportsController::class,'index'])->name('reports.index');
    Route::get('/reports/orders',      [AdminReportsController::class,'orders'])->name('reports.orders');
    Route::get('/reports/revenue',     [AdminReportsController::class,'revenue'])->name('reports.revenue');

    // Monitor Panel — analytics & insights
    Route::get('/monitor',                   [AdminMonitorController::class,'index'])->name('monitor.index');
    Route::get('/monitor/top-products',      [AdminMonitorController::class,'topProducts'])->name('monitor.top-products');
    Route::get('/monitor/top-shops',         [AdminMonitorController::class,'topShops'])->name('monitor.top-shops');
    Route::get('/monitor/location',          [AdminMonitorController::class,'locationAnalytics'])->name('monitor.location');
    Route::get('/monitor/chart-data',        [AdminMonitorController::class,'chartData'])->name('monitor.chart-data');

    // Splash Screen
    Route::get('/splash',              [AdminSplashController::class,'index'])->name('splash.index');
    Route::post('/splash',             [AdminSplashController::class,'update'])->name('splash.update');
    Route::post('/splash/media',       [AdminSplashController::class,'uploadMedia'])->name('splash.media');
    Route::delete('/splash/media',     [AdminSplashController::class,'removeMedia'])->name('splash.remove');
    Route::get('/splash/stream',       [AdminSplashController::class,'streamMedia'])->name('splash.stream');

    // Settings
    Route::get('/settings',            [AdminSettingsController::class,'index'])->name('settings.index');
    Route::post('/settings',           [AdminSettingsController::class,'update'])->name('settings.update');
    Route::post('/settings/firebase',  [AdminSettingsController::class,'updateFirebase'])->name('settings.firebase');
    Route::post('/settings/smtp',      [AdminSettingsController::class,'updateSmtp'])->name('settings.smtp');
    Route::post('/settings/gst',       [AdminSettingsController::class,'updateGst'])->name('settings.gst');
    Route::post('/settings/payment-qr',[AdminSettingsController::class,'uploadPaymentQr'])->name('settings.payment-qr');
    Route::delete('/settings/payment-qr',[AdminSettingsController::class,'removePaymentQr'])->name('settings.payment-qr.remove');
    Route::post('/settings/appinfo',   [AdminSettingsController::class,'updateAppInfo'])->name('settings.appinfo');
    Route::post('/settings/maps',      [AdminSettingsController::class,'updateMaps'])->name('settings.maps');
});
