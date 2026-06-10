<?php

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
        // Confio en el proxy de Railway (y cualquier plataforma similar) para que Laravel
        // detecte correctamente HTTPS desde la cabecera X-Forwarded-Proto. Sin esto, las URLs
        // y los assets se generarian en http detras del proxy y el navegador los bloquearia.
        // En local no hay proxy, asi que estas cabeceras no llegan y esto no afecta a nada.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
