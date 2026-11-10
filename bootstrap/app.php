<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Klien SOAP legacy tidak memiliki sesi CSRF; route API tetap terlindungi.
        $middleware->validateCsrfTokens(except: ['soap/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua kegagalan API mengikuti RFC 7807, sedangkan halaman Blade tetap HTML.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $exception): bool {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            if (! in_array($status, [401, 404, 422], true)) {
                return null;
            }

            $titles = [
                401 => 'Unauthorized',
                404 => 'Not Found',
                422 => 'Unprocessable Content',
            ];

            return response()->json([
                'type' => 'https://httpstatuses.com/' . $status,
                'title' => $titles[$status],
                'status' => $status,
                'detail' => $exception instanceof ValidationException
                    ? 'Data yang dikirim tidak valid.'
                    : ($exception->getMessage() ?: $titles[$status]),
                'instance' => $request->getRequestUri(),
            ], $status, [
                'Content-Type' => 'application/problem+json',
            ]);
        });
    })->create();
