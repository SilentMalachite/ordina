<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 商品管理
    Route::get('products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
    Route::resource('products', App\Http\Controllers\ProductController::class);

    // 在庫管理
    Route::get('inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/adjustments', [App\Http\Controllers\InventoryController::class, 'adjustments'])->name('inventory.adjustments');
    Route::get('inventory/adjustment/create', [App\Http\Controllers\InventoryController::class, 'createAdjustment'])->name('inventory.adjustment.create');
    Route::post('inventory/adjustment', [App\Http\Controllers\InventoryController::class, 'storeAdjustment'])->name('inventory.adjustment.store');
    Route::get('inventory/stock-alert', [App\Http\Controllers\InventoryController::class, 'stockAlert'])->name('inventory.stock-alert');
    Route::get('inventory/bulk-adjustment', [App\Http\Controllers\InventoryController::class, 'bulkAdjustment'])->name('inventory.bulk-adjustment');
    Route::post('inventory/bulk-adjustment', [App\Http\Controllers\InventoryController::class, 'storeBulkAdjustment'])->name('inventory.bulk-adjustment.store');

    // 顧客管理
    Route::get('customers/search', [App\Http\Controllers\CustomerController::class, 'search'])->name('customers.search');
    Route::get('customers/{customer}/rentals', [App\Http\Controllers\CustomerController::class, 'rentals'])->name('customers.rentals');
    Route::post('customers/{customer}/transactions/{transaction}/return', [App\Http\Controllers\CustomerController::class, 'returnItem'])->name('customers.return-item');
    Route::resource('customers', App\Http\Controllers\CustomerController::class);

    // 取引管理
    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    Route::post('transactions/{transaction}/return', [App\Http\Controllers\TransactionController::class, 'returnItem'])->name('transactions.return');
    Route::get('transactions-sales', [App\Http\Controllers\TransactionController::class, 'sales'])->name('transactions.sales');
    Route::get('transactions-rentals', [App\Http\Controllers\TransactionController::class, 'rentals'])->name('transactions.rentals');

    // レポート
    Route::get('reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [App\Http\Controllers\ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('reports/rentals', [App\Http\Controllers\ReportController::class, 'rentalReport'])->name('reports.rentals');
    Route::get('reports/inventory', [App\Http\Controllers\ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('reports/customers', [App\Http\Controllers\ReportController::class, 'customerReport'])->name('reports.customers');
    Route::get('reports/export/sales', [App\Http\Controllers\ReportController::class, 'exportSales'])->name('reports.export.sales');
    Route::get('reports/export/inventory', [App\Http\Controllers\ReportController::class, 'exportInventory'])->name('reports.export.inventory');

    // インポート
    Route::get('import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    Route::get('import/products', [App\Http\Controllers\ImportController::class, 'products'])->name('import.products');
    Route::get('import/customers', [App\Http\Controllers\ImportController::class, 'customers'])->name('import.customers');
    Route::get('import/transactions', [App\Http\Controllers\ImportController::class, 'transactions'])->name('import.transactions');
    Route::post('import/products', [App\Http\Controllers\ImportController::class, 'importProducts'])->name('import.products.store');
    Route::post('import/customers', [App\Http\Controllers\ImportController::class, 'importCustomers'])->name('import.customers.store');
    Route::post('import/transactions', [App\Http\Controllers\ImportController::class, 'importTransactions'])->name('import.transactions.store');
    Route::get('import/template/{type}', [App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('import.template');

    // ジョブステータス
    Route::get('job-statuses', [App\Http\Controllers\JobStatusController::class, 'index'])->name('job-statuses.index');
    Route::get('job-statuses/{jobStatus}', [App\Http\Controllers\JobStatusController::class, 'show'])->name('job-statuses.show');

    // 管理者機能
    Route::middleware(['auth', 'role:管理者'])->group(function () {
        Route::get('admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
        Route::get('admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
        Route::get('admin/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.users.create');
        Route::post('admin/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::get('admin/users/{user}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::patch('admin/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [App\Http\Controllers\AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('admin/data-management', [App\Http\Controllers\AdminController::class, 'dataManagement'])->name('admin.data-management');
        Route::post('admin/backup', [App\Http\Controllers\AdminController::class, 'backupData'])->name('admin.backup');
        Route::post('admin/clear-data', [App\Http\Controllers\AdminController::class, 'clearData'])->name('admin.clear-data');
        Route::get('admin/system-settings', [App\Http\Controllers\AdminController::class, 'systemSettings'])->name('admin.system-settings');
        Route::get('admin/closing-dates', [App\Http\Controllers\AdminController::class, 'closingDates'])->name('admin.closing-dates');
        Route::get('admin/closing-dates/create', [App\Http\Controllers\AdminController::class, 'createClosingDate'])->name('admin.closing-dates.create');
        Route::post('admin/closing-dates', [App\Http\Controllers\AdminController::class, 'storeClosingDate'])->name('admin.closing-dates.store');
        Route::delete('admin/closing-dates/{closingDate}', [App\Http\Controllers\AdminController::class, 'destroyClosingDate'])->name('admin.closing-dates.destroy');
        Route::get('admin/system-logs', [App\Http\Controllers\AdminController::class, 'systemLogs'])->name('admin.system-logs');
        Route::get('admin/backup/download/{filename}', [App\Http\Controllers\AdminController::class, 'downloadBackup'])->name('admin.backup.download');
        
        // ロール管理
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });
});

require __DIR__.'/auth.php';
