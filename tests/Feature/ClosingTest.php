<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClosingDate;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Services\ClosingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ClosingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_closing_index()
    {
        $user = User::factory()->create();
        ClosingDate::factory()->create();
        
        $response = $this->actingAs($user)->get('/closing');
        $response->assertStatus(200);
        $response->assertViewHas(['closing_dates', 'next_closing_date', 'closing_history']);
    }

    public function test_user_can_preview_closing_data()
    {
        $user = User::factory()->create();
        $closingDate = ClosingDate::factory()->create(['day_of_month' => 25]);
        
        // Create some test data
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'transaction_date' => now()->subDays(5)
        ]);
        
        $response = $this->actingAs($user)->get('/closing/show', [
            'closing_date_id' => $closingDate->id,
            'closing_date' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200);
        $response->assertViewHas('data');
    }

    public function test_user_can_process_closing()
    {
        $user = User::factory()->create();
        $closingDate = ClosingDate::factory()->create(['day_of_month' => 25]);
        
        $response = $this->actingAs($user)->post('/closing/process', [
            'closing_date_id' => $closingDate->id,
            'closing_date' => now()->format('Y-m-d'),
            'confirmation' => true
        ]);
        
        $response->assertRedirect(route('closing.index'));
        $response->assertSessionHas('success');
    }

    public function test_closing_validation_requires_confirmation()
    {
        $user = User::factory()->create();
        $closingDate = ClosingDate::factory()->create();
        
        $response = $this->actingAs($user)->post('/closing/process', [
            'closing_date_id' => $closingDate->id,
            'closing_date' => now()->format('Y-m-d'),
            'confirmation' => false // Not confirmed
        ]);
        
        $response->assertSessionHasErrors(['confirmation']);
    }

    public function test_closing_service_calculates_period_correctly()
    {
        $closingDate = ClosingDate::factory()->create(['day_of_month' => 25]);
        $service = new ClosingService();
        
        $result = $service->processClosing($closingDate->id, Carbon::parse('2024-01-25'));
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('period', $result['data']);
        $this->assertArrayHasKey('start', $result['data']['period']);
        $this->assertArrayHasKey('end', $result['data']['period']);
    }

    public function test_closing_service_gets_closing_dates()
    {
        ClosingDate::factory()->count(3)->create();
        
        $service = new ClosingService();
        $closingDates = $service->getClosingDates();
        
        $this->assertCount(3, $closingDates);
    }

    public function test_closing_service_gets_next_closing_date()
    {
        ClosingDate::factory()->create(['day_of_month' => 25]);
        
        $service = new ClosingService();
        $nextClosingDate = $service->getNextClosingDate();
        
        $this->assertNotNull($nextClosingDate);
        $this->assertInstanceOf(Carbon::class, $nextClosingDate);
    }

    public function test_closing_history_can_be_accessed()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/closing/history');
        $response->assertStatus(200);
        $response->assertViewHas('history');
    }

    public function test_closing_process_with_transaction_data()
    {
        $user = User::factory()->create();
        $closingDate = ClosingDate::factory()->create(['day_of_month' => 25]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        // Create transactions in the closing period
        Transaction::factory()->count(3)->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'transaction_date' => now()->subDays(10)
        ]);
        
        $response = $this->actingAs($user)->get('/closing/show', [
            'closing_date_id' => $closingDate->id,
            'closing_date' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200);
        $data = $response->viewData('data');
        $this->assertArrayHasKey('totals', $data);
        $this->assertArrayHasKey('sales_count', $data['totals']);
    }
}