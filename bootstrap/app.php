<?php

use App\Domains\Auth\Http\Middleware\RoleMiddleware;
use App\Domains\Auth\Http\Middleware\AdminMiddleware;
use App\Domains\Auth\Http\Middleware\EnsureActiveAccount;
use App\Domains\Auth\Http\Middleware\EnsureTutorApproval;
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
        $middleware->appendToGroup('web', EnsureActiveAccount::class);
        $middleware->appendToGroup('web', EnsureTutorApproval::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
