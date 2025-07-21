<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:role-manage');
    }

    /**
     * ロール一覧を表示
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * 新規ロール作成フォームを表示
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            // 権限名からカテゴリを抽出
            $parts = explode('-', $permission->name);
            return $this->getPermissionCategory($parts[0]);
        });
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * 新規ロールを保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::transaction(function () use ($request) {
            $role = Role::create(['name' => $request->input('name')]);
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions'));
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'ロールを作成しました。');
    }

    /**
     * ロール編集フォームを表示
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        
        // デフォルトロールは編集不可
        if ($this->isDefaultRole($role->name)) {
            return redirect()->route('roles.index')
                ->with('error', 'デフォルトロールは編集できません。');
        }
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return $this->getPermissionCategory($parts[0]);
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * ロールを更新
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        // デフォルトロールは編集不可
        if ($this->isDefaultRole($role->name)) {
            return redirect()->route('roles.index')
                ->with('error', 'デフォルトロールは編集できません。');
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->input('name')]);
            $role->syncPermissions($request->input('permissions', []));
        });

        return redirect()->route('roles.index')
            ->with('success', 'ロールを更新しました。');
    }

    /**
     * ロールを削除
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // デフォルトロールは削除不可
        if ($this->isDefaultRole($role->name)) {
            return redirect()->route('roles.index')
                ->with('error', 'デフォルトロールは削除できません。');
        }
        
        // 使用中のロールは削除不可
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'このロールはユーザーに割り当てられているため削除できません。');
        }
        
        $role->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'ロールを削除しました。');
    }

    /**
     * デフォルトロールかどうかを判定
     */
    private function isDefaultRole($roleName)
    {
        return in_array($roleName, ['管理者', 'マネージャー', '一般スタッフ', '閲覧者']);
    }

    /**
     * 権限カテゴリを取得
     */
    private function getPermissionCategory($prefix)
    {
        $categories = [
            'product' => '商品管理',
            'customer' => '顧客管理',
            'transaction' => '取引管理',
            'inventory' => '在庫管理',
            'report' => 'レポート',
            'import' => 'インポート',
            'system' => 'システム管理',
            'user' => 'ユーザー管理',
            'role' => 'ロール管理',
            'closing' => '締め日管理'
        ];
        
        return $categories[$prefix] ?? 'その他';
    }
}
