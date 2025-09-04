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

    // セキュリティ設定
    'security' => [
        // CSP設定
        'csp_enabled' => env('CSP_ENABLED', true),
        'csp_report_only' => env('CSP_REPORT_ONLY', false),

        // Rate Limiting
        'rate_limit_enabled' => env('RATE_LIMIT_ENABLED', true),
        'rate_limit_sync' => env('RATE_LIMIT_SYNC', 100), // 1時間あたりのリクエスト数
        'rate_limit_search' => env('RATE_LIMIT_SEARCH', 300),
        'rate_limit_general' => env('RATE_LIMIT_GENERAL', 1000),

        // セキュリティヘッダー
        'security_headers_enabled' => env('SECURITY_HEADERS_ENABLED', true),
        'force_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),

        // 入力検証
        'input_validation_strict' => env('INPUT_VALIDATION_STRICT', true),
        'max_string_length' => env('MAX_STRING_LENGTH', 1000),
        'max_array_items' => env('MAX_ARRAY_ITEMS', 100),
    ],
];

