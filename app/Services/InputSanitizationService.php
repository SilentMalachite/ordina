<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class InputSanitizationService
{
    /**
     * LIKE検索用の入力をサニタイズ
     * SQLインジェクション攻撃から保護
     */
    public function sanitizeSearchInput(string $input): string
    {
        // 文字エンコーディングを検証
        if (!$this->isValidEncoding($input)) {
            Log::warning('Invalid character encoding detected in search input', [
                'input_length' => strlen($input),
                'ip' => request()->ip(),
            ]);
            return '';
        }

        // SQLインジェクション対策
        $input = $this->preventSqlInjection($input);

        // XSS対策
        $input = $this->preventXss($input);

        // 長さを制限（検索効率向上）
        $input = $this->limitLength($input, 100);

        return trim($input);
    }

    /**
     * SQLインジェクション攻撃を防止
     */
    private function preventSqlInjection(string $input): string
    {
        // 危険なSQLキーワードを検知してブロック
        $dangerousPatterns = [
            '/\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\b/i',
            '/\b(xp_|sp_)\w+/i',  // SQL Server拡張プロシージャ
            '/--/',  // SQLコメント
            '/\/\*.*?\*\//s',  // ブロックコメント
            '/;(\s*|$)/',  // クエリ区切り
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('SQL injection attempt detected', [
                    'pattern' => $pattern,
                    'input' => substr($input, 0, 50) . '...',
                    'ip' => request()->ip(),
                ]);
                // 危険な入力の場合は空文字を返す
                return '';
            }
        }

        // 特殊文字をエスケープ
        $input = addslashes($input);

        return $input;
    }

    /**
     * XSS攻撃を防止
     */
    private function preventXss(string $input): string
    {
        // HTMLタグをエスケープ
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // JavaScript関連の危険な文字列を検知
        $dangerousJsPatterns = [
            '/javascript:/i',
            '/vbscript:/i',
            '/data:/i',
            '/on\w+\s*=/i',  // イベントハンドラー
        ];

        foreach ($dangerousJsPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('XSS attempt detected', [
                    'pattern' => $pattern,
                    'input' => substr($input, 0, 50) . '...',
                    'ip' => request()->ip(),
                ]);
                return '';
            }
        }

        return $input;
    }

    /**
     * 文字エンコーディングを検証
     */
    private function isValidEncoding(string $input): bool
    {
        // UTF-8エンコーディングを検証
        return mb_check_encoding($input, 'UTF-8');
    }

    /**
     * 文字列の長さを制限
     */
    private function limitLength(string $input, int $maxLength): string
    {
        return substr($input, 0, $maxLength);
    }

    /**
     * 数値パラメータを検証・サニタイズ
     */
    public function sanitizeNumericInput($input, ?int $min = null, ?int $max = null): ?int
    {
        if (!is_numeric($input) && !is_int($input)) {
            return null;
        }

        $value = (int) $input;

        if ($min !== null && $value < $min) {
            return $min;
        }

        if ($max !== null && $value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * 日付パラメータを検証・サニタイズ
     */
    public function sanitizeDateInput(string $input): ?string
    {
        try {
            $date = \Carbon\Carbon::parse($input);

            // 有効な日付範囲を制限（例: 2000-2100年）
            if ($date->year < 2000 || $date->year > 2100) {
                return null;
            }

            return $date->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * ソートパラメータを検証・サニタイズ
     */
    public function sanitizeOrderByInput(string $input, array $allowedColumns = []): ?string
    {
        // 文字エンコーディングを検証
        if (!$this->isValidEncoding($input)) {
            Log::warning('Invalid encoding in order by input', ['input' => $input]);
            return null;
        }

        // XSS対策
        $input = $this->preventXss($input);

        // 空白を削除
        $input = trim($input);

        // 許可されたカラムのみを許可
        if (!empty($allowedColumns) && !in_array($input, $allowedColumns)) {
            Log::warning('Unauthorized column in order by', [
                'column' => $input,
                'allowed' => $allowedColumns,
                'ip' => request()->ip(),
            ]);
            return null;
        }

        // 英数字、アンダースコア、ドットのみを許可（より厳密に）
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_.]*$/', $input)) {
            Log::warning('Invalid column name format in order by', [
                'column' => $input,
                'ip' => request()->ip(),
            ]);
            return null;
        }

        return $input;
    }

    /**
     * テーブル名を検証・サニタイズ
     */
    public function sanitizeTableName(string $input, array $allowedTables = []): ?string
    {
        // 文字エンコーディングを検証
        if (!$this->isValidEncoding($input)) {
            Log::warning('Invalid encoding in table name', ['input' => $input]);
            return null;
        }

        // XSS対策
        $input = $this->preventXss($input);

        $input = trim($input);

        // 許可されたテーブル名のみを許可
        if (!empty($allowedTables) && !in_array($input, $allowedTables)) {
            Log::warning('Unauthorized table name access', [
                'table' => $input,
                'allowed' => $allowedTables,
                'ip' => request()->ip(),
            ]);
            return null;
        }

        // 英数字、アンダースコアのみを許可（テーブル名として妥当な形式）
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $input)) {
            Log::warning('Invalid table name format', [
                'table' => $input,
                'ip' => request()->ip(),
            ]);
            return null;
        }

        return $input;
    }

    /**
     * UUIDを検証
     */
    public function validateUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * メールアドレスを検証・サニタイズ
     */
    public function sanitizeEmail(string $email): ?string
    {
        $email = trim($email);

        // Laravelのメール検証ルールを使用
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // 長さを制限
        if (strlen($email) > 254) {
            return null;
        }

        return $email;
    }

    /**
     * 配列パラメータを検証・サニタイズ
     */
    public function sanitizeArrayInput(array $input, int $maxItems = 100): array
    {
        // 配列サイズを制限
        if (count($input) > $maxItems) {
            $input = array_slice($input, 0, $maxItems);
        }

        // 各要素をサニタイズ（必要に応じて拡張）
        return array_map(function ($item) {
            return is_string($item) ? $this->sanitizeSearchInput($item) : $item;
        }, $input);
    }

    /**
     * ページネーションパラメータを検証・サニタイズ
     */
    public function sanitizePaginationInput($page, $perPage): array
    {
        $page = $this->sanitizeNumericInput($page, 1, 10000) ?? 1;
        $perPage = $this->sanitizeNumericInput($perPage, 1, 100) ?? 20;

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}

