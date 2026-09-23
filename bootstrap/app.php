<?php

use App\Http\Middleware\CheckSiteMaintenance;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function ($request): string {
            // /admin/initialiser passe par la connexion du site : le panneau refuse les non-admins.
            return $request->is('admin', 'admin/*') && ! $request->is('admin/initialiser')
                ? route('filament.admin.auth.login')
                : route('login');
        });
        $middleware->web(append: [
            CheckSiteMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
