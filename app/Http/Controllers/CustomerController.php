<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:customer-list')->only('index', 'show', 'search', 'rentals');
        $this->middleware('permission:customer-create')->only('create', 'store');
        $this->middleware('permission:customer-edit')->only('edit', 'update');
        $this->middleware('permission:customer-delete')->only('destroy');
        $this->middleware('permission:transaction-create')->only('returnItem');
    }

    public function index(Request $request)
    {
        $query = Customer::query();
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $customers = $query->withCount('transactions')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);
        
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')
            ->with('success', '顧客が正常に登録されました。');
    }

    public function show(Customer $customer)
    {
        $transactions = $customer->transactions()
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $statistics = DB::table('transactions')
            ->where('customer_id', $customer->id)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN type = "sale" THEN quantity * unit_price ELSE 0 END) as total_sales,
                COUNT(CASE WHEN type = "rental" THEN 1 END) as total_rentals,
                COUNT(CASE WHEN type = "rental" AND returned_at IS NULL THEN 1 END) as pending_returns
            ')
            ->first();
        
        return view('customers.show', compact('customer', 'transactions', 'statistics'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', '顧客情報が正常に更新されました。');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->transactions()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'この顧客には取引履歴があるため削除できません。');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', '顧客が正常に削除されました。');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }
        
        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'type', 'email', 'phone')
            ->limit(10)
            ->get();
            
        return response()->json($customers);
    }

    public function rentals(Customer $customer)
    {
        $activeRentals = $customer->transactions()
            ->with('product')
            ->where('type', 'rental')
            ->whereNull('returned_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $rentalHistory = $customer->transactions()
            ->with('product')
            ->where('type', 'rental')
            ->whereNotNull('returned_at')
            ->orderBy('returned_at', 'desc')
            ->paginate(10);
            
        return view('customers.rentals', compact('customer', 'activeRentals', 'rentalHistory'));
    }

    public function returnItem(Request $request, Customer $customer, Transaction $transaction)
    {
        if ($transaction->customer_id !== $customer->id) {
            return redirect()->back()->with('error', '無効な取引です。この取引はこの顧客のものではありません。');
        }

        try {
            $transaction->returnItem();
            return redirect()->back()->with('success', '商品の返却が記録されました。');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }
}