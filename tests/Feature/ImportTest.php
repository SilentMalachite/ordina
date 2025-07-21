<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_import_index()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import');
        $response->assertStatus(200);
        $response->assertSee('データインポート');
    }

    public function test_user_can_access_products_import_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/products');
        $response->assertStatus(200);
        $response->assertSee('商品データインポート');
    }

    public function test_user_can_access_customers_import_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/customers');
        $response->assertStatus(200);
        $response->assertSee('顧客データインポート');
    }

    public function test_user_can_access_transactions_import_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/transactions');
        $response->assertStatus(200);
        $response->assertSee('取引データインポート');
    }

    public function test_user_can_download_product_template()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/template/products');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="product_template.csv"');
    }

    public function test_user_can_download_customer_template()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/template/customers');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="customer_template.csv"');
    }

    public function test_user_can_download_transaction_template()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/import/template/transactions');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="transaction_template.csv"');
    }

    public function test_user_can_import_products_csv()
    {
        $user = User::factory()->create();
        
        $csvContent = "商品コード,商品名,在庫数,単価,売値,説明\n";
        $csvContent .= "PRD-001,テスト商品1,100,1000,1500,テスト説明1\n";
        $csvContent .= "PRD-002,テスト商品2,50,2000,3000,テスト説明2\n";
        
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            $csvContent
        );
        
        $response = $this->actingAs($user)->post('/import/products', [
            'file' => $file,
            'has_header' => true,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'product_code' => 'PRD-001',
            'name' => 'テスト商品1',
            'stock_quantity' => 100
        ]);
        
        $this->assertDatabaseHas('products', [
            'product_code' => 'PRD-002',
            'name' => 'テスト商品2',
            'stock_quantity' => 50
        ]);
    }
}