<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Models\InventoryAdjustment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PerformanceOptimizationService
{
    /**
     * 取引一覧のクエリを最適化
     */
    public function getOptimizedTransactions(Builder $query = null): Builder
    {
        $query = $query ?? Transaction::query();
        
        return $query->with([
            'product:id,product_code,name,stock_quantity,unit_price,selling_price',
            'customer:id,name,email,company',
            'user:id,name,email'
        ]);
    }

    /**
     * 商品一覧のクエリを最適化
     */
    public function getOptimizedProducts(Builder $query = null): Builder
    {
        $query = $query ?? Product::query();
        
        return $query->withCount([
            'transactions as sales_count' => function($q) {
                $q->where('type', 'sale');
            },
            'transactions as rentals_count' => function($q) {
                $q->where('type', 'rental');
            }
        ])->with([
            'transactions' => function($q) {
                $q->select('id', 'product_id', 'transaction_date', 'type', 'quantity', 'total_amount')
                  ->latest()
                  ->limit(5);
            }
        ]);
    }

    /**
     * 顧客一覧のクエリを最適化
     */
    public function getOptimizedCustomers(Builder $query = null): Builder
    {
        $query = $query ?? Customer::query();
        
        return $query->withCount([
            'transactions as total_transactions',
            'transactions as sales_count' => function($q) {
                $q->where('type', 'sale');
            },
            'transactions as rentals_count' => function($q) {
                $q->where('type', 'rental');
            }
        ])->withSum('transactions', 'total_amount');
    }

    /**
     * 在庫調整一覧のクエリを最適化
     */
    public function getOptimizedInventoryAdjustments(Builder $query = null): Builder
    {
        $query = $query ?? InventoryAdjustment::query();
        
        return $query->with([
            'product:id,product_code,name,stock_quantity',
            'user:id,name,email'
        ]);
    }

    /**
     * ダッシュボード統計を最適化
     */
    public function getDashboardStatistics(): array
    {
        return Cache::remember('dashboard_statistics', 300, function() {
            return [
                'total_products' => Product::count(),
                'total_customers' => Customer::count(),
                'total_transactions' => Transaction::count(),
                'admin_users' => User::where('is_admin', true)->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
                'active_rentals' => Transaction::where('type', 'rental')->whereNull('returned_at')->count(),
            ];
        });
    }

    /**
     * 最近のアクティビティを最適化
     */
    public function getRecentActivities(): array
    {
        return Cache::remember('recent_activities', 180, function() {
            return [
                'new_users' => User::select('id', 'name', 'email', 'created_at')
                    ->latest()
                    ->take(5)
                    ->get(),
                'recent_transactions' => Transaction::select('id', 'type', 'quantity', 'total_amount', 'transaction_date', 'product_id', 'customer_id', 'user_id')
                    ->with([
                        'product:id,name',
                        'customer:id,name',
                        'user:id,name'
                    ])
                    ->latest()
                    ->take(5)
                    ->get(),
                'recent_adjustments' => InventoryAdjustment::select('id', 'adjustment_type', 'quantity', 'reason', 'created_at', 'product_id', 'user_id')
                    ->with([
                        'product:id,name',
                        'user:id,name'
                    ])
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        });
    }

    /**
     * レポート用の最適化されたクエリ
     */
    public function getOptimizedReportQuery(string $type, array $filters = []): Builder
    {
        $query = Transaction::query();
        
        // 基本のリレーションを読み込み
        $query->with([
            'product:id,product_code,name,unit_price,selling_price',
            'customer:id,name,email,company',
            'user:id,name,email'
        ]);
        
        // タイプフィルター
        if ($type === 'sales') {
            $query->where('type', 'sale');
        } elseif ($type === 'rentals') {
            $query->where('type', 'rental');
        }
        
        // 日付フィルター
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('transaction_date', [$filters['date_from'], $filters['date_to']]);
        }
        
        // 顧客フィルター
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // 商品フィルター
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        
        return $query;
    }

    /**
     * 在庫レポート用の最適化されたクエリ
     */
    public function getOptimizedInventoryReportQuery(array $filters = []): Builder
    {
        $query = Product::query();
        
        // 基本のリレーションとカウント
        $query->withCount([
            'transactions as sales_count' => function($q) {
                $q->where('type', 'sale');
            },
            'transactions as rentals_count' => function($q) {
                $q->where('type', 'rental');
            }
        ])->with([
            'transactions' => function($q) {
                $q->select('id', 'product_id', 'transaction_date', 'type', 'quantity')
                  ->latest()
                  ->limit(1);
            }
        ]);
        
        // 低在庫フィルター
        if (isset($filters['low_stock_only']) && $filters['low_stock_only']) {
            $query->where('stock_quantity', '<=', 10);
        }
        
        return $query;
    }

    /**
     * 顧客レポート用の最適化されたクエリ
     */
    public function getOptimizedCustomerReportQuery(array $filters = []): Builder
    {
        $query = Customer::query();
        
        // 基本のリレーションとカウント
        $query->withCount([
            'transactions as total_transactions',
            'transactions as sales_count' => function($q) {
                $q->where('type', 'sale');
            },
            'transactions as rentals_count' => function($q) {
                $q->where('type', 'rental');
            }
        ])->withSum('transactions', 'total_amount');
        
        // 日付フィルター
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereHas('transactions', function($q) use ($filters) {
                $q->whereBetween('transaction_date', [$filters['date_from'], $filters['date_to']]);
            });
        }
        
        return $query;
    }

    /**
     * キャッシュをクリア
     */
    public function clearCache(): void
    {
        Cache::forget('dashboard_statistics');
        Cache::forget('recent_activities');
    }

    /**
     * データベースクエリの最適化
     */
    public function optimizeDatabaseQueries(): void
    {
        // インデックスの確認と作成
        $this->ensureIndexes();
        
        // 統計情報の更新
        $this->updateStatistics();
    }

    /**
     * 必要なインデックスを確認
     */
    private function ensureIndexes(): void
    {
        $indexes = [
            'transactions' => [
                'type',
                'transaction_date',
                'customer_id',
                'product_id',
                'user_id',
                'returned_at'
            ],
            'products' => [
                'product_code',
                'name',
                'stock_quantity'
            ],
            'customers' => [
                'email',
                'name'
            ],
            'inventory_adjustments' => [
                'product_id',
                'user_id',
                'created_at'
            ]
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                $indexName = "idx_{$table}_{$column}";
                $this->createIndexIfNotExists($table, $column, $indexName);
            }
        }
    }

    /**
     * インデックスが存在しない場合に作成
     */
    private function createIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        try {
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} ({$column})");
        } catch (\Exception $e) {
            // インデックス作成エラーは無視（既に存在する場合など）
        }
    }

    /**
     * 統計情報を更新
     */
    private function updateStatistics(): void
    {
        try {
            DB::statement('ANALYZE');
        } catch (\Exception $e) {
            // SQLiteではANALYZEコマンドが利用できない場合がある
        }
    }

    /**
     * ページネーションの最適化
     */
    public function getOptimizedPaginatedResults(Builder $query, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $query->paginate($perPage);
    }

    /**
     * 検索クエリの最適化
     */
    public function optimizeSearchQuery(Builder $query, string $searchTerm, array $searchColumns): Builder
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $query->where(function($q) use ($searchTerm, $searchColumns) {
            foreach ($searchColumns as $column) {
                $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
            }
        });

        return $query;
    }
}