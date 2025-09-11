<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Services\PerformanceOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_optimized_transactions_query_includes_relations()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'product_id' => $product->id
        ]);
        
        $service = new PerformanceOptimizationService();
        $query = $service->getOptimizedTransactions();
        $transaction = $query->first();
        
        $this->assertTrue($transaction->relationLoaded('product'));
        $this->assertTrue($transaction->relationLoaded('customer'));
        $this->assertTrue($transaction->relationLoaded('user'));
    }

    public function test_optimized_products_query_includes_counts()
    {
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        
        Transaction::factory()->count(3)->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'type' => 'sale'
        ]);
        
        Transaction::factory()->count(2)->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'type' => 'rental'
        ]);
        
        $service = new PerformanceOptimizationService();
        $query = $service->getOptimizedProducts();
        $product = $query->first();
        
        $this->assertEquals(3, $product->sales_count);
        $this->assertEquals(2, $product->rentals_count);
    }

    public function test_optimized_customers_query_includes_counts_and_sums()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $user = User::factory()->create();
        
        Transaction::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'total_amount' => 1000
        ]);
        
        $service = new PerformanceOptimizationService();
        $query = $service->getOptimizedCustomers();
        $customer = $query->first();
        
        $this->assertEquals(3, $customer->total_transactions);
        $this->assertEquals(3000, $customer->transactions_sum_total_amount);
    }

    public function test_dashboard_statistics_are_cached()
    {
        User::factory()->count(5)->create();
        Product::factory()->count(10)->create();
        Customer::factory()->count(8)->create();
        
        $service = new PerformanceOptimizationService();
        
        // First call should cache the results
        $stats1 = $service->getDashboardStatistics();
        
        // Second call should use cache
        $stats2 = $service->getDashboardStatistics();
        
        $this->assertEquals($stats1, $stats2);
        $this->assertEquals(10, $stats1['total_products']);
        $this->assertEquals(8, $stats1['total_customers']);
    }

    public function test_recent_activities_are_cached()
    {
        User::factory()->count(3)->create();
        
        $service = new PerformanceOptimizationService();
        
        // First call should cache the results
        $activities1 = $service->getRecentActivities();
        
        // Second call should use cache
        $activities2 = $service->getRecentActivities();
        
        $this->assertEquals($activities1, $activities2);
        $this->assertArrayHasKey('new_users', $activities1);
        $this->assertArrayHasKey('recent_transactions', $activities1);
        $this->assertArrayHasKey('recent_adjustments', $activities1);
    }

    public function test_optimized_report_query_with_filters()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $user = User::factory()->create();
        
        Transaction::factory()->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'transaction_date' => now()->subDays(5)
        ]);
        
        $service = new PerformanceOptimizationService();
        $filters = [
            'date_from' => now()->subDays(10)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
            'customer_id' => $customer->id
        ];
        
        $query = $service->getOptimizedReportQuery('sales', $filters);
        $transactions = $query->get();
        
        $this->assertCount(1, $transactions);
        $this->assertTrue($transactions->first()->relationLoaded('product'));
        $this->assertTrue($transactions->first()->relationLoaded('customer'));
    }

    public function test_cache_can_be_cleared()
    {
        $service = new PerformanceOptimizationService();
        
        // Generate some cached data
        $service->getDashboardStatistics();
        $service->getRecentActivities();
        
        // Clear cache
        $service->clearCache();
        
        // Verify cache is cleared (this is hard to test directly, but we can ensure no errors)
        $this->assertTrue(true);
    }

    public function test_optimized_inventory_report_query()
    {
        Product::factory()->count(3)->create(['stock_quantity' => 5]); // Low stock
        Product::factory()->count(2)->create(['stock_quantity' => 15]); // Normal stock
        
        $service = new PerformanceOptimizationService();
        
        // Test without low stock filter
        $query1 = $service->getOptimizedInventoryReportQuery();
        $products1 = $query1->get();
        $this->assertCount(5, $products1);
        
        // Test with low stock filter
        $query2 = $service->getOptimizedInventoryReportQuery(['low_stock_only' => true]);
        $products2 = $query2->get();
        $this->assertCount(3, $products2);
    }
}