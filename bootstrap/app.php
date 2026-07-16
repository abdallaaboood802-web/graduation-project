<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ── CORS: السماح لـ Next.js بالاتصال بالـ API ─────────────────────
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        // ── تسجيل Middleware الأدوار بـ alias مختصر ──────────────────────
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // استبعاد مسارات الـ API من حماية CSRF
        $middleware->validateCsrfTokens(except: ['api/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
