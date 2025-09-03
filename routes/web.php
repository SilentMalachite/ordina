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
    
    // Excel出力
    Route::get('reports/export/sales', [App\Http\Controllers\ReportController::class, 'exportSales'])->name('reports.export.sales');
    Route::get('reports/export/inventory', [App\Http\Controllers\ReportController::class, 'exportInventory'])->name('reports.export.inventory');
    Route::get('reports/export/customers', [App\Http\Controllers\ReportController::class, 'exportCustomer'])->name('reports.export.customers');
    Route::get('reports/export/comprehensive', [App\Http\Controllers\ReportController::class, 'exportComprehensive'])->name('reports.export.comprehensive');

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

    // 締め処理
    Route::get('closing', [App\Http\Controllers\ClosingController::class, 'index'])->name('closing.index');
    Route::post('closing/process', [App\Http\Controllers\ClosingController::class, 'process'])->name('closing.process');
    Route::get('closing/show', [App\Http\Controllers\ClosingController::class, 'show'])->name('closing.show');
    Route::get('closing/history', [App\Http\Controllers\ClosingController::class, 'history'])->name('closing.history');

    // 在庫アラート
    Route::get('stock-alerts', [App\Http\Controllers\StockAlertController::class, 'index'])->name('stock-alerts.index');
    Route::get('stock-alerts/settings', [App\Http\Controllers\StockAlertController::class, 'settings'])->name('stock-alerts.settings');
    Route::post('stock-alerts/settings', [App\Http\Controllers\StockAlertController::class, 'updateSettings'])->name('stock-alerts.update-settings');
    Route::post('stock-alerts/run-check', [App\Http\Controllers\StockAlertController::class, 'runCheck'])->name('stock-alerts.run-check');
    Route::get('stock-alerts/history', [App\Http\Controllers\StockAlertController::class, 'history'])->name('stock-alerts.history');
    Route::get('stock-alerts/statistics', [App\Http\Controllers\StockAlertController::class, 'statistics'])->name('stock-alerts.statistics');

    // 管理者機能
    Route::middleware(['auth', 'permission:system-manage'])->group(function () {
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
        
        // ログ管理 UI
        Route::get('admin/logs', [App\Http\Controllers\Admin\LogUIController::class, 'index'])->name('admin.logs.page');

        // ログ管理（JSON API）
        Route::prefix('admin/logs')->group(function () {
            Route::get('files', [App\Http\Controllers\Admin\LogManagementController::class, 'files'])->name('admin.logs.files');
            Route::get('{filename}', [App\Http\Controllers\Admin\LogManagementController::class, 'show'])->name('admin.logs.show');
            Route::get('statistics/summary', [App\Http\Controllers\Admin\LogManagementController::class, 'statistics'])->name('admin.logs.statistics');
            Route::get('levels/errors', [App\Http\Controllers\Admin\LogManagementController::class, 'errors'])->name('admin.logs.errors');
            Route::get('levels/warnings', [App\Http\Controllers\Admin\LogManagementController::class, 'warnings'])->name('admin.logs.warnings');
            Route::post('{filename}/clear', [App\Http\Controllers\Admin\LogManagementController::class, 'clear'])->name('admin.logs.clear');
            Route::delete('{filename}', [App\Http\Controllers\Admin\LogManagementController::class, 'destroy'])->name('admin.logs.destroy');
            Route::post('rotate', [App\Http\Controllers\Admin\LogManagementController::class, 'rotate'])->name('admin.logs.rotate');
        });
        
        // バックアップ管理 UI
        Route::get('admin/backups/manage', [App\Http\Controllers\Admin\BackupUIController::class, 'index'])->name('admin.backups.page');

        // バックアップ管理（JSON API）
        Route::prefix('admin/backups')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('admin.backups.index');
            Route::get('statistics/summary', [App\Http\Controllers\Admin\BackupController::class, 'statistics'])->name('admin.backups.statistics');
            Route::post('create/full', [App\Http\Controllers\Admin\BackupController::class, 'createFull'])->name('admin.backups.create.full');
            Route::post('create/database', [App\Http\Controllers\Admin\BackupController::class, 'createDatabase'])->name('admin.backups.create.database');
            Route::get('download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('admin.backups.download');
            Route::delete('{filename}', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('admin.backups.destroy');
            Route::post('restore/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('admin.backups.restore');
            Route::post('cleanup', [App\Http\Controllers\Admin\BackupController::class, 'cleanup'])->name('admin.backups.cleanup');
        });
        
        // ロール管理
        Route::resource('roles', App\Http\Controllers\RoleController::class);

        // 同期競合管理
        Route::get('sync-conflicts', [App\Http\Controllers\SyncConflictController::class, 'index'])->name('sync-conflicts.index');
        Route::get('sync-conflicts/{syncConflict}', [App\Http\Controllers\SyncConflictController::class, 'show'])->name('sync-conflicts.show');
        Route::post('sync-conflicts/{syncConflict}/resolve', [App\Http\Controllers\SyncConflictController::class, 'resolve'])->name('sync-conflicts.resolve');
        Route::post('sync-conflicts/{syncConflict}/ignore', [App\Http\Controllers\SyncConflictController::class, 'ignore'])->name('sync-conflicts.ignore');

        // APIトークン管理
        Route::get('api-tokens', [App\Http\Controllers\ApiTokenController::class, 'index'])->name('api-tokens.index');
        Route::get('api-tokens/create', [App\Http\Controllers\ApiTokenController::class, 'create'])->name('api-tokens.create');
        Route::post('api-tokens', [App\Http\Controllers\ApiTokenController::class, 'store'])->name('api-tokens.store');
        Route::get('api-tokens/{apiToken}', [App\Http\Controllers\ApiTokenController::class, 'show'])->name('api-tokens.show');
        Route::post('api-tokens/{apiToken}/revoke', [App\Http\Controllers\ApiTokenController::class, 'revoke'])->name('api-tokens.revoke');
        Route::post('api-tokens/{apiToken}/unrevoke', [App\Http\Controllers\ApiTokenController::class, 'unrevoke'])->name('api-tokens.unrevoke');
        Route::post('api-tokens/{apiToken}/regenerate', [App\Http\Controllers\ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
        Route::post('api-tokens/cleanup-expired', [App\Http\Controllers\ApiTokenController::class, 'cleanupExpired'])->name('api-tokens.cleanup-expired');
        Route::get('api-tokens-statistics', [App\Http\Controllers\ApiTokenController::class, 'statistics'])->name('api-tokens.statistics');
    });
});

require __DIR__.'/auth.php';
