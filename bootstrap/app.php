<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'ensure.role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Rediriger les utilisateurs non-authentifiés vers /login
        $middleware->redirectGuestsTo('/login');

        // Rediriger les utilisateurs déjà connectés selon leur rôle
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            return match ($user?->role) {
                'supplier' => '/supplier/dashboard',
                'seller'   => '/seller/dashboard',
                default    => '/login',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
