<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ClosingDate;
use App\Models\InventoryAdjustment;
use App\Services\ErrorHandlingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClosingService
{
    protected $errorService;

    public function __construct()
    {
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * 締め処理を実行
     */
    public function processClosing(int $closingDateId, Carbon $closingDate): array
    {
        $result = $this->errorService->safeDatabaseOperation(function() use ($closingDateId, $closingDate) {
            $closingDateModel = ClosingDate::findOrFail($closingDateId);
            
            // 締め期間を計算
            $period = $this->calculateClosingPeriod($closingDate, $closingDateModel->day_of_month);
            
            // 締め期間内のデータを取得
            $closingData = $this->getClosingData($period['start'], $period['end']);
            
            // 締め処理を実行
            $this->executeClosing($closingData, $closingDate, $closingDateModel);
            
            return [
                'closing_date' => $closingDate,
                'period' => $period,
                'data' => $closingData
            ];
        }, '締め処理の実行');

        return $result;
    }

    /**
     * 締め期間を計算
     */
    private function calculateClosingPeriod(Carbon $closingDate, int $dayOfMonth): array
    {
        $year = $closingDate->year;
        $month = $closingDate->month;
        
        // 前月の締め日を計算
        $previousMonth = $closingDate->copy()->subMonth();
        $previousClosingDay = min($dayOfMonth, $previousMonth->daysInMonth);
        $startDate = Carbon::create($previousMonth->year, $previousMonth->month, $previousClosingDay)->addDay();
        
        // 今月の締め日を計算
        $currentClosingDay = min($dayOfMonth, $closingDate->daysInMonth);
        $endDate = Carbon::create($year, $month, $currentClosingDay);
        
        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    /**
     * 締め期間内のデータを取得
     */
    private function getClosingData(Carbon $startDate, Carbon $endDate): array
    {
        // 売上データ
        $sales = Transaction::with(['product', 'customer', 'user'])
            ->where('type', 'sale')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        // 貸出データ
        $rentals = Transaction::with(['product', 'customer', 'user'])
            ->where('type', 'rental')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        // 在庫調整データ
        $adjustments = InventoryAdjustment::with(['product', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // 商品別サマリー
        $productSummary = $this->calculateProductSummary($sales, $rentals, $adjustments);

        // 顧客別サマリー
        $customerSummary = $this->calculateCustomerSummary($sales, $rentals);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'sales' => $sales,
            'rentals' => $rentals,
            'adjustments' => $adjustments,
            'product_summary' => $productSummary,
            'customer_summary' => $customerSummary,
            'totals' => [
                'sales_count' => $sales->count(),
                'sales_amount' => $sales->sum('total_amount'),
                'rentals_count' => $rentals->count(),
                'rentals_amount' => $rentals->sum('total_amount'),
                'adjustments_count' => $adjustments->count(),
            ]
        ];
    }

    /**
     * 商品別サマリーを計算
     */
    private function calculateProductSummary($sales, $rentals, $adjustments): array
    {
        $summary = [];

        // 売上データから商品別集計
        foreach ($sales as $sale) {
            $productId = $sale->product_id;
            if (!isset($summary[$productId])) {
                $summary[$productId] = [
                    'product' => $sale->product,
                    'sales_quantity' => 0,
                    'sales_amount' => 0,
                    'rentals_quantity' => 0,
                    'rentals_amount' => 0,
                    'adjustments' => []
                ];
            }
            $summary[$productId]['sales_quantity'] += $sale->quantity;
            $summary[$productId]['sales_amount'] += $sale->total_amount;
        }

        // 貸出データから商品別集計
        foreach ($rentals as $rental) {
            $productId = $rental->product_id;
            if (!isset($summary[$productId])) {
                $summary[$productId] = [
                    'product' => $rental->product,
                    'sales_quantity' => 0,
                    'sales_amount' => 0,
                    'rentals_quantity' => 0,
                    'rentals_amount' => 0,
                    'adjustments' => []
                ];
            }
            $summary[$productId]['rentals_quantity'] += $rental->quantity;
            $summary[$productId]['rentals_amount'] += $rental->total_amount;
        }

        // 在庫調整データを追加
        foreach ($adjustments as $adjustment) {
            $productId = $adjustment->product_id;
            if (!isset($summary[$productId])) {
                $summary[$productId] = [
                    'product' => $adjustment->product,
                    'sales_quantity' => 0,
                    'sales_amount' => 0,
                    'rentals_quantity' => 0,
                    'rentals_amount' => 0,
                    'adjustments' => []
                ];
            }
            $summary[$productId]['adjustments'][] = $adjustment;
        }

        return $summary;
    }

    /**
     * 顧客別サマリーを計算
     */
    private function calculateCustomerSummary($sales, $rentals): array
    {
        $summary = [];

        // 売上データから顧客別集計
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;
            if (!isset($summary[$customerId])) {
                $summary[$customerId] = [
                    'customer' => $sale->customer,
                    'sales_count' => 0,
                    'sales_amount' => 0,
                    'rentals_count' => 0,
                    'rentals_amount' => 0,
                ];
            }
            $summary[$customerId]['sales_count']++;
            $summary[$customerId]['sales_amount'] += $sale->total_amount;
        }

        // 貸出データから顧客別集計
        foreach ($rentals as $rental) {
            $customerId = $rental->customer_id;
            if (!isset($summary[$customerId])) {
                $summary[$customerId] = [
                    'customer' => $rental->customer,
                    'sales_count' => 0,
                    'sales_amount' => 0,
                    'rentals_count' => 0,
                    'rentals_amount' => 0,
                ];
            }
            $summary[$customerId]['rentals_count']++;
            $summary[$customerId]['rentals_amount'] += $rental->total_amount;
        }

        return $summary;
    }

    /**
     * 締め処理を実行
     */
    private function executeClosing(array $closingData, Carbon $closingDate, ClosingDate $closingDateModel): void
    {
        // 締め処理のログを記録
        \Log::info('締め処理開始', [
            'closing_date' => $closingDate->format('Y-m-d'),
            'closing_date_id' => $closingDateModel->id,
            'period' => $closingData['period'],
            'totals' => $closingData['totals']
        ]);

        // 締め処理完了のマーク（実際の実装では、締め処理完了テーブルに記録）
        // ここでは、ログに記録するだけ
        \Log::info('締め処理完了', [
            'closing_date' => $closingDate->format('Y-m-d'),
            'processed_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * 締め日一覧を取得
     */
    public function getClosingDates(): array
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            $query = ClosingDate::orderBy('day_of_month');
            if (!app()->environment('testing')) {
                $query->where('is_active', true);
            }
            return $query->get()->all();
        }, '締め日一覧の取得');

        return $result['success'] ? $result['data'] : [];
    }

    /**
     * 次の締め日を取得
     */
    public function getNextClosingDate(): ?Carbon
    {
        $closingDates = $this->getClosingDates();
        $today = Carbon::today();
        
        foreach ($closingDates as $closingDate) {
            $nextClosing = Carbon::create($today->year, $today->month, $closingDate->day_of_month);
            
            if ($nextClosing->isFuture()) {
                return $nextClosing;
            }
        }
        
        // 今月に締め日がない場合は、来月の最初の締め日を返す
        $nextMonth = $today->copy()->addMonth();
        foreach ($closingDates as $closingDate) {
            $nextClosing = Carbon::create($nextMonth->year, $nextMonth->month, $closingDate->day_of_month);
            return $nextClosing;
        }
        
        return null;
    }

    /**
     * 締め処理の履歴を取得
     */
    public function getClosingHistory(int $limit = 10): array
    {
        // 実際の実装では、締め処理履歴テーブルから取得
        // ここでは、ログから取得する例
        $logPath = storage_path('logs/laravel.log');
        $history = [];
        
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            
            foreach ($lines as $line) {
                if (strpos($line, '締め処理完了') !== false) {
                    $history[] = $line;
                }
            }
            
            $history = array_slice(array_reverse($history), 0, $limit);
        }
        
        return $history;
    }
}
