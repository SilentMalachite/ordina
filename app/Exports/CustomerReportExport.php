<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Customer::withCount('transactions')
            ->withSum('transactions', 'total_amount')
            ->orderBy('transactions_sum_total_amount', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($customer): array
    {
        $lastTransaction = $customer->transactions()->latest()->first();
        
        return [
            $customer->id,
            $customer->name,
            $customer->company_name ?? '',
            $customer->email,
            $customer->phone ?? '',
            $customer->address ?? '',
            $customer->transactions_count,
            number_format($customer->transactions_sum_total_amount ?? 0),
            $lastTransaction ? $lastTransaction->transaction_date->format('Y-m-d') : '',
            $customer->created_at->format('Y-m-d'),
            $customer->notes ?? ''
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
            'A' => 10, // 顧客ID
            'B' => 20, // 顧客名
            'C' => 20, // 会社名
            'D' => 25, // メールアドレス
            'E' => 15, // 電話番号
            'F' => 30, // 住所
            'G' => 12, // 取引回数
            'H' => 15, // 取引総額
            'I' => 15, // 最終取引日
            'J' => 12, // 登録日
            'K' => 30, // 備考
        ];
    }

    public function title(): string
    {
        return '顧客レポート';
    }
}