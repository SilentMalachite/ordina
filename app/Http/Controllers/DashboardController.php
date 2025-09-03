<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->is_admin) {
            return $this->adminDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    private function adminDashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
            'recent_transactions' => Transaction::with(['product', 'customer'])
                ->latest()
                ->take(5)
                ->get(),
            'total_revenue' => Transaction::where('type', 'sale')
                ->get()
                ->sum(function ($transaction) {
                    return $transaction->quantity * $transaction->unit_price;
                }),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    private function userDashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
            'recent_transactions' => Transaction::with(['product', 'customer'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('dashboard.user', compact('stats'));
    }
}
