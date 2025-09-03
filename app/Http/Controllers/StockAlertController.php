<?php

namespace App\Http\Controllers;

use App\Services\StockAlertService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    protected $stockAlertService;
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:inventory-view');
        $this->stockAlertService = new StockAlertService();
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * 在庫アラート一覧を表示
     */
    public function index()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return [
                'low_stock_products' => $this->stockAlertService->checkLowStock(),
                'out_of_stock_products' => $this->stockAlertService->checkOutOfStock(),
                'statistics' => $this->stockAlertService->getAlertStatistics()
            ];
        }, '在庫アラート一覧の取得');

        if ($result['success']) {
            $data = $result['data'];
            return view('stock-alerts.index', $data);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 在庫アラート設定を表示
     */
    public function settings()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return [
                'statistics' => $this->stockAlertService->getAlertStatistics(),
                'alert_history' => $this->stockAlertService->getAlertHistory(20)
            ];
        }, '在庫アラート設定の取得');

        if ($result['success']) {
            $data = $result['data'];
            return view('stock-alerts.settings', $data);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 在庫アラート設定を更新
     */
    public function updateSettings(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'low_stock_threshold' => 'required|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->stockAlertService->updateAlertSettings($request->low_stock_threshold);

        if ($result) {
            return redirect()->route('stock-alerts.settings')
                ->with('success', '在庫アラート設定が正常に更新されました。');
        }

        return redirect()->back()
            ->with('error', '在庫アラート設定の更新に失敗しました。')
            ->withInput();
    }

    /**
     * 在庫チェックを手動実行
     */
    public function runCheck()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            $this->stockAlertService->runScheduledCheck();
            return true;
        }, '在庫チェックの実行');

        if ($result['success']) {
            return redirect()->route('stock-alerts.index')
                ->with('success', '在庫チェックが正常に実行されました。');
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 在庫アラート履歴を表示
     */
    public function history()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return $this->stockAlertService->getAlertHistory(100);
        }, '在庫アラート履歴の取得');

        if ($result['success']) {
            $history = $result['data'];
            return view('stock-alerts.history', compact('history'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 在庫アラート統計を取得（API）
     */
    public function statistics()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return $this->stockAlertService->getAlertStatistics();
        }, '在庫アラート統計の取得');

        if ($result['success']) {
            return response()->json($result['data']);
        }

        return response()->json(['error' => $result['message']], 500);
    }
}
