<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteMiddlewareServiceProvider::class,
    App\Queue\Middleware\RabbitMQReconnectMiddleware::class,
];
