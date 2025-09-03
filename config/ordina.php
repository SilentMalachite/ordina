<?php

return [
    // 事業ロジック系
    'default_closing_day' => env('ORDINA_DEFAULT_CLOSING_DAY', 25),
    'low_stock_threshold' => env('ORDINA_LOW_STOCK_THRESHOLD', 10),

    // エクスポート/バックアップ
    'excel_export_path' => env('ORDINA_EXCEL_EXPORT_PATH', 'storage/app/exports'),
    'backup_path' => env('ORDINA_BACKUP_PATH', storage_path('app/backups')),
    'max_backup_files' => env('ORDINA_MAX_BACKUP_FILES', 30),

    // ログ設定
    'max_log_files' => env('ORDINA_MAX_LOG_FILES', 30),
    'max_log_size' => env('ORDINA_MAX_LOG_SIZE', 10 * 1024 * 1024), // 10MB
];

