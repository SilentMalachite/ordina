<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_customers_index()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        Customer::factory()->count(5)->create();
        
        $response = $this->actingAs($user)->get('/customers');
        $response->assertStatus(200);
        $response->assertViewHas('customers');
    }

    public function test_user_can_create_customer()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/customers/create');
        $response->assertStatus(200);
        
        $customerData = [
            'name' => 'テスト顧客',
            'type' => 'individual',
            'email' => 'test@example.com',
            'phone' => '03-1234-5678',
            'address' => '東京都千代田区',
            'notes' => 'テストノート'
        ];
        
        $response = $this->actingAs($user)->post('/customers', $customerData);
        
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'テスト顧客',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_update_customer()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        $customer = Customer::factory()->create();
        
        $response = $this->actingAs($user)->get("/customers/{$customer->id}/edit");
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
        
        $response = $this->actingAs($user)->put("/customers/{$customer->id}", $updatedData);
        
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => '更新された顧客名',
            'email' => 'updated@example.com'
        ]);
    }
}