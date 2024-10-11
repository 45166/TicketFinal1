<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Global HTTP middleware
    ];

    protected $middlewareGroups = [
        'web' => [
            // Web middleware group
            \App\Http\Middleware\ForceHttps::class,
        ],
        'api' => [
            // API middleware group
        ],
    ];

    protected $routeMiddleware = [
        // Route-specific middleware
    ];
}
