<?php

namespace App\Exceptions;

class DataNotFoundException extends OrdinaException
{
    public function __construct(string $resource = 'データ', array $context = [])
    {
        parent::__construct(
            message: "{$resource}が見つかりません。",
            errorCode: 'DATA_NOT_FOUND',
            userMessage: "指定された{$resource}が見つかりません。",
            context: $context,
            code: 404
        );
    }
}