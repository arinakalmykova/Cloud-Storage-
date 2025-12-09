<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Routing\Router;

class RouteMiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('jwt', JwtMiddleware::class);
    }
}
