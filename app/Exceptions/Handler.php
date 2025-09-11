<?php

namespace App\Exceptions;

use App\Services\ErrorHandlingService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // バリデーション例外はまずここで扱う（API以外はリダイレクト＋セッションエラー）
        if ($exception instanceof ValidationException) {
            // API 経路のみ JSON で返す
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'VALIDATION_ERROR',
                    'message' => '入力データに問題があります。',
                    'errors' => $exception->errors(),
                ], 422);
            }
            // リダイレクト＋セッションにエラーを格納（標準動作の簡易実装）
            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->withErrors($exception->errors(), $exception->errorBag);
        }

        // API リクエスト、または明示的に JSON を期待する場合は共通 JSON エラーにフォールバック
        if ($request->is('api/*') || $request->expectsJson()) {
            $errorService = new ErrorHandlingService();
            $errorResponse = $errorService->handleException($exception, $request);
            return response()->json($errorResponse, $errorResponse['status_code'] ?? 500);
        }

        return parent::render($request, $exception);
    }

    /**
     * バリデーション例外の処理
     */
    // 専用ハンドラは不要（render 内で対応）
}
