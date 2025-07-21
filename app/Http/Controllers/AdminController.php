<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Models\ClosingDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:system-manage')->only('index', 'dataManagement', 'backupData', 'clearData', 'systemSettings', 'systemLogs', 'downloadBackup');
        $this->middleware('permission:user-manage')->only('users', 'createUser', 'storeUser', 'editUser', 'updateUser', 'destroyUser');
        $this->middleware('permission:closing-date-manage')->only('closingDates', 'createClosingDate', 'storeClosingDate', 'destroyClosingDate');
    }

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_transactions' => Transaction::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
            'active_rentals' => Transaction::where('type', 'rental')->whereNull('returned_at')->count(),
        ];

        $recentActivities = [
            'new_users' => User::latest()->take(5)->get(),
            'recent_transactions' => Transaction::with(['product', 'customer', 'user'])
                ->latest()->take(5)->get(),
            'recent_adjustments' => InventoryAdjustment::with(['product', 'user'])
                ->latest()->take(5)->get(),
        ];

        return view('admin.index', compact('stats', 'recentActivities'));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users', compact('users'));
    }

    public function createUser()
    {
        $roles = Role::all();
        return view('admin.create-user', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->role === '管理者',
            'email_verified_at' => now(),
        ]);
        
        $user->assignRole($request->role);

        return redirect()->route('admin.users')
            ->with('success', 'ユーザーが正常に作成されました。');
    }

    public function editUser(User $user)
    {
        $roles = Role::all();
        $userRole = $user->roles->first();
        return view('admin.edit-user', compact('user', 'roles', 'userRole'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->role === '管理者',
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')
            ->with('success', 'ユーザー情報が正常に更新されました。');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', '自分自身のアカウントは削除できません。');
        }

        if ($user->transactions()->exists() || $user->inventoryAdjustments()->exists()) {
            return redirect()->route('admin.users')
                ->with('error', 'このユーザーには関連する取引データがあるため削除できません。');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'ユーザーが正常に削除されました。');
    }

    public function dataManagement()
    {
        $stats = [
            'products_count' => Product::count(),
            'customers_count' => Customer::count(),
            'transactions_count' => Transaction::count(),
            'adjustments_count' => InventoryAdjustment::count(),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('admin.data-management', compact('stats'));
    }

    public function backupData()
    {
        try {
            $backup = $this->createDataBackup();
            
            return response()->json([
                'success' => true,
                'message' => 'バックアップが正常に作成されました。',
                'backup_file' => $backup
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'バックアップの作成中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_type' => 'required|in:transactions,products,customers,all',
            'confirmation' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::transaction(function() use ($request) {
                switch ($request->data_type) {
                    case 'transactions':
                        Transaction::truncate();
                        InventoryAdjustment::truncate();
                        Product::query()->update(['stock_quantity' => 0]);
                        break;
                        
                    case 'products':
                        Transaction::truncate();
                        InventoryAdjustment::truncate();
                        Product::truncate();
                        break;
                        
                    case 'customers':
                        Transaction::truncate();
                        Customer::truncate();
                        break;
                        
                    case 'all':
                        Transaction::truncate();
                        InventoryAdjustment::truncate();
                        Product::truncate();
                        Customer::truncate();
                        ClosingDate::truncate();
                        break;
                }
            });

            return redirect()->route('admin.data-management')
                ->with('success', 'データが正常に削除されました。');
                
        } catch (\Exception $e) {
            \Log::error('データ削除処理中にエラーが発生しました: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function systemSettings()
    {
        $settings = [
            'low_stock_threshold' => 10, // 設定値として保存したい場合は設定テーブルを作成
            'default_closing_day' => 25,
            'backup_frequency' => 'weekly',
        ];

        return view('admin.system-settings', compact('settings'));
    }

    public function closingDates()
    {
        $closingDates = ClosingDate::orderBy('day_of_month', 'desc')->paginate(20);
        
        return view('admin.closing-dates', compact('closingDates'));
    }

    public function createClosingDate()
    {
        return view('admin.create-closing-date');
    }

    public function storeClosingDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_month' => 'required|integer|between:1,31|unique:closing_dates',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ClosingDate::create([
            'day_of_month' => $request->day_of_month,
            'description' => $request->description,
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.closing-dates')
            ->with('success', '締め日が正常に設定されました。');
    }

    public function destroyClosingDate(ClosingDate $closingDate)
    {
        $closingDate->delete();

        return redirect()->route('admin.closing-dates')
            ->with('success', '締め日が正常に削除されました。');
    }

    public function systemLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $logLines = array_reverse(explode("\n", $logContent));
            $logs = array_slice($logLines, 0, 100); // 最新100行
        }

        return view('admin.system-logs', compact('logs'));
    }

    private function getDatabaseSize()
    {
        $dbPath = database_path('database.sqlite');
        
        if (file_exists($dbPath)) {
            return filesize($dbPath);
        }
        
        return 0;
    }

    private function createDataBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("app/backups/backup_{$timestamp}.json");
        
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }
        
        $data = [
            'created_at' => now()->toISOString(),
            'products' => Product::all()->toArray(),
            'customers' => Customer::all()->toArray(),
            'transactions' => Transaction::with(['product', 'customer'])->get()->toArray(),
            'inventory_adjustments' => InventoryAdjustment::with(['product', 'user'])->get()->toArray(),
            'closing_dates' => ClosingDate::all()->toArray(),
            'users' => User::select('id', 'name', 'email', 'is_admin', 'created_at')->get()->toArray(),
        ];
        
        file_put_contents($backupPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return "backup_{$timestamp}.json";
    }

    public function downloadBackup($filename)
    {
        $path = storage_path("app/backups/{$filename}");
        
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'バックアップファイルが見つかりません。');
        }
        
        return response()->download($path);
    }
}