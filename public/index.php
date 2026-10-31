<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Jika dijalankan melalui PHP built-in server (cli-server), layani file statis (.js, .css, gambar) secara langsung
if (PHP_SAPI === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $staticPath = __DIR__.$uri;
    if ($uri !== '/' && is_file($staticPath)) {
        return false;
    }
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
