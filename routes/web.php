<?php

use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\AdvancedFeaturesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DebtController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/api/auth/session-ping', function () {
        return response()->json(['status' => 'active']);
    })->name('auth.session.ping');

    // Live Notifications API بۆ ئەدمین و خاوەنکار
    Route::get('/api/admin/notifications/poll', [NotificationController::class, 'poll'])->name('admin.notifications.poll');
    Route::post('/api/admin/notifications/mark-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark_read');

    // بەڕێوەبردنی شیفتی سندوق
    Route::get('/api/shift/current', [ShiftController::class, 'getCurrentShift'])->name('shift.current');
    Route::post('/api/shift/close', [ShiftController::class, 'closeShift'])->name('shift.close');

    // شاشەی سندوق
    Route::middleware([RoleOrPermissionMiddleware::class . ':pos.access'])->group(function () {
        Route::get('/pos', function () {
            return view('pos.index');
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

    // ڕێکخستنەکان و بەڕێوەبردنی دەسەڵاتەکان
    Route::middleware([RoleOrPermissionMiddleware::class . ':settings.manage'])->group(function () {
        Route::get('/settings', [AdvancedFeaturesController::class, 'settings'])->name('admin.settings.index');
        Route::post('/settings', [AdvancedFeaturesController::class, 'updateSettings'])->name('admin.settings.update');
        Route::get('/reports/z-report/{shiftId}', [AdvancedFeaturesController::class, 'zReport'])->name('admin.reports.z_report');
        
        Route::get('/access-control', [AccessControlController::class, 'index'])->name('admin.access.index');
        Route::get('/access-control/users', fn() => redirect()->route('admin.access.index'));
        Route::get('/access-control/roles', fn() => redirect()->route('admin.access.index'));
        Route::post('/access-control/roles', [AccessControlController::class, 'storeRole'])->name('admin.access.role.store');
        Route::post('/access-control/roles/{id}', [AccessControlController::class, 'updateRole'])->name('admin.access.role.update');
        Route::post('/access-control/users', [AccessControlController::class, 'storeUser'])->name('admin.access.user.store');
        Route::post('/access-control/users/{id}', [AccessControlController::class, 'updateUser'])->name('admin.access.user.update');
    });
});
