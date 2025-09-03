<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class ComprehensiveReportExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        $sheets = [];

        // 売上レポート
        $sheets[] = new SalesReportExport($this->startDate, $this->endDate, $this->customerId);

        // 在庫レポート
        $sheets[] = new InventoryReportExport();

        // 低在庫レポート
        $sheets[] = new InventoryReportExport(true);

        // 顧客レポート
        $sheets[] = new CustomerReportExport();

        // サマリーレポート
        $sheets[] = new SummaryReportExport($this->startDate, $this->endDate);

        return $sheets;
    }
}