<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Events\LowStockDetected;

class TransactionController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:transaction-list')->only('index', 'show', 'sales', 'rentals');
        $this->middleware('permission:transaction-create')->only('create', 'store', 'returnItem');
        $this->middleware('permission:transaction-edit')->only('edit', 'update');
        $this->middleware('permission:transaction-delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Transaction::with(['product', 'customer', 'user']);
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('transactions.index', compact('transactions', 'customers', 'products'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('stock_quantity', '>', 0)->orderBy('name')->get();
        
        return view('transactions.create', compact('customers', 'products'));
    }

    public function store(StoreTransactionRequest $request)
    {

        try {
            DB::transaction(function() use ($request) {
                $product = Product::findOrFail($request->product_id);
                
                if ($product->stock_quantity < $request->quantity) {
                    throw new \Exception('在庫数が不足しています。');
                }
                
                Transaction::create([
                    'type' => $request->type,
                    'customer_id' => $request->customer_id,
                    'product_id' => $request->product_id,
                    'user_id' => auth()->id(),
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'total_amount' => $request->quantity * $request->unit_price,
                    'transaction_date' => $request->transaction_date,
                    'expected_return_date' => $request->expected_return_date,
                    'notes' => $request->notes,
                ]);
                
                $product->decrement('stock_quantity', $request->quantity);
                
                // 在庫僅少チェック
                if ($product->stock_quantity <= config('app.low_stock_threshold', 10)) {
                    event(new LowStockDetected($product));
                }
            });

            $message = $request->type === 'sale' ? '売上が正常に記録されました。' : '貸し出しが正常に記録されました。';

            return redirect()->route('transactions.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('取引処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['product', 'customer', 'user']);
        
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('transactions.edit', compact('transaction', 'customers', 'products'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {

        try {
            DB::transaction(function() use ($request, $transaction) {
                $oldProduct = $transaction->product;
                $newProduct = Product::findOrFail($request->product_id);
                
                $oldQuantity = $transaction->quantity;
                $newQuantity = $request->quantity;
                
                if ($transaction->product_id !== $request->product_id) {
                    $oldProduct->increment('stock_quantity', $oldQuantity);
                    
                    if ($newProduct->stock_quantity < $newQuantity) {
                        throw new \Exception('新しい商品の在庫数が不足しています。');
                    }
                    $newProduct->decrement('stock_quantity', $newQuantity);
                } else {
                    $quantityDifference = $newQuantity - $oldQuantity;
                    
                    if ($quantityDifference > 0 && $newProduct->stock_quantity < $quantityDifference) {
                        throw new \Exception('在庫数が不足しています。');
                    }
                    
                    if ($quantityDifference !== 0) {
                        if ($quantityDifference > 0) {
                            $newProduct->decrement('stock_quantity', $quantityDifference);
                        } else {
                            $newProduct->increment('stock_quantity', abs($quantityDifference));
                        }
                    }
                }
                
                $transaction->update([
                    'type' => $request->type,
                    'customer_id' => $request->customer_id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'total_amount' => $request->quantity * $request->unit_price,
                    'transaction_date' => $request->transaction_date,
                    'expected_return_date' => $request->expected_return_date,
                    'notes' => $request->notes,
                ]);
            });

            return redirect()->route('transactions.index')
                ->with('success', '取引情報が正常に更新されました。');
                
        } catch (\Exception $e) {
            \Log::error('取引更新処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function destroy(Transaction $transaction)
    {
        try {
            DB::transaction(function() use ($transaction) {
                $product = $transaction->product;
                $product->increment('stock_quantity', $transaction->quantity);
                
                $transaction->delete();
            });

            return redirect()->route('transactions.index')
                ->with('success', '取引が正常に削除されました。');
                
        } catch (\Exception $e) {
            \Log::error('取引削除処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function returnItem(Transaction $transaction)
    {
        try {
            $transaction->returnItem();
            return redirect()->back()->with('success', '商品の返却が記録されました。');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function sales()
    {
        $query = Transaction::with(['product', 'customer'])
            ->where('type', 'sale');
            
        $sales = $query->orderBy('transaction_date', 'desc')->paginate(20);
        
        $totalSales = Transaction::where('type', 'sale')
            ->sum(DB::raw('quantity * unit_price'));
            
        return view('transactions.sales', compact('sales', 'totalSales'));
    }

    public function rentals()
    {
        $activeRentals = Transaction::with(['product', 'customer'])
            ->where('type', 'rental')
            ->whereNull('returned_at')
            ->orderBy('expected_return_date', 'asc')
            ->get();
            
        $overdueRentals = Transaction::with(['product', 'customer'])
            ->where('type', 'rental')
            ->whereNull('returned_at')
            ->where('expected_return_date', '<', now())
            ->orderBy('expected_return_date', 'asc')
            ->get();
            
        return view('transactions.rentals', compact('activeRentals', 'overdueRentals'));
    }
}