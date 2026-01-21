<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoCompressed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $photoId;
    public $compressedUrl;
    public $userId;

    public function __construct(string $photoId, string $compressedUrl, string $userId)
    {
        $this->photoId      = $photoId;
        $this->compressedUrl = $compressedUrl;
        $this->userId       = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
        // или 'photos.' . $this->userId  — любой удобный тебе формат
    }

    public function broadcastAs(): string
    {
        return 'photo.compressed';   // имя события на фронте
    }

    public function broadcastWith(): array
    {
        return [
            'photo_id'       => $this->photoId,
            'compressed_url' => $this->compressedUrl,
        ];
    }
}