<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class PhotoCompressed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $photoId,
        public string $url
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . auth()->id()), // только своему пользователю
        ];
    }

    public function broadcastAs(): string
    {
        return 'photo.compressed';
    }
}