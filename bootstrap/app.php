<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - applies to all requests
        $middleware->append(\App\Http\Middleware\SecureHeaders::class);
        $middleware->append(\App\Http\Middleware\PerformanceMonitoring::class);
        $middleware->append(\App\Http\Middleware\CheckUserActive::class);

        // Route middleware aliases
        $middleware->alias([
            'user.type' => \App\Http\Middleware\CheckUserType::class,
            'global.admin' => \App\Http\Middleware\GlobalAdminMiddleware::class,
            'security.admin' => \App\Http\Middleware\SecurityAdminMiddleware::class,
            'sas.drrm.admin' => \App\Http\Middleware\SasOrDrrmAdminMiddleware::class,
            'marketing.admin' => \App\Http\Middleware\MarketingAdminMiddleware::class,
            'file.upload.security' => \App\Http\Middleware\FileUploadSecurity::class,
            'patrol.monitor' => \App\Http\Middleware\CheckPatrolMonitorAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
