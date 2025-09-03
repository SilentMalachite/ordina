<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SummaryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // サマリーデータを生成
        $summaryData = collect();

        // 期間内の売上統計
        $salesStats = Transaction::where('type', 'sale')
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->selectRaw('
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_transaction
            ')
            ->first();

        // 期間内の貸出統計
        $rentalStats = Transaction::where('type', 'rental')
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->selectRaw('
                COUNT(*) as rental_count,
                SUM(quantity) as total_rental_quantity,
                SUM(total_amount) as total_rental_amount
            ')
            ->first();

        // 在庫統計
        $inventoryStats = Product::selectRaw('
            COUNT(*) as total_products,
            SUM(stock_quantity) as total_stock,
            SUM(stock_quantity * unit_price) as total_inventory_value,
            COUNT(CASE WHEN stock_quantity <= 10 THEN 1 END) as low_stock_count
        ')->first();

        // 顧客統計
        $customerStats = Customer::selectRaw('
            COUNT(*) as total_customers,
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_customers
        ', [$this->startDate])->first();

        $summaryData->push([
            'category' => '売上統計',
            'item' => '取引件数',
            'value' => $salesStats->transaction_count ?? 0,
            'unit' => '件'
        ]);

        $summaryData->push([
            'category' => '売上統計',
            'item' => '売上数量',
            'value' => $salesStats->total_quantity ?? 0,
            'unit' => '個'
        ]);

        $summaryData->push([
            'category' => '売上統計',
            'item' => '売上総額',
            'value' => $salesStats->total_sales ?? 0,
            'unit' => '円'
        ]);

        $summaryData->push([
            'category' => '売上統計',
            'item' => '平均取引額',
            'value' => round($salesStats->average_transaction ?? 0),
            'unit' => '円'
        ]);

        $summaryData->push([
            'category' => '貸出統計',
            'item' => '貸出件数',
            'value' => $rentalStats->rental_count ?? 0,
            'unit' => '件'
        ]);

        $summaryData->push([
            'category' => '貸出統計',
            'item' => '貸出数量',
            'value' => $rentalStats->total_rental_quantity ?? 0,
            'unit' => '個'
        ]);

        $summaryData->push([
            'category' => '貸出統計',
            'item' => '貸出総額',
            'value' => $rentalStats->total_rental_amount ?? 0,
            'unit' => '円'
        ]);

        $summaryData->push([
            'category' => '在庫統計',
            'item' => '商品総数',
            'value' => $inventoryStats->total_products ?? 0,
            'unit' => '種類'
        ]);

        $summaryData->push([
            'category' => '在庫統計',
            'item' => '在庫総数',
            'value' => $inventoryStats->total_stock ?? 0,
            'unit' => '個'
        ]);

        $summaryData->push([
            'category' => '在庫統計',
            'item' => '在庫評価額',
            'value' => $inventoryStats->total_inventory_value ?? 0,
            'unit' => '円'
        ]);

        $summaryData->push([
            'category' => '在庫統計',
            'item' => '低在庫商品数',
            'value' => $inventoryStats->low_stock_count ?? 0,
            'unit' => '種類'
        ]);

        $summaryData->push([
            'category' => '顧客統計',
            'item' => '顧客総数',
            'value' => $customerStats->total_customers ?? 0,
            'unit' => '人'
        ]);

        $summaryData->push([
            'category' => '顧客統計',
            'item' => '新規顧客数',
            'value' => $customerStats->new_customers ?? 0,
            'unit' => '人'
        ]);

        return $summaryData;
    }

    public function headings(): array
    {
        return [
            'カテゴリ',
            '項目',
            '値',
            '単位',
            '期間',
            '生成日時'
        ];
    }

    public function map($row): array
    {
        return [
            $row['category'],
            $row['item'],
            is_numeric($row['value']) ? number_format($row['value']) : $row['value'],
            $row['unit'],
            $this->startDate . ' ～ ' . $this->endDate,
            now()->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // ヘッダー行のスタイル
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // カテゴリ
            'B' => 20, // 項目
            'C' => 15, // 値
            'D' => 8,  // 単位
            'E' => 25, // 期間
            'F' => 18, // 生成日時
        ];
    }

    public function title(): string
    {
        return 'サマリーレポート';
    }
}