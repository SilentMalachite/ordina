<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SafeDataDeletionService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DataManagementController extends Controller
{
    protected $deletionService;
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:system-manage');
        $this->deletionService = new SafeDataDeletionService();
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * データ管理画面を表示
     */
    public function index()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return $this->deletionService->getDataStatistics();
        }, 'データ統計の取得');

        if ($result['success']) {
            $stats = $result['data'];
            return view('admin.data-management', compact('stats'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * データバックアップを作成
     */
    public function backup()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return $this->createDataBackup();
        }, 'データバックアップの作成');

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'バックアップが正常に作成されました。',
                'backup_file' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * データを削除
     */
    public function clear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_type' => 'required|in:transactions,products,customers,all',
            'confirmation' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $result = $this->deletionService->deleteData($request->data_type, auth()->id());

        if ($result['success']) {
            $message = $result['message'];
            if ($result['backup_created']) {
                $message .= ' バックアップも作成されました。';
            }
            
            return redirect()->route('admin.data-management')
                ->with('success', $message);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * バックアップファイルをダウンロード
     */
    public function downloadBackup($filename)
    {
        // パストラバーサル対策: ファイル名検証 + 実パス検証
        if ($filename !== basename($filename) || !preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            return redirect()->back()->with('error', '不正なファイル名です。');
        }
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'json') {
            return redirect()->back()->with('error', '許可されていないファイル形式です。');
        }

        $base = storage_path('app/backups');
        $path = $base . DIRECTORY_SEPARATOR . $filename;
        $real = realpath($path);
        if ($real === false) {
            return redirect()->back()->with('error', 'バックアップファイルが見つかりません。');
        }
        $baseReal = realpath($base) ?: $base;
        $baseReal = rtrim($baseReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $realWithSep = rtrim($real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realWithSep, $baseReal)) {
            return redirect()->back()->with('error', '不正なファイルパスです。');
        }

        return response()->download($real);
    }

    /**
     * データバックアップを作成
     */
    private function createDataBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("app/backups/backup_{$timestamp}.json");
        
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }
        
        $data = [
            'created_at' => now()->toISOString(),
            'products' => \App\Models\Product::all()->toArray(),
            'customers' => \App\Models\Customer::all()->toArray(),
            'transactions' => \App\Models\Transaction::with(['product', 'customer'])->get()->toArray(),
            'inventory_adjustments' => \App\Models\InventoryAdjustment::with(['product', 'user'])->get()->toArray(),
            'closing_dates' => \App\Models\ClosingDate::all()->toArray(),
            'users' => \App\Models\User::select('id', 'name', 'email', 'is_admin', 'created_at')->get()->toArray(),
        ];
        
        file_put_contents($backupPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return "backup_{$timestamp}.json";
    }
}
