<?php 

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\ProcessUserCreatedJob;

class PushUserCreatedToRabbitMQ 
{ 
    public function handle(UserCreated $event): void 
    { 
        ProcessUserCreatedJob::dispatch(['user_id' => $event->userId]) ->onConnection('rabbitmq') ->onQueue('auth'); 
    } 
}