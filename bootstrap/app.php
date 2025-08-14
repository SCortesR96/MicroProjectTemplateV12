<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};

use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.verify' => App\Http\Middleware\JWT\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   =>  __('auth.unauthorized'),
                'data'      => []
            ], 401);
        });

        $exceptions->render(function (RouteNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => __('auth.unauthorized'),
                'data'      => []
            ], 401);
        });
    })->create();
