<?php

namespace App\Jobs;

use App\Models\Customer;
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

class ImportCustomersFromCsv implements ShouldQueue
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
            'job_name' => '顧客CSVインポート',
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
                        $customerData = $this->mapCustomerData($row);
                        
                        $validator = Validator::make($customerData, [
                            'customer_code' => 'required|string|max:50|unique:customers',
                            'company_name' => 'required|string|max:255',
                            'contact_name' => 'nullable|string|max:100',
                            'email' => 'nullable|email|max:255',
                            'phone' => 'nullable|string|max:20',
                            'address' => 'nullable|string|max:500',
                        ]);
                        
                        if ($validator->fails()) {
                            $errors++;
                            $errorMessages[] = "行 " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                            continue;
                        }
                        
                        Customer::create($customerData);
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

            // 結果を通知（テスト環境/無効化時はスキップ）
            if (config('nativephp.enabled') && !app()->environment('testing')) {
                $this->sendNotification($success, $errors);
            }

            // エラーがあればログに記録
            if (!empty($errorMessages)) {
                Log::warning('顧客インポートエラー', [
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

            Log::error('顧客インポート処理中にエラーが発生しました', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);

            if (config('nativephp.enabled') && !app()->environment('testing')) {
                \Native\Laravel\Facades\Notification::title('顧客インポート失敗')
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
     * 顧客データのマッピング
     */
    private function mapCustomerData($row): array
    {
        if (is_array($row) && isset($row[0])) {
            return [
                'customer_code' => $row[0] ?? '',
                'company_name' => $row[1] ?? '',
                'contact_name' => $row[2] ?? null,
                'email' => $row[3] ?? null,
                'phone' => $row[4] ?? null,
                'address' => $row[5] ?? null,
            ];
        }
        
        return [
            'customer_code' => $row['顧客コード'] ?? $row['customer_code'] ?? '',
            'company_name' => $row['会社名'] ?? $row['company_name'] ?? '',
            'contact_name' => $row['担当者名'] ?? $row['contact_name'] ?? null,
            'email' => $row['メールアドレス'] ?? $row['email'] ?? null,
            'phone' => $row['電話番号'] ?? $row['phone'] ?? null,
            'address' => $row['住所'] ?? $row['address'] ?? null,
        ];
    }

    /**
     * 処理結果を通知
     */
    private function sendNotification(int $success, int $errors): void
    {
        if (config('nativephp.enabled') && !app()->environment('testing')) {
            if ($errors === 0) {
                \Native\Laravel\Facades\Notification::title('顧客インポート完了')
                    ->message("{$success}件の顧客を正常にインポートしました。")
                    ->show();
            } else {
                \Native\Laravel\Facades\Notification::title('顧客インポート完了（エラーあり）')
                    ->message("成功: {$success}件、エラー: {$errors}件")
                    ->show();
            }
        }
    }
}
