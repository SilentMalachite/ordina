<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 同期API
Route::get('/health-check', [App\Http\Controllers\Api\SyncController::class, 'healthCheck']);

Route::middleware(['api.token', 'api.rate-limit'])->group(function () {
    Route::post('/sync/start', [App\Http\Controllers\Api\SyncController::class, 'start']);
    Route::post('/sync/push', [App\Http\Controllers\Api\SyncController::class, 'push'])->middleware('api.rate-limit:sync');
    Route::get('/sync/pull', [App\Http\Controllers\Api\SyncController::class, 'pull'])->middleware('api.rate-limit:sync');
    Route::post('/sync/resolve-conflict', [App\Http\Controllers\Api\SyncController::class, 'resolveConflict'])->middleware('api.rate-limit:general');
});
