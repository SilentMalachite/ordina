<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\JobStatus;
use App\Events\LowStockDetected;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Native\Laravel\Facades\Notification;
use Carbon\Carbon;

class ImportTransactionsFromCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $userId;
    protected bool $hasHeader;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $userId, bool $hasHeader = true)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->hasHeader = $hasHeader;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobStatus = JobStatus::create([
            'user_id' => $this->userId,
            'job_name' => '取引CSVインポート',
            'job_id' => $this->job->getJobId(),
            'status' => JobStatus::STATUS_PROCESSING,
            'progress' => 0,
            'started_at' => now(),
        ]);

        $success = 0;
        $errors = 0;
        $errorMessages = [];

        try {
            // ファイルが存在するか確認
            if (!Storage::exists($this->filePath)) {
                throw new \Exception('インポートファイルが見つかりません。');
            }

            // CSVファイルを読み込む
            $data = $this->parseCsvFile(Storage::path($this->filePath));

            // データをインポート
            $totalRows = count($data);
            $processedRows = 0;

            DB::transaction(function() use ($data, &$success, &$errors, &$errorMessages, &$processedRows, $totalRows, $jobStatus) {
                foreach ($data as $index => $row) {
                    try {
                        $transactionData = $this->mapTransactionData($row);
                        
                        // 商品と顧客の存在確認
                        $product = Product::where('product_code', $transactionData['product_code'])->first();
                        $customer = Customer::where('customer_code', $transactionData['customer_code'])->first();
                        
                        if (!$product) {
                            throw new \Exception("商品コード {$transactionData['product_code']} が存在しません。");
                        }
                        
                        if (!$customer) {
                            throw new \Exception("顧客コード {$transactionData['customer_code']} が存在しません。");
                        }
                        
                        // バリデーションデータを準備
                        $validationData = [
                            'transaction_date' => $transactionData['transaction_date'],
                            'transaction_type' => $transactionData['transaction_type'],
                            'product_id' => $product->id,
                            'customer_id' => $customer->id,
                            'quantity' => $transactionData['quantity'],
                            'unit_price' => $transactionData['unit_price'],
                            'total_amount' => $transactionData['total_amount'],
                        ];
                        
                        $validator = Validator::make($validationData, [
                            'transaction_date' => 'required|date',
                            'transaction_type' => 'required|in:sale,rental',
                            'product_id' => 'required|exists:products,id',
                            'customer_id' => 'required|exists:customers,id',
                            'quantity' => 'required|integer|min:1',
                            'unit_price' => 'required|numeric|min:0',
                            'total_amount' => 'required|numeric|min:0',
                        ]);
                        
                        if ($validator->fails()) {
                            $errors++;
                            $errorMessages[] = "行 " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                            continue;
                        }
                        
                        // 在庫数の確認（売却・貸出時）
                        if ($transactionData['transaction_type'] !== 'return' && $product->stock_quantity < $transactionData['quantity']) {
                            $errors++;
                            $errorMessages[] = "行 " . ($index + 2) . ": 在庫が不足しています。現在の在庫数: {$product->stock_quantity}、要求数: {$transactionData['quantity']}";
                            continue;
                        }
                        
                        // 取引を作成
                        $transaction = Transaction::create([
                            'transaction_date' => $transactionData['transaction_date'],
                            'transaction_type' => $transactionData['transaction_type'],
                            'product_id' => $product->id,
                            'customer_id' => $customer->id,
                            'quantity' => $transactionData['quantity'],
                            'unit_price' => $transactionData['unit_price'],
                            'total_amount' => $transactionData['total_amount'],
                            'user_id' => $this->userId,
                            'status' => $transactionData['transaction_type'] === 'rental' ? 'rented' : 'completed',
                        ]);
                        
                        // 在庫数を更新
                        if ($transactionData['transaction_type'] !== 'return') {
                            $product->stock_quantity -= $transactionData['quantity'];
                            $product->save();
                            
                            // 低在庫アラート
                            if ($product->stock_quantity <= config('app.low_stock_threshold', 10)) {
                                event(new LowStockDetected($product));
                            }
                        }
                        
                        $success++;
                        
                    } catch (\Exception $e) {
                        $errors++;
                        $errorMessages[] = "行 " . ($index + 2) . ": " . $e->getMessage();
                    }

                    $processedRows++;
                    if ($processedRows % 50 === 0) {
                        $progress = (int) (($processedRows / $totalRows) * 100);
                        $jobStatus->update(['progress' => $progress]);
                    }
                }
            });

            // ジョブステータスを更新
            $jobStatus->update([
                'status' => JobStatus::STATUS_COMPLETED,
                'progress' => 100,
                'completed_at' => now(),
                'output' => "インポート完了: 成功 {$success}件, エラー {$errors}件",
                'meta' => [
                    'success_count' => $success,
                    'error_count' => $errors,
                    'error_messages' => $errorMessages,
                ],
            ]);

            // 結果を通知
            $this->sendNotification($success, $errors);

            // エラーがあればログに記録
            if (!empty($errorMessages)) {
                Log::warning('取引インポートエラー', [
                    'file' => $this->filePath,
                    'errors' => $errorMessages
                ]);
            }

            // 処理済みファイルを削除
            Storage::delete($this->filePath);

        } catch (\Exception $e) {
            $jobStatus->update([
                'status' => JobStatus::STATUS_FAILED,
                'completed_at' => now(),
                'output' => 'エラーが発生しました: ' . $e->getMessage(),
            ]);

            Log::error('取引インポート処理中にエラーが発生しました', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);

            if (config('nativephp.enabled') && !app()->environment('testing')) {
                \Native\Laravel\Facades\Notification::title('取引インポート失敗')
                    ->message('エラーが発生しました: ' . $e->getMessage())
                    ->show();
            }
        }
    }

    /**
     * CSVファイルを解析
     */
    private function parseCsvFile($filePath): array
    {
        $data = [];
        $headers = null;
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            $rowIndex = 0;
            
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if ($this->hasHeader && $rowIndex === 0) {
                    $headers = $row;
                    $rowIndex++;
                    continue;
                }
                
                if ($this->hasHeader && $headers) {
                    $data[] = array_combine($headers, $row);
                } else {
                    $data[] = $row;
                }
                
                $rowIndex++;
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * 取引データのマッピング
     */
    private function mapTransactionData($row): array
    {
        if (is_array($row) && isset($row[0])) {
            return [
                'transaction_date' => $this->parseDate($row[0] ?? ''),
                'transaction_type' => $this->parseTransactionType($row[1] ?? ''),
                'product_code' => $row[2] ?? '',
                'customer_code' => $row[3] ?? '',
                'quantity' => (int) ($row[4] ?? 0),
                'unit_price' => (float) ($row[5] ?? 0),
                'total_amount' => (float) ($row[6] ?? 0),
            ];
        }
        
        return [
            'transaction_date' => $this->parseDate($row['取引日'] ?? $row['transaction_date'] ?? ''),
            'transaction_type' => $this->parseTransactionType($row['取引種別'] ?? $row['transaction_type'] ?? ''),
            'product_code' => $row['商品コード'] ?? $row['product_code'] ?? '',
            'customer_code' => $row['顧客コード'] ?? $row['customer_code'] ?? '',
            'quantity' => (int) ($row['数量'] ?? $row['quantity'] ?? 0),
            'unit_price' => (float) ($row['単価'] ?? $row['unit_price'] ?? 0),
            'total_amount' => (float) ($row['合計金額'] ?? $row['total_amount'] ?? 0),
        ];
    }

    /**
     * 日付をパース
     */
    private function parseDate($dateString): ?string
    {
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 取引種別をパース
     */
    private function parseTransactionType($type): ?string
    {
        $type = mb_strtolower(trim($type));
        
        if (in_array($type, ['売却', 'sale', '販売'])) {
            return 'sale';
        }
        
        if (in_array($type, ['貸出', 'rental', 'レンタル', '貸し出し'])) {
            return 'rental';
        }
        
        if (in_array($type, ['返却', 'return'])) {
            return 'return';
        }
        
        return null;
    }

    /**
     * 処理結果を通知
     */
    private function sendNotification(int $success, int $errors): void
    {
        if (config('nativephp.enabled') && !app()->environment('testing')) {
            if ($errors === 0) {
                \Native\Laravel\Facades\Notification::title('取引インポート完了')
                    ->message("{$success}件の取引を正常にインポートしました。")
                    ->show();
            } else {
                \Native\Laravel\Facades\Notification::title('取引インポート完了（エラーあり）')
                    ->message("成功: {$success}件、エラー: {$errors}件")
                    ->show();
            }
        }
    }
}
