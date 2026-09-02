<?php

use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\AdvancedFeaturesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DebtController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PosOrderController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\SmartPaymentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/api/auth/session-ping', fn() => response()->json(['status' => 'active']))->name('auth.session.ping');
    Route::get('/api/admin/notifications/poll', [NotificationController::class, 'poll'])->name('admin.notifications.poll');
    Route::post('/api/admin/notifications/mark-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark_read');

    // شیفت
    Route::get('/api/shift/current', [ShiftController::class, 'getCurrentShift'])->name('shift.current');
    Route::post('/api/shift/open', [ShiftController::class, 'openShift'])->name('shift.open');
    Route::post('/api/shift/close', [ShiftController::class, 'closeShift'])->name('shift.close');

    // پاشەکەوتکردنی وەسڵ لە سندوق
    Route::post('/api/pos/checkout', [PosOrderController::class, 'store'])->name('pos.checkout');
    Route::get('/api/pos/my-invoices', [PosOrderController::class, 'myInvoices'])->name('pos.my_invoices');

    // پارەدانی دیجیتاڵ
    Route::post('/api/payments/initiate', [SmartPaymentController::class, 'initiate'])->name('payment.initiate');
    Route::post('/api/payments/confirm-manual', [SmartPaymentController::class, 'confirmManual'])->name('payment.confirm_manual');
    Route::get('/api/payments/status/{id}', [SmartPaymentController::class, 'checkApiStatus'])->name('payment.check_status');

    // سندوق
    Route::middleware([RoleOrPermissionMiddleware::class . ':pos.access'])->group(function () {
        Route::get('/pos', function() {
        $settings = \Illuminate\Support\Facades\DB::table('store_settings')->pluck('value', 'key')->toArray();
        return view('pos.index', compact('settings'));
    })->name('pos.index');
    });

    // داشبۆرد
    Route::middleware([RoleOrPermissionMiddleware::class . ':dashboard.view'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });

    // کۆگا
    Route::middleware([RoleOrPermissionMiddleware::class . ':inventory.manage'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::post('/inventory/products', [InventoryController::class, 'storeProduct'])->name('admin.inventory.product.store');
        Route::post('/inventory/purchases', [InventoryController::class, 'storePurchase'])->name('admin.inventory.purchase.store');
        Route::get('/inventory/products/{id}/label', [InventoryController::class, 'printLabel'])->name('admin.inventory.label');
    });

    // قەرز
    Route::middleware([RoleOrPermissionMiddleware::class . ':debts.manage'])->group(function () {
        Route::get('/debts', [DebtController::class, 'index'])->name('admin.debts.index');
        Route::post('/debts/parties', [DebtController::class, 'storeParty'])->name('admin.debts.party.store');
        Route::post('/debts/payments', [DebtController::class, 'recordPayment'])->name('admin.debts.payment.store');
    });

    // خەرجییەکان
    Route::middleware([RoleOrPermissionMiddleware::class . ':expenses.manage'])->group(function () {
        Route::get('/expenses', [AdvancedFeaturesController::class, 'expenses'])->name('admin.expenses.index');
        Route::post('/expenses', [AdvancedFeaturesController::class, 'storeExpense'])->name('admin.expenses.store');
    });

    // ئۆفەرەکان
    Route::middleware([RoleOrPermissionMiddleware::class . ':promotions.manage'])->group(function () {
        Route::get('/promotions', [AdvancedFeaturesController::class, 'promotions'])->name('admin.promotions.index');
        Route::post('/promotions', [AdvancedFeaturesController::class, 'storePromotion'])->name('admin.promotions.store');
    });

    // ڕێکخستنەکان
    Route::middleware([RoleOrPermissionMiddleware::class . ':settings.manage'])->group(function () {
        Route::get('/settings', [AdvancedFeaturesController::class, 'settings'])->name('admin.settings.index');
        Route::post('/settings', [AdvancedFeaturesController::class, 'updateSettings'])->name('admin.settings.update');
        
        
    
        
        
        
        
        
        
    });
});





Route::get('/reports/z-report/{shiftId?}', [App\Http\Controllers\Admin\ReportController::class, 'zReport'])
    ->name('admin.reports.z_report')
    ->middleware(['web', 'auth', 'App\Http\Middleware\RoleOrPermissionMiddleware:reports.view']);

    
/* ACCESS_CONTROL_START */
Route::middleware(['web', 'auth', 'App\Http\Middleware\RoleOrPermissionMiddleware:users.view'])->group(function () {
    Route::get('/access-control', [App\Http\Controllers\Admin\AccessControlController::class, 'index'])->name('admin.access.index');
    Route::post('/access-control/users', [App\Http\Controllers\Admin\AccessControlController::class, 'storeUser'])->name('admin.access.users.store');
    Route::put('/access-control/users/{id}', [App\Http\Controllers\Admin\AccessControlController::class, 'updateUser'])->name('admin.access.users.update');
    Route::delete('/access-control/users/{id}', [App\Http\Controllers\Admin\AccessControlController::class, 'deleteUser'])->name('admin.access.users.delete');
    Route::post('/access-control/users/{id}/toggle', [App\Http\Controllers\Admin\AccessControlController::class, 'toggleUserStatus'])->name('admin.access.users.toggle');
    Route::post('/access-control/roles', [App\Http\Controllers\Admin\AccessControlController::class, 'storeRole'])->name('admin.access.roles.store');
    Route::put('/access-control/roles/{id}', [App\Http\Controllers\Admin\AccessControlController::class, 'updateRole'])->name('admin.access.roles.update');
    Route::delete('/access-control/roles/{id}', [App\Http\Controllers\Admin\AccessControlController::class, 'deleteRole'])->name('admin.access.roles.delete');
});
/* ACCESS_CONTROL_END */
