<?php

namespace App\Http;

use Ensi\LaravelInitialEventPropagation\ParseInitialEventHeaderMiddleware;
use Ensi\LaravelInitialEventPropagation\SetInitialEventHttpMiddleware;
use Ensi\LaravelMetrics\HttpMiddleware\HttpMetricsMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        HttpMetricsMiddleware::class,
        \App\Http\Middleware\TrustProxies::class,
        ParseInitialEventHeaderMiddleware::class,
        SetInitialEventHttpMiddleware::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'api' => [],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
