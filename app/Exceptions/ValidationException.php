<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;

class ValidationException extends BaseValidationException
{
    protected $errorCode = 'VALIDATION_ERROR';

    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct($validator, $response, $errorBag);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_code' => $this->getErrorCode(),
                'message' => 'バリデーションエラーが発生しました。',
                'errors' => $this->errors(),
            ], 422);
        }

        return parent::render($request);
    }
}