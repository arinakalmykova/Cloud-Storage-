<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\PhotoCompressed;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Application\Photo\PhotoService;
    
class SendCompressedResultToPhoto implements ShouldQueue
{
    use Dispatchable,Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public array $payload
    ) {}

    public function handle(PhotoService $photoService)
    {
        \Log::info('Compression result received', $this->payload);

        $photoId = $this->payload['photo_id'];
        $compressedSize = $this->payload['size'];

        $photo = $photoService->getById($photoId);
        $compressedUrl = $photo->getUrl();
        if (!$photo) {
            \Log::error('Photo not found', ['photo_id' => $photoId]);
            return;
        }

        $photo->markCompressed($compressedUrl,$compressedSize);
        $photoService->save($photo);

        // broadcast(new \App\Events\PhotoCompressed($photoId, $compressedUrl, $photo->getUserId()));
    }
}
