<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Exports\SalesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\CustomerReportExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_export_collection()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->count(3)->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'transaction_date' => now()->subDays(5)
        ]);
        
        $export = new SalesReportExport(
            now()->subDays(10)->format('Y-m-d'),
            now()->format('Y-m-d')
        );
        
        $collection = $export->collection();
        $this->assertCount(3, $collection);
    }

    public function test_sales_report_export_headings()
    {
        $export = new SalesReportExport('2024-01-01', '2024-01-31');
        $headings = $export->headings();
        
        $expectedHeadings = [
            '取引ID',
            '取引日',
            '商品コード',
            '商品名',
            '顧客名',
            '顧客会社',
            '数量',
            '単価',
            '合計金額',
            '担当者',
            '備考'
        ];
        
        $this->assertEquals($expectedHeadings, $headings);
    }

    public function test_sales_report_export_mapping()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $customer = Customer::factory()->create(['name' => 'テスト顧客', 'company_name' => 'テスト会社']);
        $product = Product::factory()->create(['product_code' => 'TEST001', 'name' => 'テスト商品']);
        
        $transaction = Transaction::factory()->create([
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
            'unit_price' => 1000,
            'total_amount' => 5000,
            'transaction_date' => Carbon::parse('2024-01-15'),
            'notes' => 'テスト取引'
        ]);
        
        $export = new SalesReportExport('2024-01-01', '2024-01-31');
        $mapped = $export->map($transaction);
        
        $this->assertEquals($transaction->id, $mapped[0]);
        $this->assertEquals('2024-01-15', $mapped[1]);
        $this->assertEquals('TEST001', $mapped[2]);
        $this->assertEquals('テスト商品', $mapped[3]);
        $this->assertEquals('テスト顧客', $mapped[4]);
        $this->assertEquals('テスト会社', $mapped[5]);
        $this->assertEquals(5, $mapped[6]);
        $this->assertEquals('1,000', $mapped[7]);
        $this->assertEquals('5,000', $mapped[8]);
        $this->assertEquals('テストユーザー', $mapped[9]);
        $this->assertEquals('テスト取引', $mapped[10]);
    }

    public function test_inventory_report_export_collection()
    {
        Product::factory()->count(5)->create();
        
        $export = new InventoryReportExport();
        $collection = $export->collection();
        
        $this->assertCount(5, $collection);
    }

    public function test_inventory_report_export_with_low_stock_filter()
    {
        Product::factory()->count(3)->create(['stock_quantity' => 5]); // Low stock
        Product::factory()->count(2)->create(['stock_quantity' => 15]); // Normal stock
        
        $export = new InventoryReportExport(true); // Low stock only
        $collection = $export->collection();
        
        $this->assertCount(3, $collection);
    }

    public function test_inventory_report_export_headings()
    {
        $export = new InventoryReportExport();
        $headings = $export->headings();
        
        $expectedHeadings = [
            '商品ID',
            '商品コード',
            '商品名',
            '在庫数',
            '単価',
            '売値',
            '在庫金額',
            '最終更新日',
            '説明'
        ];
        
        $this->assertEquals($expectedHeadings, $headings);
    }

    public function test_customer_report_export_collection()
    {
        Customer::factory()->count(3)->create();
        
        $export = new CustomerReportExport();
        $collection = $export->collection();
        
        $this->assertCount(3, $collection);
    }

    public function test_customer_report_export_headings()
    {
        $export = new CustomerReportExport();
        $headings = $export->headings();
        
        $expectedHeadings = [
            '顧客ID',
            '顧客名',
            '会社名',
            'メールアドレス',
            '電話番号',
            '住所',
            '取引回数',
            '取引総額',
            '最終取引日',
            '登録日',
            '備考'
        ];
        
        $this->assertEquals($expectedHeadings, $headings);
    }

    public function test_customer_report_export_mapping()
    {
        $customer = Customer::factory()->create([
            'name' => 'テスト顧客',
            'company_name' => 'テスト会社',
            'email' => 'test@example.com',
            'phone' => '090-1234-5678',
            'address' => 'テスト住所'
        ]);
        
        $export = new CustomerReportExport();
        $mapped = $export->map($customer);
        
        $this->assertEquals($customer->id, $mapped[0]);
        $this->assertEquals('テスト顧客', $mapped[1]);
        $this->assertEquals('テスト会社', $mapped[2]);
        $this->assertEquals('test@example.com', $mapped[3]);
        $this->assertEquals('090-1234-5678', $mapped[4]);
        $this->assertEquals('テスト住所', $mapped[5]);
    }

    public function test_export_titles()
    {
        $salesExport = new SalesReportExport('2024-01-01', '2024-01-31');
        $this->assertEquals('売上レポート', $salesExport->title());
        
        $inventoryExport = new InventoryReportExport();
        $this->assertEquals('在庫レポート', $inventoryExport->title());
        
        $lowStockExport = new InventoryReportExport(true);
        $this->assertEquals('低在庫レポート', $lowStockExport->title());
        
        $customerExport = new CustomerReportExport();
        $this->assertEquals('顧客レポート', $customerExport->title());
    }
}