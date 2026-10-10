<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // AI-proxy hot path, registered OUTSIDE the web group: no session, no
        // cookies, no CSRF (see routes/ai_proxy.php for why this matters for
        // tracking latency).
        then: function () {
            require __DIR__.'/../routes/ai_proxy.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The site runs behind a reverse proxy that terminates SSL. Trust its
        // X-Forwarded-* headers (most importantly X-Forwarded-Proto) so Laravel
        // sees the real scheme/client and generates https URLs everywhere.
        $middleware->trustProxies(at: '*');

        // AI-engine proxy endpoints receive POSTs from mobile.html and the
        // dashboard <img> (no Laravel session/CSRF token exists there). The
        // engine itself is the only backend behind them, so skip CSRF here.
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
