<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:product-list')->only('index', 'show', 'search');
        $this->middleware('permission:product-create')->only('create', 'store');
        $this->middleware('permission:product-edit')->only('edit', 'update');
        $this->middleware('permission:product-delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:50|unique:products',
            'name' => 'required|string|max:255',
            'stock_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', '商品が正常に登録されました。');
    }

    public function show(Product $product)
    {
        $recentTransactions = $product->transactions()
            ->with('customer')
            ->latest()
            ->take(10)
            ->get();
            
        return view('products.show', compact('product', 'recentTransactions'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $product->id,
            'name' => 'required|string|max:255',
            'stock_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product->update($request->all());

        return redirect()->route('products.index')
            ->with('success', '商品情報が正常に更新されました。');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', '商品が正常に削除されました。');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }
        
        $products = Product::where('product_code', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->select('id', 'product_code', 'name', 'stock_quantity', 'selling_price')
            ->limit(10)
            ->get();
            
        return response()->json($products);
    }
}