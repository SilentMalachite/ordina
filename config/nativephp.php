<?php

return [
    // NativePHP（デスクトップ連携機能）の有効/無効
    // テスト環境では明示的に無効化します（phpunit.xml で上書き）。
    'enabled' => env('NATIVEPHP_ENABLED', true),
];

