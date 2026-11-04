<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB
        );
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 429 dari pembatas percobaan (throttle) tetap memakai envelope JSON standar
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            $retry = (int) ($e->getHeaders()['Retry-After'] ?? 60);

            return response()->json([
                'status' => 'error',
                'message' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$retry} detik.",
                'data' => null,
                'meta' => ['timestamp' => now()->toIso8601String(), 'version' => '1.0.0', 'retry_after' => $retry],
                'error_code' => 'TOO_MANY_REQUESTS',
                'code' => 'TOO_MANY_REQUESTS',
            ], 429, $e->getHeaders());
        });
    })->create();
