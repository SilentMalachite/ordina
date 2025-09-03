<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogManagementService;
use Illuminate\Http\Request;

class LogManagementController extends Controller
{
    protected LogManagementService $logService;

    public function __construct()
    {
        // 閲覧系
        $this->middleware('permission:log-view')->only([
            'files', 'show', 'statistics', 'errors', 'warnings'
        ]);
        // 破壊的操作系
        $this->middleware('permission:log-manage')->only([
            'clear', 'destroy', 'rotate'
        ]);
        $this->logService = new LogManagementService();
    }

    // ログファイル一覧
    public function files()
    {
        return response()->json($this->logService->getLogFiles());
    }

    // 指定ファイルの内容（最新N行）
    public function show(string $filename, Request $request)
    {
        $lines = (int) $request->input('lines', 100);
        return response()->json($this->logService->getLogContent($filename, $lines));
    }

    // 統計情報
    public function statistics()
    {
        return response()->json($this->logService->getLogStatistics());
    }

    // エラーログのみ
    public function errors(Request $request)
    {
        $lines = (int) $request->input('lines', 50);
        return response()->json($this->logService->getErrorLogs($lines));
    }

    // 警告ログのみ
    public function warnings(Request $request)
    {
        $lines = (int) $request->input('lines', 50);
        return response()->json($this->logService->getWarningLogs($lines));
    }

    // 指定ファイルをクリア
    public function clear(string $filename)
    {
        $ok = $this->logService->clearLogFile($filename);
        return response()->json(['success' => $ok]);
    }

    // 指定ファイルを削除
    public function destroy(string $filename)
    {
        $ok = $this->logService->deleteLogFile($filename);
        return response()->json(['success' => $ok]);
    }

    // サイズ超過などでローテーション実行 + 古いファイル整理
    public function rotate()
    {
        $rotated = $this->logService->rotateLogs();
        return response()->json(['success' => true, 'rotated' => $rotated]);
    }
}
