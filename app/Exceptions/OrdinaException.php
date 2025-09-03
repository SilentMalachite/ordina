<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrdinaException extends Exception
{
    protected $errorCode;
    protected $userMessage;
    protected $context;

    public function __construct(
        string $message = '',
        string $errorCode = 'ORDINA_ERROR',
        string $userMessage = '',
        array $context = [],
        int $code = 500,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->errorCode = $errorCode;
        $this->userMessage = $userMessage ?: $message;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_code' => $this->getErrorCode(),
                'message' => $this->getUserMessage(),
                'context' => $this->getContext(),
            ], $this->getCode());
        }

        return response()->view('errors.ordina', [
            'error_code' => $this->getErrorCode(),
            'message' => $this->getUserMessage(),
            'context' => $this->getContext(),
        ], $this->getCode());
    }
}