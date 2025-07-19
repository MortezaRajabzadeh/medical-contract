<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\RepositoryServiceProvider;
use App\Providers\AuthServiceProvider;
use Illuminate\Routing\Router as Routing;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        channels: __DIR__.'/../routes/channels.php'
    )
    ->withProviders([
        // ثبت AuthServiceProvider برای مدیریت مجوزها
        App\Providers\AuthServiceProvider::class,
        App\Providers\RepositoryServiceProvider::class,
        App\Providers\FilamentServiceProvider::class, // سرویس پروایدر برای RTL کامل پنل Filament
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // ثبت میدلور Authenticate سفارشی
        // در لاراول 11، باید از روش alias استفاده کنیم
        $middleware->alias([
            'auth' => App\Http\Middleware\Authenticate::class,
            'admin' => App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
