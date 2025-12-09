<?php
namespace App\Infrastructure\Messaging;

use Illuminate\Support\Facades\Queue;

class AmqpPublisher
{
    public function publish(string $queue, array $payload): void
    {
        Queue::connection('amqp')->pushRaw(json_encode($payload), $queue);
    }
}
