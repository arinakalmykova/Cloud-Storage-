<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\UserCreated;
use App\Listeners\PushUserCreatedToKafka;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreated::class => [
            PushUserCreatedToKafka::class,
        ],
    ];
}
