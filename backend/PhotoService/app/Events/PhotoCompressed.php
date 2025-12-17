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

    public $photoId;
    public $url;
    public $userId;

    public function __construct(string $photoId, string $url, string $userId)
    {
        $this->photoId = $photoId;
        $this->url = $url;
        $this->userId = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'photo.compressed';
    }
}
