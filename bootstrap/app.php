<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Http\Middleware\ToSweetAlert;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web([
            ToSweetAlert::class,
        ]);
        $middleware->alias([
            'module.access' => EnsureModuleAccess::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Saat server error (5xx) di production, arahkan user ke halaman login.
        $exceptions->render(function (Throwable $e, Request $request) {
            // Saat debug aktif, request JSON, atau halaman login sendiri yang error, pakai handler bawaan.
            if (config('app.debug') || $request->expectsJson() || $request->routeIs('login')) {
                return null;
            }

            // Validasi dan autentikasi punya alur redirect sendiri; jangan dianggap 500.
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status >= 500) {
                return redirect()
                    ->route('login')
                    ->with('toast_error', 'Terjadi kesalahan pada server. Silakan masuk kembali.');
            }

            return null;
        });
    })->create();
