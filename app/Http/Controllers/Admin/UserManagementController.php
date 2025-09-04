<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:user-manage');
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * ユーザー一覧を表示
     */
    public function index()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return User::orderBy('created_at', 'desc')->paginate(20);
        }, 'ユーザー一覧の取得');

        if ($result['success']) {
            $users = $result['data'];
            return view('admin.users', compact('users'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * ユーザー作成フォームを表示
     */
    public function create()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return Role::all();
        }, 'ロール一覧の取得');

        if ($result['success']) {
            $roles = $result['data'];
            return view('admin.create-user', compact('roles'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * ユーザーを保存
     */
    public function store(Request $request)
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

        $result = $this->errorService->safeDatabaseOperation(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->role === '管理者',
                'email_verified_at' => now(),
            ]);
            
            $user->assignRole($request->role);
            return $user;
        }, 'ユーザーの作成');

        if ($result['success']) {
            return redirect()->route('admin.users')
                ->with('success', 'ユーザーが正常に作成されました。');
        }

        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * ユーザー編集フォームを表示
     */
    public function edit(User $user)
    {
        $result = $this->errorService->safeDatabaseOperation(function() use ($user) {
            return [
                'roles' => Role::all(),
                'userRole' => $user->roles->first()
            ];
        }, 'ユーザー編集情報の取得');

        if ($result['success']) {
            $data = $result['data'];
            $roles = $data['roles'];
            $userRole = $data['userRole'];
            return view('admin.edit-user', compact('user', 'roles', 'userRole'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * ユーザーを更新
     */
    public function update(Request $request, User $user)
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

        $result = $this->errorService->safeDatabaseOperation(function() use ($request, $user) {
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
            
            return $user;
        }, 'ユーザーの更新');

        if ($result['success']) {
            return redirect()->route('admin.users')
                ->with('success', 'ユーザー情報が正常に更新されました。');
        }

        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * ユーザーを削除
     */
    public function destroy(User $user)
    {
        // 自分自身のアカウントは削除できない
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', '自分自身のアカウントは削除できません。');
        }

        $result = $this->errorService->safeDatabaseOperation(function() use ($user) {
            // 関連するデータがあるかチェック
            if ($user->transactions()->exists() || $user->inventoryAdjustments()->exists()) {
                throw new \Exception('このユーザーには関連する取引データがあるため削除できません。');
            }

            $user->delete();
            return true;
        }, 'ユーザーの削除');

        if ($result['success']) {
            return redirect()->route('admin.users')
                ->with('success', 'ユーザーが正常に削除されました。');
        }

        return redirect()->back()->with('error', $result['message']);
    }
}