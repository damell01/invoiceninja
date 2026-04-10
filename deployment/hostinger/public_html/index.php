<?php

define('LARAVEL_START', microtime(true));

$appFolder = __DIR__ . '/dbellbilling';

if (! is_file($appFolder . '/bootstrap/app.php')) {
    http_response_code(500);
    echo 'DBell Billing bootstrap path not found. Expected app folder at /public_html/dbellbilling';
    exit;
}

require $appFolder . '/vendor/autoload.php';

$app = require_once $appFolder . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
