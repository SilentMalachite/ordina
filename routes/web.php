<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// メインのWebルート
require __DIR__.'/web-main.php';

// 管理者用ルート
require __DIR__.'/admin.php';

// 認証ルート
require __DIR__.'/auth.php';
