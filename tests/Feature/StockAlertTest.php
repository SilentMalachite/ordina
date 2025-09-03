<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Services\StockAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_stock_alerts()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/stock-alerts');
        $response->assertStatus(200);
        $response->assertViewHas(['low_stock_products', 'out_of_stock_products', 'statistics']);
    }

    public function test_low_stock_products_are_detected()
    {
        $user = User::factory()->create();
        
        // Create products with different stock levels
        Product::factory()->create(['stock_quantity' => 5]); // Low stock
        Product::factory()->create(['stock_quantity' => 15]); // Normal stock
        Product::factory()->create(['stock_quantity' => 0]); // Out of stock
        
        $response = $this->actingAs($user)->get('/stock-alerts');
        $response->assertStatus(200);
        
        $viewData = $response->viewData();
        $this->assertCount(1, $viewData['low_stock_products']);
        $this->assertCount(1, $viewData['out_of_stock_products']);
    }

    public function test_stock_alert_settings_can_be_accessed()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/stock-alerts/settings');
        $response->assertStatus(200);
        $response->assertViewHas(['statistics', 'alert_history']);
    }

    public function test_stock_alert_threshold_can_be_updated()
    {
        $user = User::factory()->create();
        
        $newThreshold = 20;
        
        $response = $this->actingAs($user)->post('/stock-alerts/settings', [
            'low_stock_threshold' => $newThreshold
        ]);
        
        $response->assertRedirect(route('stock-alerts.settings'));
        $response->assertSessionHas('success');
    }

    public function test_manual_stock_check_can_be_run()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/stock-alerts/run-check');
        
        $response->assertRedirect(route('stock-alerts.index'));
        $response->assertSessionHas('success');
    }

    public function test_stock_alert_statistics_api()
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create(['stock_quantity' => 5]);
        Product::factory()->count(2)->create(['stock_quantity' => 0]);
        
        $response = $this->actingAs($user)->get('/stock-alerts/statistics');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('total_products', $data);
        $this->assertArrayHasKey('low_stock_products', $data);
        $this->assertArrayHasKey('out_of_stock_products', $data);
        $this->assertEquals(5, $data['total_products']);
        $this->assertEquals(3, $data['low_stock_products']);
        $this->assertEquals(2, $data['out_of_stock_products']);
    }

    public function test_stock_alert_service_detects_low_stock()
    {
        Product::factory()->create(['stock_quantity' => 5]);
        Product::factory()->create(['stock_quantity' => 15]);
        
        $service = new StockAlertService();
        $lowStockProducts = $service->checkLowStock();
        
        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals(5, $lowStockProducts[0]['stock_quantity']);
    }

    public function test_stock_alert_service_detects_out_of_stock()
    {
        Product::factory()->create(['stock_quantity' => 0]);
        Product::factory()->create(['stock_quantity' => 5]);
        
        $service = new StockAlertService();
        $outOfStockProducts = $service->checkOutOfStock();
        
        $this->assertCount(1, $outOfStockProducts);
        $this->assertEquals(0, $outOfStockProducts[0]['stock_quantity']);
    }

    public function test_stock_alert_threshold_validation()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/stock-alerts/settings', [
            'low_stock_threshold' => 0 // Invalid: must be >= 1
        ]);
        
        $response->assertSessionHasErrors(['low_stock_threshold']);
    }
}