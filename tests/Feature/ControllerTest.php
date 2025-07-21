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
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_products_index()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/products');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }
}