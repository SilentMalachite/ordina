<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/products');
        $response->assertRedirect('/login');
        
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_products_index()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        
        // デバッグ情報を出力
        echo "User ID: " . $user->id . PHP_EOL;
        echo "User roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
        echo "User permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . PHP_EOL;
        
        // ロールの権限も確認
        $role = $user->roles->first();
        if ($role) {
            echo "Role permissions: " . $role->permissions->pluck('name')->implode(', ') . PHP_EOL;
        }
        
        // 権限チェックのテスト
        echo "Can product-list: " . ($user->can('product-list') ? 'true' : 'false') . PHP_EOL;
        
        // データベースの状態も確認
        echo "Users in DB: " . \App\Models\User::count() . PHP_EOL;
        echo "Roles in DB: " . \Spatie\Permission\Models\Role::count() . PHP_EOL;
        echo "Permissions in DB: " . \Spatie\Permission\Models\Permission::count() . PHP_EOL;
        
        $response = $this->actingAs($user)->get('/products');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = $this->createUserWithRole('閲覧者');
        
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }
}