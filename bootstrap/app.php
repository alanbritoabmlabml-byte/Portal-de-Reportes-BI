<?php

use App\Http\Middleware\RegistrarRecorrido;
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
        // Detrás del proxy de la nube (Render, Railway…) la petición llega por
        // HTTP aunque el visitante use HTTPS: se confía en las cabeceras X-Forwarded-*
        $middleware->trustProxies(at: '*');

        // Bitácora de recorrido: se ejecuta al final, cuando ya se sabe qué
        // ruta respondió y con qué código
        $middleware->web(append: [RegistrarRecorrido::class]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('menu'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
