<?php

namespace Tests;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * テストの前処理
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト環境ではViteのマニフェストエラーを無視
        $this->withoutVite();
        
        // Spatie Permissionのキャッシュをクリア
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // テスト用の権限とロールを作成
        $this->createTestPermissionsAndRoles();
    }

    /**
     * テスト用の権限とロールを作成
     */
    private function createTestPermissionsAndRoles(): void
    {
        // 権限の作成
        $permissions = [
            'product-list', 'product-create', 'product-edit', 'product-delete',
            'customer-list', 'customer-create', 'customer-edit', 'customer-delete',
            'transaction-list', 'transaction-create', 'transaction-edit', 'transaction-delete', 'transaction-return',
            'inventory-view', 'inventory-adjust', 'inventory-bulk-adjust',
            'report-view', 'report-export', 'import-run',
            'system-manage', 'user-manage', 'role-manage', 'closing-date-manage',
            'log-view', 'log-manage', 'backup-view', 'backup-manage',
            'sync-conflicts-view', 'sync-conflicts-resolve',
            'api-token-view', 'api-token-create', 'api-token-edit', 'api-token-delete',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // ロールの作成と権限の割り当て
        $roles = [
            '閲覧者' => ['product-list', 'customer-list', 'transaction-list', 'inventory-view', 'report-view'],
            '一般スタッフ' => [
                'product-list', 'product-create', 'product-edit',
                'customer-list', 'customer-create', 'customer-edit',
                'transaction-list', 'transaction-create', 'transaction-edit', 'transaction-return',
                'inventory-view', 'inventory-adjust', 'report-view', 'report-export', 'import-run'
            ],
            'マネージャー' => [
                'product-list', 'product-create', 'product-edit', 'product-delete',
                'customer-list', 'customer-create', 'customer-edit', 'customer-delete',
                'transaction-list', 'transaction-create', 'transaction-edit', 'transaction-delete', 'transaction-return',
                'inventory-view', 'inventory-adjust', 'inventory-bulk-adjust',
                'report-view', 'report-export', 'import-run',
                'log-view', 'backup-view', 'sync-conflicts-view', 'api-token-view',
            ],
            '管理者' => $permissions, // 全権限
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }

    /**
     * テスト用のユーザーを作成し、指定されたロールを付与
     */
    protected function createUserWithRole(string $roleName = '一般スタッフ'): User
    {
        $user = User::factory()->create();
        
        // ロールが存在しない場合は作成
        $role = SpatieRole::firstOrCreate(['name' => $roleName]);
        
        // ユーザーにロールを付与
        $user->assignRole($role);
        
        // 権限キャッシュをクリア
        $user->forgetCachedPermissions();
        
        // データベースから再取得して権限を確実に反映
        $user = User::find($user->id);
        
        return $user;
    }

    /**
     * テスト用の管理者ユーザーを作成
     */
    protected function createAdminUser(): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        
        // 管理者ロールを付与
        $adminRole = SpatieRole::firstOrCreate(['name' => '管理者']);
        $user->assignRole($adminRole);
        
        // 権限キャッシュをクリア
        $user->forgetCachedPermissions();
        
        // データベースから再取得して権限を確実に反映
        $user = User::find($user->id);
        
        return $user;
    }

    /**
     * テスト用のマネージャーユーザーを作成
     */
    protected function createManagerUser(): User
    {
        return $this->createUserWithRole('マネージャー');
    }

    /**
     * テスト用の閲覧者ユーザーを作成
     */
    protected function createViewerUser(): User
    {
        return $this->createUserWithRole('閲覧者');
    }
}
