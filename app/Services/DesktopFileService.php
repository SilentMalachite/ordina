<?php

namespace App\Services;

use Native\Laravel\Facades\FileSystem;
use Native\Laravel\Facades\Dialog;
use Illuminate\Support\Facades\Storage;

class DesktopFileService
{
    /**
     * ファイル保存ダイアログを表示してファイルを保存
     */
    public function saveFileDialog(string $content, string $defaultName, string $extension = 'txt'): ?string
    {
        $path = Dialog::save()
            ->title('ファイルを保存')
            ->defaultName($defaultName)
            ->filters([
                ['name' => ucfirst($extension) . ' Files', 'extensions' => [$extension]],
                ['name' => 'All Files', 'extensions' => ['*']]
            ]);

        if ($path) {
            file_put_contents($path, $content);
            return $path;
        }

        return null;
    }

    /**
     * ファイル選択ダイアログを表示してファイルを選択
     */
    public function openFileDialog(array $filters = []): ?string
    {
        $defaultFilters = [
            ['name' => 'Excel Files', 'extensions' => ['xlsx', 'xls']],
            ['name' => 'CSV Files', 'extensions' => ['csv']],
            ['name' => 'All Files', 'extensions' => ['*']]
        ];

        $filters = empty($filters) ? $defaultFilters : $filters;

        return Dialog::open()
            ->title('ファイルを選択')
            ->filters($filters);
    }

    /**
     * デスクトップにファイルを保存
     */
    public function saveToDesktop(string $content, string $filename): ?string
    {
        $desktopPath = $this->getDesktopPath();
        $filePath = $desktopPath . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($filePath, $content)) {
            return $filePath;
        }

        return null;
    }

    /**
     * デスクトップパスを取得
     */
    public function getDesktopPath(): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE');
        return $home . DIRECTORY_SEPARATOR . 'Desktop';
    }

    /**
     * ダウンロードフォルダにファイルを保存
     */
    public function saveToDownloads(string $content, string $filename): ?string
    {
        $downloadsPath = $this->getDownloadsPath();
        $filePath = $downloadsPath . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($filePath, $content)) {
            return $filePath;
        }

        return null;
    }

    /**
     * ダウンロードフォルダパスを取得
     */
    public function getDownloadsPath(): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE');
        return $home . DIRECTORY_SEPARATOR . 'Downloads';
    }

    /**
     * ファイルを開く
     */
    public function openFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $command = $this->getOpenCommand($filePath);
        if ($command) {
            exec($command);
            return true;
        }

        return false;
    }

    /**
     * ファイルを開くコマンドを取得
     */
    private function getOpenCommand(string $filePath): ?string
    {
        $os = PHP_OS_FAMILY;

        switch ($os) {
            case 'Darwin': // macOS
                return "open '" . $filePath . "'";
            case 'Windows':
                return "start '" . $filePath . "'";
            case 'Linux':
                return "xdg-open '" . $filePath . "'";
            default:
                return null;
        }
    }

    /**
     * フォルダを開く
     */
    public function openFolder(string $folderPath): bool
    {
        if (!is_dir($folderPath)) {
            return false;
        }

        $command = $this->getOpenFolderCommand($folderPath);
        if ($command) {
            exec($command);
            return true;
        }

        return false;
    }

    /**
     * フォルダを開くコマンドを取得
     */
    private function getOpenFolderCommand(string $folderPath): ?string
    {
        $os = PHP_OS_FAMILY;

        switch ($os) {
            case 'Darwin': // macOS
                return "open '" . $folderPath . "'";
            case 'Windows':
                return "explorer '" . $folderPath . "'";
            case 'Linux':
                return "xdg-open '" . $folderPath . "'";
            default:
                return null;
        }
    }
}