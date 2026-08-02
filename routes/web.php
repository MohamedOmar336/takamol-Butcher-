<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ScaleSimulatorController;
use App\Http\Controllers\SuperAdminController;

// Central Domain Routing (Landing & Super Admin Panel)
$centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

Route::domain($centralDomain)->group(function () {
    Route::get('/', [SuperAdminController::class, 'showLanding'])->name('central.landing');
    Route::post('/redirect-store', [SuperAdminController::class, 'redirectStore'])->name('central.redirect_store');

    Route::prefix('super-admin')->group(function () {
        Route::get('/login', [SuperAdminController::class, 'showLogin'])->name('super_admin.login');
        Route::post('/login', [SuperAdminController::class, 'login']);
        Route::post('/logout', [SuperAdminController::class, 'logout'])->name('super_admin.logout');

        // Central Super Admin authenticated routes
        Route::middleware(['auth', \App\Http\Middleware\CheckSuperAdmin::class])->group(function () {
            Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('super_admin.dashboard');
            Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('super_admin.tenants.store');
            Route::post('/tenants/{tenant}/toggle-status', [SuperAdminController::class, 'toggleTenantStatus'])->name('super_admin.tenants.toggle');
            Route::post('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('super_admin.tenants.update');
            Route::delete('/tenants/{tenant}', [SuperAdminController::class, 'destroyTenant'])->name('super_admin.tenants.destroy');
        });
    });
});

// 1. Language switcher (accessible to everyone)
Route::get('/change-language/{locale}', [AuthController::class, 'changeLanguage'])->name('change_language');

// 2. Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/login/bypass', [AuthController::class, 'loginBypass'])->name('login.bypass');

// 3. Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    // Scale simulator (visible to authenticated users for testing)
    Route::get('/scale-simulator', [ScaleSimulatorController::class, 'index'])->name('scale.simulator');

    // POS Cashier Screen (requires access_pos permission)
    Route::middleware(['permission:access_pos'])->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('pos.index');
        Route::get('/pos/customers', [POSController::class, 'searchCustomer'])->name('pos.customers.search');
        Route::post('/pos/customers', [POSController::class, 'quickAddCustomer'])->name('pos.customers.store');
        Route::post('/pos/scan', [POSController::class, 'scanBarcode'])->name('pos.scan');
        Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{order}', [POSController::class, 'printReceipt'])->name('pos.receipt');
        Route::post('/pos/send-report', [POSController::class, 'sendDailyReport'])->name('pos.send_report');
    });

    // Admin Panel Section
    Route::prefix('admin')->group(function () {
        
        // Dashboard Home & Mail Report (requires view_reports)
        Route::middleware(['permission:view_reports'])->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
            Route::get('/orders', [DashboardController::class, 'ordersIndex'])->name('admin.orders.index');
            Route::post('/orders/{order}/refund', [DashboardController::class, 'refundOrder'])->name('admin.orders.refund');
            Route::get('/print-report', [DashboardController::class, 'printDailyReport'])->name('admin.print_report');
            Route::post('/send-range-report', [DashboardController::class, 'sendRangeReportManual'])->name('admin.send_range_report');
            Route::get('/print-range-report', [DashboardController::class, 'printRangeReport'])->name('admin.print_range_report');
            Route::post('/send-report', [DashboardController::class, 'sendDailyReportManual'])->name('admin.send_report');
        });

        // Users Management (requires manage_users)
        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/users', [DashboardController::class, 'usersIndex'])->name('admin.users');
            Route::post('/users', [DashboardController::class, 'usersStore'])->name('admin.users.store');
            Route::put('/users/{user}', [DashboardController::class, 'usersUpdate'])->name('admin.users.update');
            Route::delete('/users/{user}', [DashboardController::class, 'usersDestroy'])->name('admin.users.destroy');
        });

        // Products Catalog & Excel Importer (requires manage_inventory)
        Route::middleware(['permission:manage_inventory'])->group(function () {
            Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
            Route::get('/products/fix-barcodes', [ProductController::class, 'fixScaleBarcodes'])->name('admin.products.fix_barcodes');
            Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
            Route::post('/products/import', [ProductController::class, 'importExcel'])->name('admin.products.import');
        });

        // Customers CRM (requires access_pos or manage_inventory, let's wrap it inside access_pos or manage_inventory)
        Route::middleware(['permission:access_pos'])->group(function () {
            Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
            Route::post('/customers', [CustomerController::class, 'store'])->name('admin.customers.store');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
            Route::post('/customers/{customer}/pay', [CustomerController::class, 'payDebt'])->name('admin.customers.pay');
        });
    });
});
