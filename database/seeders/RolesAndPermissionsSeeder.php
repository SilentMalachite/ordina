<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // キャッシュされたロールと権限をリセット
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 権限の定義
        $permissions = [
            // 商品管理
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            
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
            
            // 在庫管理
            'inventory-list',
            'inventory-adjust',
            
            // レポート
            'report-view',
            'report-export',
            
            // インポート
            'import-run',
            
            // システム管理
            'system-manage',
            'user-manage',
            'role-manage',
            'closing-date-manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ロールの定義と権限の割り当て

        // 閲覧者ロール
        $viewer = Role::create(['name' => '閲覧者']);
        $viewer->givePermissionTo([
            'product-list',
            'customer-list',
            'transaction-list',
            'inventory-list',
            'report-view'
        ]);

        // 一般スタッフロール
        $staff = Role::create(['name' => '一般スタッフ']);
        $staff->givePermissionTo([
            'product-list',
            'product-create',
            'customer-list',
            'customer-create',
            'transaction-list',
            'transaction-create',
            'inventory-list',
            'inventory-adjust',
            'report-view'
        ]);

        // マネージャーロール
        $manager = Role::create(['name' => 'マネージャー']);
        $manager->givePermissionTo([
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'transaction-list',
            'transaction-create',
            'transaction-edit',
            'transaction-delete',
            'inventory-list',
            'inventory-adjust',
            'report-view',
            'report-export',
            'import-run'
        ]);

        // 管理者ロール（全権限はAuthServiceProviderで処理）
        $admin = Role::create(['name' => '管理者']);
        // 管理者には明示的に権限を付与せず、Gate::beforeで全権限を許可する
    }
}