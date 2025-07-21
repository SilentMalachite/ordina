<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\JobStatus;
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

class ImportProductsFromCsv implements ShouldQueue
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
            'job_name' => '商品CSVインポート',
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
                        $productData = $this->mapProductData($row);
                        
                        $validator = Validator::make($productData, [
                            'product_code' => 'required|string|max:50|unique:products',
                            'name' => 'required|string|max:255',
                            'stock_quantity' => 'required|integer|min:0',
                            'unit_price' => 'required|numeric|min:0',
                            'selling_price' => 'required|numeric|min:0',
                        ]);
                        
                        if ($validator->fails()) {
                            $errors++;
                            $errorMessages[] = "行 " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                            continue;
                        }
                        
                        Product::create($productData);
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
                Log::warning('商品インポートエラー', [
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

            Log::error('商品インポート処理中にエラーが発生しました', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);

            Notification::title('商品インポート失敗')
                ->message('エラーが発生しました: ' . $e->getMessage())
                ->show();
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
     * 商品データのマッピング
     */
    private function mapProductData($row): array
    {
        if (is_array($row) && isset($row[0])) {
            return [
                'product_code' => $row[0] ?? '',
                'name' => $row[1] ?? '',
                'stock_quantity' => (int) ($row[2] ?? 0),
                'unit_price' => (float) ($row[3] ?? 0),
                'selling_price' => (float) ($row[4] ?? 0),
                'description' => $row[5] ?? '',
            ];
        }
        
        return [
            'product_code' => $row['商品コード'] ?? $row['product_code'] ?? '',
            'name' => $row['商品名'] ?? $row['name'] ?? '',
            'stock_quantity' => (int) ($row['在庫数'] ?? $row['stock_quantity'] ?? 0),
            'unit_price' => (float) ($row['単価'] ?? $row['unit_price'] ?? 0),
            'selling_price' => (float) ($row['売値'] ?? $row['selling_price'] ?? 0),
            'description' => $row['説明'] ?? $row['description'] ?? '',
        ];
    }

    /**
     * 処理結果を通知
     */
    private function sendNotification(int $success, int $errors): void
    {
        if ($errors === 0) {
            Notification::title('商品インポート完了')
                ->message("{$success}件の商品を正常にインポートしました。")
                ->show();
        } else {
            Notification::title('商品インポート完了（エラーあり）')
                ->message("成功: {$success}件、エラー: {$errors}件")
                ->show();
        }
    }
}
