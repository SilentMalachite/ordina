<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $lowStockOnly;

    public function __construct($lowStockOnly = false)
    {
        $this->lowStockOnly = $lowStockOnly;
    }

    public function collection()
    {
        $query = Product::query();

        if ($this->lowStockOnly) {
            $query->where('stock_quantity', '<=', 10);
        }

        return $query->orderBy('stock_quantity', 'asc')->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($product): array
    {
        $stockValue = $product->stock_quantity * $product->unit_price;
        
        return [
            $product->id,
            $product->product_code,
            $product->name,
            $product->stock_quantity,
            number_format($product->unit_price),
            number_format($product->selling_price),
            number_format($stockValue),
            $product->updated_at->format('Y-m-d H:i:s'),
            $product->description ?? ''
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
            'A' => 10, // 商品ID
            'B' => 15, // 商品コード
            'C' => 25, // 商品名
            'D' => 10, // 在庫数
            'E' => 12, // 単価
            'F' => 12, // 売値
            'G' => 12, // 在庫金額
            'H' => 18, // 最終更新日
            'I' => 30, // 説明
        ];
    }

    public function title(): string
    {
        return $this->lowStockOnly ? '低在庫レポート' : '在庫レポート';
    }
}