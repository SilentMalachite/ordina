<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Services\ErrorHandlingService;

class DashboardController extends Controller
{
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:system-manage');
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * 管理者ダッシュボードを表示
     */
    public function index()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return [
                'stats' => $this->getSystemStats(),
                'recentActivities' => $this->getRecentActivities()
            ];
        }, '管理者ダッシュボードデータの取得');

        if ($result['success']) {
            $data = $result['data'];
            return view('admin.index', $data);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * システム統計を取得
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_transactions' => Transaction::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
            'active_rentals' => Transaction::where('type', 'rental')->whereNull('returned_at')->count(),
        ];
    }

    /**
     * 最近のアクティビティを取得
     */
    private function getRecentActivities(): array
    {
        return [
            'new_users' => User::latest()->take(5)->get(),
            'recent_transactions' => Transaction::with(['product', 'customer', 'user'])
                ->latest()->take(5)->get(),
            'recent_adjustments' => InventoryAdjustment::with(['product', 'user'])
                ->latest()->take(5)->get(),
        ];
    }
}