<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use App\Services\InputSanitizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreInventoryAdjustmentRequest;
use App\Http\Requests\BulkInventoryAdjustmentRequest;

class InventoryController extends Controller
{
    protected InputSanitizationService $sanitizationService;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->sanitizationService = new InputSanitizationService();
        $this->middleware('permission:inventory-view')->only('index', 'adjustments', 'stockAlert');
        $this->middleware('permission:inventory-adjust')->only('createAdjustment', 'storeAdjustment', 'bulkAdjustment', 'storeBulkAdjustment');
    }

    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('low_stock') && $request->low_stock) {
            $query->where('stock_quantity', '<=', 10);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $this->sanitizationService->sanitizeSearchInput($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('product_code', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }
        }
        
        $products = $query->orderBy('stock_quantity', 'asc')->paginate(20);
        $lowStockCount = Product::where('stock_quantity', '<=', 10)->count();
        
        return view('inventory.index', compact('products', 'lowStockCount'));
    }

    public function adjustments(Request $request)
    {
        $query = InventoryAdjustment::with(['product', 'user']);
        
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $adjustments = $query->orderBy('created_at', 'desc')->paginate(20);
        $products = Product::orderBy('name')->get();
        
        return view('inventory.adjustments', compact('adjustments', 'products'));
    }

    public function createAdjustment()
    {
        $products = Product::orderBy('name')->get();
        return view('inventory.create-adjustment', compact('products'));
    }

    public function storeAdjustment(StoreInventoryAdjustmentRequest $request)
    {

        try {
            DB::transaction(function() use ($request) {
                $product = Product::findOrFail($request->product_id);
                
                $adjustmentQuantity = $request->adjustment_type === 'increase' 
                    ? $request->quantity 
                    : -$request->quantity;
                
                if ($request->adjustment_type === 'decrease' && $product->stock_quantity < $request->quantity) {
                    throw new \Exception('在庫数が不足しています。');
                }
                
                InventoryAdjustment::create([
                    'product_id' => $request->product_id,
                    'user_id' => auth()->id(),
                    'adjustment_type' => $request->adjustment_type,
                    'quantity' => $request->quantity,
                    'previous_quantity' => $product->stock_quantity,
                    'new_quantity' => $product->stock_quantity + $adjustmentQuantity,
                    'reason' => $request->reason,
                ]);
                
                $product->update([
                    'stock_quantity' => $product->stock_quantity + $adjustmentQuantity
                ]);
            });

            return redirect()->route('inventory.adjustments')
                ->with('success', '在庫調整が正常に記録されました。');
                
        } catch (\Exception $e) {
            \Log::error('在庫調整処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function stockAlert()
    {
        $lowStockProducts = Product::where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->get();
            
        return view('inventory.stock-alert', compact('lowStockProducts'));
    }

    public function bulkAdjustment()
    {
        $products = Product::orderBy('name')->get();
        return view('inventory.bulk-adjustment', compact('products'));
    }

    public function storeBulkAdjustment(BulkInventoryAdjustmentRequest $request)
    {

        try {
            DB::transaction(function() use ($request) {
                foreach ($request->adjustments as $adjustment) {
                    $product = Product::findOrFail($adjustment['product_id']);
                    
                    $adjustmentQuantity = $adjustment['adjustment_type'] === 'increase' 
                        ? $adjustment['quantity'] 
                        : -$adjustment['quantity'];
                    
                    if ($adjustment['adjustment_type'] === 'decrease' && $product->stock_quantity < $adjustment['quantity']) {
                        throw new \Exception("商品「{$product->name}」の在庫数が不足しています。");
                    }
                    
                    InventoryAdjustment::create([
                        'product_id' => $adjustment['product_id'],
                        'user_id' => auth()->id(),
                        'adjustment_type' => $adjustment['adjustment_type'],
                        'quantity' => $adjustment['quantity'],
                        'previous_quantity' => $product->stock_quantity,
                        'new_quantity' => $product->stock_quantity + $adjustmentQuantity,
                        'reason' => $request->reason,
                    ]);
                    
                    $product->update([
                        'stock_quantity' => $product->stock_quantity + $adjustmentQuantity
                    ]);
                }
            });

            return redirect()->route('inventory.adjustments')
                ->with('success', '一括在庫調整が正常に記録されました。');
                
        } catch (\Exception $e) {
            \Log::error('一括在庫調整処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }
}
