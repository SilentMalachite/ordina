<?php

namespace App\Services;

use App\Exceptions\OrdinaException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorHandlingService
{
    /**
     * 例外を適切に処理し、ユーザーフレンドリーなレスポンスを返す
     */
    public function handleException(Throwable $exception, Request $request): array
    {
        // ログに記録
        $this->logException($exception, $request);

        // 例外タイプに応じて処理
        if ($exception instanceof OrdinaException) {
            return $this->handleOrdinaException($exception);
        }

        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception);
        }

        // その他の例外
        return $this->handleGenericException($exception);
    }

    /**
     * OrdinaExceptionの処理
     */
    private function handleOrdinaException(OrdinaException $exception): array
    {
        return [
            'success' => false,
            'error_code' => $exception->getErrorCode(),
            'message' => $exception->getUserMessage(),
            'context' => $exception->getContext(),
            'status_code' => $exception->getCode()
        ];
    }

    /**
     * ValidationExceptionの処理
     */
    private function handleValidationException(ValidationException $exception): array
    {
        return [
            'success' => false,
            'error_code' => 'VALIDATION_ERROR',
            'message' => '入力データに問題があります。',
            'errors' => $exception->errors(),
            'status_code' => 422
        ];
    }

    /**
     * ModelNotFoundExceptionの処理
     */
    private function handleModelNotFoundException(ModelNotFoundException $exception): array
    {
        $model = class_basename($exception->getModel());
        $resource = $this->getResourceName($model);

        return [
            'success' => false,
            'error_code' => 'DATA_NOT_FOUND',
            'message' => "指定された{$resource}が見つかりません。",
            'status_code' => 404
        ];
    }

    /**
     * AuthorizationExceptionの処理
     */
    private function handleAuthorizationException(AuthorizationException $exception): array
    {
        return [
            'success' => false,
            'error_code' => 'PERMISSION_DENIED',
            'message' => 'この操作を実行する権限がありません。',
            'status_code' => 403
        ];
    }

    /**
     * 一般的な例外の処理
     */
    private function handleGenericException(Throwable $exception): array
    {
        $message = app()->environment('production') 
            ? 'システムエラーが発生しました。しばらく時間をおいて再度お試しください。'
            : $exception->getMessage();

        return [
            'success' => false,
            'error_code' => 'SYSTEM_ERROR',
            'message' => $message,
            'status_code' => 500
        ];
    }

    /**
     * 例外をログに記録
     */
    private function logException(Throwable $exception, Request $request): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
        ];

        if ($exception instanceof OrdinaException) {
            $context['error_code'] = $exception->getErrorCode();
            $context['context'] = $exception->getContext();
        }

        Log::error('Exception occurred', $context);
    }

    /**
     * モデル名からリソース名を取得
     */
    private function getResourceName(string $model): string
    {
        $resourceMap = [
            'Product' => '商品',
            'Customer' => '顧客',
            'Transaction' => '取引',
            'User' => 'ユーザー',
            'InventoryAdjustment' => '在庫調整',
            'ClosingDate' => '締め日',
        ];

        return $resourceMap[$model] ?? 'データ';
    }

    /**
     * 安全なデータベース操作を実行
     */
    public function safeDatabaseOperation(callable $operation, string $operationName = 'データベース操作'): array
    {
        try {
            $result = $operation();
            
            return [
                'success' => true,
                'data' => $result,
                'message' => "{$operationName}が正常に完了しました。"
            ];
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, request());
        }
    }

    /**
     * バリデーションエラーを統一フォーマットで返す
     */
    public function formatValidationErrors(array $errors): array
    {
        $formatted = [];
        
        foreach ($errors as $field => $messages) {
            $formatted[$field] = is_array($messages) ? $messages[0] : $messages;
        }
        
        return $formatted;
    }
}