<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (AuthorizationException $e, Request $request) {
        return response()->json([
          'message' => $e->getMessage(),
          'path' => '/' . $request->path(),
          'status' => 403,
          'error' => 'Forbidden',
          'timestamp' => now()->toIso8601String(),
        ], 403);
      });
    })
    ->create();
