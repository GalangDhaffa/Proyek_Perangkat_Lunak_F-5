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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Biarkan HTTP 4xx (validasi, unauthorized, forbidden, not found) ditangani normal
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return null;
                }

                // Catat detail exception ke log pengembang
                \Illuminate\Support\Facades\Log::error('Server Exception [HTTP 500]: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Respons ramah pengguna tanpa trace SQL
                return response()->json([
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'message' => 'Terjadi kendala pada server saat memproses permintaan. Silakan coba beberapa saat lagi.',
                    'details' => [],
                ], 500);
            }
        });
    })->create();
