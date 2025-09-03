<?php

namespace App\Services;

class InputSanitizationService
{
    /**
     * LIKE検索用の入力をサニタイズ
     * SQLインジェクション攻撃から保護
     */
    public function sanitizeSearchInput(string $input): string
    {
        // 危険な文字をエスケープまたは削除
        $input = $this->removeSqlInjectionCharacters($input);

        // 長さを制限（検索効率向上）
        $input = $this->limitLength($input, 100);

        return trim($input);
    }

    /**
     * SQLインジェクションに使用される可能性のある文字を削除またはエスケープ
     */
    private function removeSqlInjectionCharacters(string $input): string
    {
        // 危険な文字を削除
        $dangerousChars = [
            ';',      // クエリ区切り
            '--',     // SQLコメント
            '/*',     // ブロックコメント開始
            '*/',     // ブロックコメント終了
            'xp_',    // SQL Server拡張ストアドプロシージャ
            'sp_',    // システムストアドプロシージャ
            'exec',   // 実行コマンド
            'union',  // UNION攻撃
            'select', // SELECTインジェクション
            'insert', // INSERTインジェクション
            'update', // UPDATEインジェクション
            'delete', // DELETEインジェクション
            'drop',   // DROPインジェクション
            'create', // CREATEインジェクション
            'alter',  // ALTERインジェクション
            'script', // XSS攻撃
            '<',      // HTMLタグ
            '>',      // HTMLタグ
            'javascript:', // JavaScriptインジェクション
            'vbscript:',   // VBScriptインジェクション
            'onload',     // イベントハンドラー
            'onerror',    // イベントハンドラー
            'onclick',    // イベントハンドラー
        ];

        // 大文字小文字を区別しない検索・置換
        foreach ($dangerousChars as $char) {
            $input = str_ireplace($char, '', $input);
        }

        return $input;
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
        // 空白を削除
        $input = trim($input);

        // 許可されたカラムのみを許可
        if (!empty($allowedColumns) && !in_array($input, $allowedColumns)) {
            return null;
        }

        // 英数字、アンダースコア、ドットのみを許可
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $input)) {
            return null;
        }

        return $input;
    }

    /**
     * テーブル名を検証・サニタイズ
     */
    public function sanitizeTableName(string $input, array $allowedTables = []): ?string
    {
        $input = trim($input);

        // 許可されたテーブル名のみを許可
        if (!empty($allowedTables) && !in_array($input, $allowedTables)) {
            return null;
        }

        // 英数字、アンダースコアのみを許可
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) {
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
