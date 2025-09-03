<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Models\ClosingDate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    protected $backupPath;
    protected $maxBackupFiles;

    public function __construct()
    {
        $this->backupPath = config('ordina.backup_path', storage_path('app/backups'));
        $this->maxBackupFiles = config('ordina.max_backup_files', 30);
        
        // バックアップディレクトリの作成
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * 完全バックアップの作成
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "full_backup_{$timestamp}";
        $backupDir = $this->backupPath . '/' . $backupName;
        
        // バックアップディレクトリの作成
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        try {
            // データベースのバックアップ
            $this->backupDatabase($backupDir);
            
            // ファイルのバックアップ
            $this->backupFiles($backupDir);
            
            // 設定ファイルのバックアップ
            $this->backupConfig($backupDir);
            
            // バックアップ情報の記録
            $this->createBackupInfo($backupDir, 'full');
            
            // ZIPファイルの作成
            $zipFile = $this->createZipFile($backupDir, $backupName);
            
            // 一時ディレクトリの削除
            $this->deleteDirectory($backupDir);
            
            return [
                'success' => true,
                'filename' => $zipFile,
                'size' => filesize($this->backupPath . '/' . $zipFile),
                'created_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            // エラー時のクリーンアップ
            if (is_dir($backupDir)) {
                $this->deleteDirectory($backupDir);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * データベースのバックアップ
     */
    public function createDatabaseBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "database_backup_{$timestamp}";
        $backupDir = $this->backupPath . '/' . $backupName;
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        try {
            $this->backupDatabase($backupDir);
            $this->createBackupInfo($backupDir, 'database');
            
            $zipFile = $this->createZipFile($backupDir, $backupName);
            $this->deleteDirectory($backupDir);
            
            return [
                'success' => true,
                'filename' => $zipFile,
                'size' => filesize($this->backupPath . '/' . $zipFile),
                'created_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            if (is_dir($backupDir)) {
                $this->deleteDirectory($backupDir);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * データベースのバックアップ（内部メソッド）
     */
    private function backupDatabase(string $backupDir): void
    {
        $dbPath = database_path('ordina.sqlite');
        
        if (file_exists($dbPath)) {
            // SQLiteファイルのコピー
            copy($dbPath, $backupDir . '/database.sqlite');
            
            // JSON形式でのデータエクスポート
            $data = [
                'created_at' => now()->toISOString(),
                'users' => User::all()->toArray(),
                'products' => Product::all()->toArray(),
                'customers' => Customer::all()->toArray(),
                'transactions' => Transaction::with(['product', 'customer', 'user'])->get()->toArray(),
                'inventory_adjustments' => InventoryAdjustment::with(['product', 'user'])->get()->toArray(),
                'closing_dates' => ClosingDate::all()->toArray(),
            ];
            
            file_put_contents(
                $backupDir . '/data_export.json',
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    /**
     * ファイルのバックアップ
     */
    private function backupFiles(string $backupDir): void
    {
        $filesDir = $backupDir . '/files';
        mkdir($filesDir, 0755, true);
        
        // ストレージディレクトリのバックアップ
        $storagePath = storage_path('app');
        if (is_dir($storagePath)) {
            $this->copyDirectory($storagePath, $filesDir . '/storage');
        }
        
        // アップロードファイルのバックアップ
        $publicPath = public_path('uploads');
        if (is_dir($publicPath)) {
            $this->copyDirectory($publicPath, $filesDir . '/uploads');
        }
    }

    /**
     * 設定ファイルのバックアップ
     */
    private function backupConfig(string $backupDir): void
    {
        $configDir = $backupDir . '/config';
        mkdir($configDir, 0755, true);
        
        // センシティブな .env はバックアップに含めない
        
        // 設定ディレクトリのバックアップ
        $configPath = config_path();
        if (is_dir($configPath)) {
            $this->copyDirectory($configPath, $configDir . '/config');
        }
    }

    /**
     * バックアップ情報の作成
     */
    private function createBackupInfo(string $backupDir, string $type): void
    {
        $info = [
            'type' => $type,
            'created_at' => now()->toISOString(),
            'created_by' => auth()->id(),
            'version' => config('app.version', '1.0.0'),
            'database_size' => $this->getDatabaseSize(),
            'total_files' => $this->countFiles($backupDir),
        ];
        
        file_put_contents(
            $backupDir . '/backup_info.json',
            json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * ZIPファイルの作成
     */
    private function createZipFile(string $backupDir, string $backupName): string
    {
        $zipFile = $this->backupPath . '/' . $backupName . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $this->addDirectoryToZip($zip, $backupDir, '');
            $zip->close();
        }
        
        return $backupName . '.zip';
    }

    /**
     * ディレクトリをZIPに追加
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dir . '/' . $file;
            $zipFilePath = $zipPath . '/' . $file;
            
            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                $this->addDirectoryToZip($zip, $filePath, $zipFilePath);
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }

    /**
     * バックアップファイルの一覧取得
     */
    public function getBackupFiles(): array
    {
        $files = [];
        
        if (is_dir($this->backupPath)) {
            $backupFiles = File::files($this->backupPath);
            
            foreach ($backupFiles as $file) {
                if ($file->getExtension() === 'zip') {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'size_human' => $this->formatBytes($file->getSize()),
                        'created' => Carbon::createFromTimestamp($file->getCTime()),
                        'created_human' => Carbon::createFromTimestamp($file->getCTime())->diffForHumans(),
                        'type' => $this->getBackupType($file->getFilename()),
                    ];
                }
            }
            
            // 作成日時でソート（新しい順）
            usort($files, function($a, $b) {
                return $b['created']->timestamp - $a['created']->timestamp;
            });
        }
        
        return $files;
    }

    /**
     * バックアップタイプの取得
     */
    private function getBackupType(string $filename): string
    {
        if (strpos($filename, 'full_backup_') === 0) {
            return 'full';
        } elseif (strpos($filename, 'database_backup_') === 0) {
            return 'database';
        }
        
        return 'unknown';
    }

    /**
     * バックアップファイルの削除
     */
    public function deleteBackup(string $filename): bool
    {
        if (!$this->isSafeBackupFilename($filename)) {
            return false;
        }

        $filePath = $this->backupPath . '/' . $filename;
        $real = realpath($filePath);
        if ($real === false) {
            return false;
        }
        if (!$this->isPathWithin($real, $this->backupPath)) {
            return false;
        }

        return @unlink($real);
    }

    /**
     * バックアップファイルのダウンロード
     */
    public function downloadBackup(string $filename): ?string
    {
        if (!$this->isSafeBackupFilename($filename)) {
            return null;
        }

        $filePath = $this->backupPath . '/' . $filename;
        $real = realpath($filePath);
        if ($real === false) {
            return null;
        }
        if (!$this->isPathWithin($real, $this->backupPath)) {
            return null;
        }

        return $real;
    }

    /**
     * バックアップからの復元
     */
    public function restoreFromBackup(string $filename): array
    {
        if (!$this->isSafeBackupFilename($filename)) {
            return [
                'success' => false,
                'error' => '不正なバックアップファイル名です。',
            ];
        }

        $filePath = $this->backupPath . '/' . $filename;
        $real = realpath($filePath);
        if ($real === false || !$this->isPathWithin($real, $this->backupPath)) {
            return [
                'success' => false,
                'error' => 'バックアップファイルが見つかりません。',
            ];
        }
        
        try {
            $tempDir = storage_path('app/temp_restore_' . uniqid());
            mkdir($tempDir, 0755, true);
            
            // ZIPファイルの展開
            $zip = new ZipArchive();
            if ($zip->open($real) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                return [
                    'success' => false,
                    'error' => 'バックアップファイルの展開に失敗しました。',
                ];
            }
            
            // データベースの復元
            if (file_exists($tempDir . '/database.sqlite')) {
                copy($tempDir . '/database.sqlite', database_path('ordina.sqlite'));
            }
            
            // ファイルの復元
            if (is_dir($tempDir . '/files/storage')) {
                $this->copyDirectory($tempDir . '/files/storage', storage_path('app'));
            }
            
            // 一時ディレクトリの削除
            $this->deleteDirectory($tempDir);
            
            return [
                'success' => true,
                'message' => 'バックアップからの復元が完了しました。',
            ];
            
        } catch (\Exception $e) {
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            
            return [
                'success' => false,
                'error' => '復元中にエラーが発生しました: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 古いバックアップのクリーンアップ
     */
    public function cleanupOldBackups(): int
    {
        $files = $this->getBackupFiles();
        $deletedCount = 0;
        
        if (count($files) > $this->maxBackupFiles) {
            $filesToDelete = array_slice($files, $this->maxBackupFiles);
            
            foreach ($filesToDelete as $file) {
                if ($this->deleteBackup($file['name'])) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    /**
     * バックアップ統計の取得
     */
    public function getBackupStatistics(): array
    {
        $files = $this->getBackupFiles();
        $totalSize = 0;
        $typeCounts = [];
        
        foreach ($files as $file) {
            $totalSize += $file['size'];
            $type = $file['type'];
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        
        return [
            'total_backups' => count($files),
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'type_counts' => $typeCounts,
            'oldest_backup' => count($files) > 0 ? $files[count($files) - 1]['created'] : null,
            'newest_backup' => count($files) > 0 ? $files[0]['created'] : null,
            'max_backup_files' => $this->maxBackupFiles,
        ];
    }

    /**
     * ディレクトリのコピー
     */
    private function copyDirectory(string $src, string $dst): void
    {
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        $files = scandir($src);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }

    /**
     * ディレクトリの削除
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dir . '/' . $file;
            
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        rmdir($dir);
    }

    /**
     * ファイル数のカウント
     */
    private function countFiles(string $dir): int
    {
        $count = 0;
        
        if (is_dir($dir)) {
            $files = scandir($dir);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                $filePath = $dir . '/' . $file;
                
                if (is_dir($filePath)) {
                    $count += $this->countFiles($filePath);
                } else {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * データベースサイズの取得
     */
    private function getDatabaseSize(): int
    {
        $dbPath = database_path('ordina.sqlite');
        
        if (file_exists($dbPath)) {
            return filesize($dbPath);
        }
        
        return 0;
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
     * バックアップ用の安全なファイル名か確認（拡張子zipのみ許可）
     */
    private function isSafeBackupFilename(string $filename): bool
    {
        // ディレクトリセパレータ禁止 + 制御文字禁止
        if ($filename !== basename($filename)) {
            return false;
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            return false;
        }
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'zip') {
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
