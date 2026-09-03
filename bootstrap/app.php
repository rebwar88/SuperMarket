<?php

use App\Http\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
            'dynamic_permission' => \App\Http\Middleware\CheckDynamicPermission::class,
        ]);
        $middleware->web(append: [
            AuthenticateSession::class,
        ]);
        
        $middleware->alias([
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'verify_shift' => \App\Http\Middleware\VerifyShiftOpen::class,
            'manager_pin' => \App\Http\Middleware\RequireManagerPin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
