<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway / proxies reversos (HTTPS + IP real) — PRG Fase 2
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtAuthenticate::class,
            'profile' => \App\Http\Middleware\EnsureUserProfile::class,
            'clinic' => \App\Http\Middleware\ResolveClinicTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn ($request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            \Illuminate\Support\Facades\Log::warning('api.query_exception', [
                'sqlstate' => $e->errorInfo[0] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Serviço temporariamente indisponível. Tente novamente em instantes.',
            ], 503);
        });

        // Evita vazar stack/SQL em produção mesmo se APP_DEBUG=true por engano
        $exceptions->render(function (\Throwable $e, $request) {
            if (! app()->environment('production')) {
                return null;
            }
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                || $e instanceof \Illuminate\Database\QueryException) {
                return null;
            }

            \Illuminate\Support\Facades\Log::error('api.unhandled', [
                'exception' => $e::class,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.',
            ], 500);
        });
    })->create();
