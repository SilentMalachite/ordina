<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\ClosingDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_reports_index()
    {
        $user = User::factory()->create();
        ClosingDate::factory()->count(3)->create();
        
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
        $response->assertViewHas('closingDates');
    }

    public function test_user_can_access_sales_report()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->count(5)->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->get('/reports/sales');
        $response->assertStatus(200);
        $response->assertViewHas(['transactions', 'groupedData', 'productSummary', 'customerSummary']);
    }

    public function test_user_can_access_rental_report()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->count(3)->rental()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->get('/reports/rentals');
        $response->assertStatus(200);
        $response->assertViewHas(['transactions', 'groupedData', 'productSummary', 'customerSummary']);
    }

    public function test_user_can_access_inventory_report()
    {
        $user = User::factory()->create();
        Product::factory()->count(10)->create();
        
        $response = $this->actingAs($user)->get('/reports/inventory');
        $response->assertStatus(200);
        $response->assertViewHas(['products', 'totalStockValue', 'lowStockCount', 'totalProducts']);
    }

    public function test_user_can_access_customer_report()
    {
        $user = User::factory()->create();
        $customers = Customer::factory()->count(5)->create();
        
        foreach ($customers as $customer) {
            Transaction::factory()->count(2)->create([
                'customer_id' => $customer->id,
                'user_id' => $user->id
            ]);
        }
        
        $response = $this->actingAs($user)->get('/reports/customers');
        $response->assertStatus(200);
        $response->assertViewHas('customerStats');
    }

    public function test_user_can_export_sales_report()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->count(3)->sale()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->get('/reports/export/sales');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    public function test_user_can_export_inventory_report()
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create();
        
        $response = $this->actingAs($user)->get('/reports/export/inventory');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }
}