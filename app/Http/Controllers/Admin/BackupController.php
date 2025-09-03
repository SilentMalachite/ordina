<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct()
    {
        // 閲覧系
        $this->middleware('permission:backup-view')->only([
            'index', 'statistics', 'download'
        ]);
        // 破壊的操作系
        $this->middleware('permission:backup-manage')->only([
            'createFull', 'createDatabase', 'destroy', 'restore', 'cleanup'
        ]);
        $this->backupService = new BackupService();
    }

    // バックアップ一覧
    public function index()
    {
        return response()->json($this->backupService->getBackupFiles());
    }

    // バックアップ統計
    public function statistics()
    {
        return response()->json($this->backupService->getBackupStatistics());
    }

    // フルバックアップ作成
    public function createFull()
    {
        $result = $this->backupService->createFullBackup();
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    // データベースのみバックアップ作成
    public function createDatabase()
    {
        $result = $this->backupService->createDatabaseBackup();
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    // バックアップ削除
    public function destroy(string $filename)
    {
        $ok = $this->backupService->deleteBackup($filename);
        return response()->json(['success' => $ok]);
    }

    // バックアップダウンロード
    public function download(string $filename)
    {
        $path = $this->backupService->downloadBackup($filename);
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'ファイルが見つかりません。'], 404);
        }
        return response()->download($path);
    }

    // バックアップから復元
    public function restore(string $filename)
    {
        $result = $this->backupService->restoreFromBackup($filename);
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    // 古いバックアップのクリーンアップ
    public function cleanup()
    {
        $deleted = $this->backupService->cleanupOldBackups();
        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
