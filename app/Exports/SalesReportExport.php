<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $customerId;

    public function __construct($startDate, $endDate, $customerId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customerId = $customerId;
    }

    public function collection()
    {
        $query = Transaction::with(['product', 'customer', 'user'])
            ->where('type', 'sale')
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate]);

        if ($this->customerId) {
            $query->where('customer_id', $this->customerId);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->transaction_date->format('Y-m-d'),
            $transaction->product->product_code,
            $transaction->product->name,
            $transaction->customer->name,
            $transaction->customer->company_name ?? '',
            $transaction->quantity,
            number_format($transaction->unit_price),
            number_format($transaction->total_amount),
            $transaction->user->name,
            $transaction->notes ?? ''
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
            'A' => 10, // 取引ID
            'B' => 12, // 取引日
            'C' => 15, // 商品コード
            'D' => 25, // 商品名
            'E' => 20, // 顧客名
            'F' => 20, // 顧客会社
            'G' => 8,  // 数量
            'H' => 12, // 単価
            'I' => 12, // 合計金額
            'J' => 15, // 担当者
            'K' => 30, // 備考
        ];
    }

    public function title(): string
    {
        return '売上レポート';
    }
}