<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\ProcessUserCreatedJob;

class PushUserCreatedToRabbitMQ
{
    public function handle(UserCreated $event): void
    {
        // ← ЭТО РАБОТАЕТ ВСЕГДА. pushRaw() — НЕТ!
        ProcessUserCreatedJob::dispatch(['user_id' => $event->userId])
            ->onConnection('rabbitmq')
            ->onQueue('auth');
    }
}