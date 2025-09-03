<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * ユーザーが管理者かどうかを判定
     */
    public function isAdmin(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        // Spatie Permissionのロールを優先
        if ($user->hasRole('管理者')) {
            return true;
        }

        // フォールバックとしてis_adminフラグをチェック
        return $user->is_admin ?? false;
    }

    /**
     * ユーザーに管理者権限を付与
     */
    public function grantAdminRole(User $user): void
    {
        $user->assignRole('管理者');
        $user->update(['is_admin' => true]);
    }

    /**
     * ユーザーから管理者権限を削除
     */
    public function revokeAdminRole(User $user): void
    {
        $user->removeRole('管理者');
        $user->update(['is_admin' => false]);
    }

    /**
     * ユーザーが特定の権限を持っているかチェック
     */
    public function hasPermission(User $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        // 管理者は全ての権限を持つ
        if ($this->isAdmin($user)) {
            return true;
        }

        // Spatie Permissionでチェック
        return $user->can($permission);
    }

    /**
     * ユーザーが特定のロールを持っているかチェック
     */
    public function hasRole(User $user, string $role): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * 権限チェック用のミドルウェアヘルパー
     */
    public function checkPermission(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $this->hasPermission($user, $permission);
    }

    /**
     * ロールチェック用のミドルウェアヘルパー
     */
    public function checkRole(string $role): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $this->hasRole($user, $role);
    }

    /**
     * ユーザーの権限を同期（is_adminフラグとSpatie Permissionを同期）
     */
    public function syncUserPermissions(User $user): void
    {
        $isAdmin = $this->isAdmin($user);
        
        if ($isAdmin && !$user->hasRole('管理者')) {
            $user->assignRole('管理者');
        } elseif (!$isAdmin && $user->hasRole('管理者')) {
            $user->removeRole('管理者');
        }
    }

    /**
     * 全てのユーザーの権限を同期
     */
    public function syncAllUserPermissions(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $this->syncUserPermissions($user);
        }
    }

    /**
     * 権限の初期化
     */
    public function initializePermissions(): void
    {
        // 権限の作成（Seeder と整合性を取る）
        $permissions = [
            // 商品管理
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            
            // 在庫管理
            'inventory-view',
            'inventory-adjust',
            'inventory-bulk-adjust',
            
            // 顧客管理
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            
            // 取引管理
            'transaction-list',
            'transaction-create',
            'transaction-edit',
            'transaction-delete',
            'transaction-return',
            
            // レポート
            'report-view',
            'report-export',
            
            // インポート（単一権限に統一）
            'import-run',
            
            // システム管理
            'system-manage',
            'user-manage',
            'role-manage',
            'closing-date-manage',

            // ログ・バックアップ
            'log-view',
            'log-manage',
            'backup-view',
            'backup-manage',

            // 同期競合
            'sync-conflicts-view',
            'sync-conflicts-resolve',

            // APIトークン管理
            'api-token-view',
            'api-token-create',
            'api-token-edit',
            'api-token-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ロールの作成
        $roles = [
            '管理者' => [
                'product-list', 'product-create', 'product-edit', 'product-delete',
                'inventory-view', 'inventory-adjust', 'inventory-bulk-adjust',
                'customer-list', 'customer-create', 'customer-edit', 'customer-delete',
                'transaction-list', 'transaction-create', 'transaction-edit', 'transaction-delete', 'transaction-return',
                'report-view', 'report-export',
                'import-run',
                'system-manage', 'user-manage', 'role-manage', 'closing-date-manage',
                // ログ・バックアップ（管理者は両方）
                'log-view', 'log-manage', 'backup-view', 'backup-manage',
                // 同期競合
                'sync-conflicts-view', 'sync-conflicts-resolve',
                // APIトークン
                'api-token-view', 'api-token-create', 'api-token-edit', 'api-token-delete',
            ],
            '一般ユーザー' => [
                'product-list', 'product-create', 'product-edit',
                'inventory-view', 'inventory-adjust',
                'customer-list', 'customer-create', 'customer-edit',
                'transaction-list', 'transaction-create', 'transaction-edit', 'transaction-return',
                'report-view', 'report-export', 'import-run',
                // 必要に応じて閲覧だけ付与する場合は下記を有効化
                // 'log-view', 'backup-view',
            ],
            '閲覧者' => [
                'product-list',
                'inventory-view',
                'customer-list',
                'transaction-list',
                'report-view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }

    /**
     * ユーザーの権限情報を取得
     */
    public function getUserPermissions(User $user): array
    {
        return [
            'is_admin' => $this->isAdmin($user),
            'roles' => $user->roles->pluck('name')->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }
}
