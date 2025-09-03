<?php

namespace App\Exceptions;

class PermissionDeniedException extends OrdinaException
{
    public function __construct(string $action = 'この操作', array $context = [])
    {
        parent::__construct(
            message: "{$action}を実行する権限がありません。",
            errorCode: 'PERMISSION_DENIED',
            userMessage: "{$action}を実行する権限がありません。管理者にお問い合わせください。",
            context: $context,
            code: 403
        );
    }
}