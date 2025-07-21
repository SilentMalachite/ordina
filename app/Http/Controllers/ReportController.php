<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ClosingDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:report-view')->only('index', 'salesReport', 'rentalReport', 'inventoryReport', 'customerReport');
        $this->middleware('permission:report-export')->only('exportSales', 'exportInventory');
    }

    public function index()
    {
        $closingDates = ClosingDate::orderBy('closing_date', 'desc')->take(12)->get();
        
        return view('reports.index', compact('closingDates'));
    }

    public function salesReport(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'daily');
        $customerId = $request->input('customer_id');
        
        $query = Transaction::with(['product', 'customer'])
            ->where('type', 'sale')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);
            
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        
        $groupedData = $this->groupTransactionsByPeriod($transactions, $groupBy);
        $productSummary = $this->getProductSummary($transactions);
        $customerSummary = $this->getCustomerSummary($transactions);
        
        $customers = Customer::orderBy('name')->get();
        
        return view('reports.sales', compact(
            'transactions', 'groupedData', 'productSummary', 
            'customerSummary', 'customers', 'dateFrom', 'dateTo', 'groupBy', 'customerId'
        ));
    }

    public function rentalReport(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'daily');
        $customerId = $request->input('customer_id');
        $status = $request->input('status', 'all');
        
        $query = Transaction::with(['product', 'customer'])
            ->where('type', 'rental')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);
            
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        if ($status === 'active') {
            $query->whereNull('returned_at');
        } elseif ($status === 'returned') {
            $query->whereNotNull('returned_at');
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        
        $groupedData = $this->groupTransactionsByPeriod($transactions, $groupBy);
        $productSummary = $this->getProductSummary($transactions);
        $customerSummary = $this->getCustomerSummary($transactions);
        
        $customers = Customer::orderBy('name')->get();
        
        return view('reports.rentals', compact(
            'transactions', 'groupedData', 'productSummary', 
            'customerSummary', 'customers', 'dateFrom', 'dateTo', 'groupBy', 'customerId', 'status'
        ));
    }

    public function inventoryReport(Request $request)
    {
        $lowStockOnly = $request->input('low_stock_only', false);
        $categoryFilter = $request->input('category_filter');
        
        $query = Product::with(['transactions' => function($q) {
            $q->orderBy('transaction_date', 'desc')->take(5);
        }]);
        
        if ($lowStockOnly) {
            $query->where('stock_quantity', '<=', 10);
        }
        
        $products = $query->orderBy('stock_quantity', 'asc')->get();
        
        $totalStockValue = $products->sum(function($product) {
            return $product->stock_quantity * $product->unit_price;
        });
        
        $lowStockCount = Product::where('stock_quantity', '<=', 10)->count();
        $totalProducts = Product::count();
        
        return view('reports.inventory', compact(
            'products', 'totalStockValue', 'lowStockCount', 
            'totalProducts', 'lowStockOnly'
        ));
    }

    public function customerReport(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        
        $customers = Customer::with(['transactions' => function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }])->get();
        
        $customerStats = $customers->map(function($customer) {
            $sales = $customer->transactions->where('type', 'sale');
            $rentals = $customer->transactions->where('type', 'rental');
            
            return [
                'customer' => $customer,
                'total_sales' => $sales->sum('total_amount'),
                'sales_count' => $sales->count(),
                'total_rentals' => $rentals->sum('total_amount'),
                'rentals_count' => $rentals->count(),
                'active_rentals' => $rentals->where('returned_at', null)->count(),
            ];
        })->sortByDesc('total_sales');
        
        return view('reports.customers', compact('customerStats', 'dateFrom', 'dateTo'));
    }

    public function exportSales(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $customerId = $request->input('customer_id');
        
        $query = Transaction::with(['product', 'customer'])
            ->where('type', 'sale')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);
            
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        
        return $this->generateCsvResponse($transactions, 'sales_report_' . $dateFrom . '_to_' . $dateTo . '.csv');
    }

    public function exportInventory()
    {
        $products = Product::with(['transactions' => function($q) {
            $q->latest()->take(1);
        }])->orderBy('name')->get();
        
        $data = $products->map(function($product) {
            return [
                '商品コード' => $product->product_code,
                '商品名' => $product->name,
                '在庫数' => $product->stock_quantity,
                '単価' => $product->unit_price,
                '売値' => $product->selling_price,
                '在庫価値' => $product->stock_quantity * $product->unit_price,
                '最終取引日' => $product->transactions->first() ? 
                    $product->transactions->first()->transaction_date->format('Y-m-d') : '',
                '説明' => $product->description,
            ];
        });
        
        return $this->generateCsvResponse($data, 'inventory_report_' . now()->format('Y-m-d') . '.csv');
    }

    private function groupTransactionsByPeriod($transactions, $groupBy)
    {
        return $transactions->groupBy(function($transaction) use ($groupBy) {
            $date = Carbon::parse($transaction->transaction_date);
            
            switch ($groupBy) {
                case 'weekly':
                    return $date->format('Y-W');
                case 'monthly':
                    return $date->format('Y-m');
                case 'yearly':
                    return $date->format('Y');
                default:
                    return $date->format('Y-m-d');
            }
        })->map(function($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('total_amount'),
                'total_quantity' => $group->sum('quantity'),
            ];
        });
    }

    private function getProductSummary($transactions)
    {
        return $transactions->groupBy('product.name')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('total_amount'),
                'total_quantity' => $group->sum('quantity'),
            ];
        })->sortByDesc('total_amount');
    }

    private function getCustomerSummary($transactions)
    {
        return $transactions->groupBy('customer.name')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('total_amount'),
                'total_quantity' => $group->sum('quantity'),
            ];
        })->sortByDesc('total_amount');
    }

    private function generateCsvResponse($data, $filename)
    {
        $output = fopen('php://temp', 'r+');
        
        if ($data->isNotEmpty()) {
            if (is_array($data->first())) {
                fputcsv($output, array_keys($data->first()));
                
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            } else {
                $headers = [
                    '取引日', '取引タイプ', '顧客名', '商品名', 
                    '数量', '単価', '合計金額', '備考'
                ];
                fputcsv($output, $headers);
                
                foreach ($data as $transaction) {
                    fputcsv($output, [
                        $transaction->transaction_date->format('Y-m-d'),
                        $transaction->type === 'sale' ? '売上' : '貸出',
                        $transaction->customer->name,
                        $transaction->product->name,
                        $transaction->quantity,
                        $transaction->unit_price,
                        $transaction->total_amount,
                        $transaction->notes,
                    ]);
                }
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($csv));
    }
}