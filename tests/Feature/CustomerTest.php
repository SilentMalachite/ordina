<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => '一般スタッフ']);
        $permissions = [
            'customer-list',
            'customer-create',
            'customer-edit',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('一般スタッフ');
    }

    public function test_authenticated_user_can_access_customers_index()
    {
        Customer::factory()->count(5)->create();
        
        $response = $this->actingAs($this->user)->get('/customers');
        $response->assertStatus(200);
        $response->assertViewHas('customers');
    }

    public function test_user_can_create_customer()
    {
        $response = $this->actingAs($this->user)->get('/customers/create');
        $response->assertStatus(200);
        
        $customerData = [
            'name' => 'テスト顧客',
            'type' => 'individual',
            'email' => 'test@example.com',
            'phone' => '03-1234-5678',
            'address' => '東京都千代田区',
            'notes' => 'テストノート'
        ];
        
        $response = $this->actingAs($this->user)->post('/customers', $customerData);
        
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'テスト顧客',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_update_customer()
    {
        $customer = Customer::factory()->create();
        
        $response = $this->actingAs($this->user)->get("/customers/{$customer->id}/edit");
        $response->assertStatus(200);
        
        $updatedData = [
            'name' => '更新された顧客名',
            'type' => 'company',
            'email' => 'updated@example.com',
            'phone' => '090-1234-5678',
            'address' => '更新された住所',
            'contact_person' => '担当者名',
            'notes' => '更新されたノート'
        ];
        
        $response = $this->actingAs($this->user)->put("/customers/{$customer->id}", $updatedData);
        
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => '更新された顧客名',
            'email' => 'updated@example.com'
        ]);
    }
}