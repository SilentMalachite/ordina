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

});
