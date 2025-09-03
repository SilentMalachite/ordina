<?php

use Illuminate\Support\Facades\Route;

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
