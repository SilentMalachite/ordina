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
        // APIリクエストまたはJSONを期待するリクエストの場合
        if ($request->expectsJson() || $request->is('api/*')) {
            $errorService = new ErrorHandlingService();
            $errorResponse = $errorService->handleException($exception, $request);
            
            return response()->json($errorResponse, $errorResponse['status_code'] ?? 500);
        }

        // バリデーション例外の特別処理
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception, $request);
        }

        return parent::render($request, $exception);
    }

    /**
     * バリデーション例外の処理
     */
    protected function handleValidationException(ValidationException $exception, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_code' => 'VALIDATION_ERROR',
                'message' => '入力データに問題があります。',
                'errors' => $exception->errors(),
            ], 422);
        }

        return parent::handleValidationException($exception, $request);
    }
}
