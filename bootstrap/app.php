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
        // Trust all proxies (Cloudflare, Nginx reverse proxy, dsb)
        // Wajib agar Laravel tau dia diakses via HTTPS
        // Tanpa ini signed URL Livewire (upload file) akan gagal di production
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [\App\Http\Middleware\ForcePasswordChange::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
