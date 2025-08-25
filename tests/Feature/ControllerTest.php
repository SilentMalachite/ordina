<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $roleAdmin = Role::create(['name' => '管理者']);
        $roleStaff = Role::create(['name' => '一般スタッフ']);

        $permission = Permission::create(['name' => 'product-list']);
        $roleStaff->givePermissionTo($permission);
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/products');
        $response->assertRedirect('/login');
        
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_products_index()
    {
        $user = User::factory()->create();
        $user->assignRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/products');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('管理者');
        
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }
}