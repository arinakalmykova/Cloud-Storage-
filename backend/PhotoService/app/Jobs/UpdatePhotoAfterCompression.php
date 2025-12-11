<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Application\Photo\PhotoService;

class UpdatePhotoAfterCompression implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'compression';

    public function __construct(
        public array $payload,
        private PhotoService $photoService
    ) {}

    public function handle()
    {
        $photoId = $this->payload['photo_id'];
        $compressedSize = $this->payload['compressed_size'] ?? null;
        $compressedUrl = $this->payload['compressed_url'] ?? null;

        $photo = $this->photoService->getById($photoId);

        if (!$photo) {
            \Log::error('Photo not found in DB', ['photo_id' => $photoId]);
            return;
        }

        $photo->markCompressed($compressedUrl, $compressedSize);
        $this->photoService->save($photo);

        // Отправляем событие
        event(new \App\Events\PhotoCompressed($photoId, $compressedUrl));
    }
}