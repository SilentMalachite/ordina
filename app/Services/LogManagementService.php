<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogManagementService
{
    protected $logPath;
    protected $maxLogFiles;
    protected $maxLogSize;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
        $this->maxLogFiles = config('ordina.max_log_files', 30);
        $this->maxLogSize = config('ordina.max_log_size', 10485760); // 10MB
    }

    /**
     * ログファイルの一覧を取得
     */
    public function getLogFiles(): array
    {
        $files = [];
        
        if (is_dir($this->logPath)) {
            $logFiles = File::files($this->logPath);
            
            foreach ($logFiles as $file) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'size_human' => $this->formatBytes($file->getSize()),
                    'modified' => Carbon::createFromTimestamp($file->getMTime()),
                    'modified_human' => Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
                ];
            }
            
            // 更新日時でソート（新しい順）
            usort($files, function($a, $b) {
                return $b['modified']->timestamp - $a['modified']->timestamp;
            });
        }
        
        return $files;
    }

    /**
     * ログファイルの内容を取得
     */
    public function getLogContent(string $filename, int $lines = 100): array
    {
        if (!$this->isSafeLogFilename($filename)) {
            return [];
        }
        $filePath = realpath($this->logPath . '/' . $filename);
        if ($filePath === false || !$this->isPathWithin($filePath, $this->logPath)) {
            return [];
        }
        
        $content = @file_get_contents($filePath);
        $logLines = explode("\n", $content);
        
        // 最新のN行を取得
        $logLines = array_slice($logLines, -$lines);
        
        $logs = [];
        foreach ($logLines as $line) {
            if (trim($line)) {
                $logs[] = $this->parseLogLine($line);
            }
        }
        
        return array_reverse($logs); // 新しい順に並び替え
    }

    /**
     * ログ行を解析
     */
    private function parseLogLine(string $line): array
    {
        $log = [
            'raw' => $line,
            'timestamp' => null,
            'level' => 'info',
            'message' => $line,
            'context' => null,
        ];
        
        // Laravelログフォーマットの解析
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+): (.+)$/', $line, $matches)) {
            $log['timestamp'] = Carbon::parse($matches[1]);
            $log['level'] = strtolower($matches[2]);
            $log['message'] = $matches[3];
            
            // JSONコンテキストの解析
            if (preg_match('/^(.+?)\s+(\{.*\})$/', $log['message'], $contextMatches)) {
                $log['message'] = $contextMatches[1];
                $log['context'] = json_decode($contextMatches[2], true);
            }
        }
        
        return $log;
    }

    /**
     * ログファイルをクリア
     */
    public function clearLogFile(string $filename): bool
    {
        if (!$this->isSafeLogFilename($filename)) {
            return false;
        }
        $filePath = realpath($this->logPath . '/' . $filename);
        if ($filePath === false || !$this->isPathWithin($filePath, $this->logPath)) {
            return false;
        }
        return @file_put_contents($filePath, '') !== false;
    }

    /**
     * ログファイルを削除
     */
    public function deleteLogFile(string $filename): bool
    {
        if (!$this->isSafeLogFilename($filename)) {
            return false;
        }
        $filePath = realpath($this->logPath . '/' . $filename);
        if ($filePath === false || !$this->isPathWithin($filePath, $this->logPath)) {
            return false;
        }
        return @unlink($filePath);
    }

    /**
     * ログローテーション
     */
    public function rotateLogs(): array
    {
        $rotated = [];
        $files = $this->getLogFiles();
        
        foreach ($files as $file) {
            // ファイルサイズが上限を超えている場合
            if ($file['size'] > $this->maxLogSize) {
                $this->rotateLogFile($file['name']);
                $rotated[] = $file['name'];
            }
        }
        
        // 古いログファイルの削除
        $this->cleanOldLogs();
        
        return $rotated;
    }

    /**
     * 個別ログファイルのローテーション
     */
    private function rotateLogFile(string $filename): void
    {
        if (!$this->isSafeLogFilename($filename)) {
            return;
        }
        $filePath = realpath($this->logPath . '/' . $filename);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $rotatedPath = $this->logPath . '/' . $filename . '.' . $timestamp;
        
        if ($filePath && $this->isPathWithin($filePath, $this->logPath) && file_exists($filePath)) {
            @rename($filePath, $rotatedPath);
            
            // 新しい空のログファイルを作成
            @touch($filePath);
            @chmod($filePath, 0644);
        }
    }

    /**
     * 古いログファイルの削除
     */
    private function cleanOldLogs(): void
    {
        $files = $this->getLogFiles();
        
        if (count($files) > $this->maxLogFiles) {
            $filesToDelete = array_slice($files, $this->maxLogFiles);
            
            foreach ($filesToDelete as $file) {
                $this->deleteLogFile($file['name']);
            }
        }
    }

    /**
     * ログ統計の取得
     */
    public function getLogStatistics(): array
    {
        $files = $this->getLogFiles();
        $totalSize = 0;
        $levelCounts = [];
        
        foreach ($files as $file) {
            $totalSize += $file['size'];
            
            // メインのログファイルからレベル別カウントを取得
            if ($file['name'] === 'laravel.log') {
                $content = $this->getLogContent($file['name'], 1000);
                foreach ($content as $log) {
                    $level = $log['level'];
                    $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'level_counts' => $levelCounts,
            'oldest_log' => count($files) > 0 ? $files[count($files) - 1]['modified'] : null,
            'newest_log' => count($files) > 0 ? $files[0]['modified'] : null,
        ];
    }

    /**
     * ログレベル別のフィルタリング
     */
    public function getLogsByLevel(string $level, int $lines = 100): array
    {
        $content = $this->getLogContent('laravel.log', $lines * 2); // 多めに取得
        
        $filtered = array_filter($content, function($log) use ($level) {
            return $log['level'] === $level;
        });
        
        return array_slice($filtered, 0, $lines);
    }

    /**
     * エラーログの取得
     */
    public function getErrorLogs(int $lines = 50): array
    {
        return $this->getLogsByLevel('error', $lines);
    }

    /**
     * 警告ログの取得
     */
    public function getWarningLogs(int $lines = 50): array
    {
        return $this->getLogsByLevel('warning', $lines);
    }

    /**
     * カスタムログの記録
     */
    public function logCustom(string $level, string $message, array $context = []): void
    {
        $logData = [
            'message' => $message,
            'context' => $context,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        Log::channel('single')->{$level}($message, $logData);
    }

    /**
     * システムイベントのログ記録
     */
    public function logSystemEvent(string $event, array $data = []): void
    {
        $this->logCustom('info', "System Event: {$event}", $data);
    }

    /**
     * セキュリティイベントのログ記録
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $this->logCustom('warning', "Security Event: {$event}", $data);
    }

    /**
     * バイト数を人間が読みやすい形式に変換
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * ログ設定の更新
     */
    public function updateLogSettings(array $settings): void
    {
        if (isset($settings['max_log_files'])) {
            $this->maxLogFiles = $settings['max_log_files'];
        }
        
        if (isset($settings['max_log_size'])) {
            $this->maxLogSize = $settings['max_log_size'];
        }
        
        // 設定をファイルに保存（実際の実装では設定テーブルを使用）
        // 設定ファイルの直接書き換えは避け、永続化は別途の設定管理に委ねる
    }

    /**
     * ログファイル名の安全性チェック（ディレクトリトラバーサル対策）
     */
    private function isSafeLogFilename(string $filename): bool
    {
        if ($filename !== basename($filename)) {
            return false;
        }
        // 許可文字のみ
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            return false;
        }
        return true;
    }

    /**
     * 与えられた実パスがベースディレクトリ配下かを確認
     */
    private function isPathWithin(string $realPath, string $baseDir): bool
    {
        $base = rtrim(realpath($baseDir) ?: $baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path = rtrim($realPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($path, $base);
    }
}
