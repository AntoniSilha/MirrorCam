<?php

use App\Http\Middleware\EnsureUserIsCustomer;
use App\Http\Middleware\EnsureUserIsMitra;
<<<<<<< HEAD
use App\Http\Middleware\EnsureUserIsSuperAdmin;
=======
use App\Http\Middleware\RedirectToCanonicalHost;
>>>>>>> 8a29d4e6e937be9d6d4dde2b490c8ab3ae9a341a
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            RedirectToCanonicalHost::class,
        ]);

        $middleware->alias([
            'role.user' => EnsureUserIsCustomer::class,
            'role.mitra' => EnsureUserIsMitra::class,
            'role.superadmin' => EnsureUserIsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
