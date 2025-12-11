<?php
namespace App\Listeners;

use App\Events\PhotoUploaded;
use App\Jobs\ProcessMinioUploadedFile;

class PushPhotoToCompressionQueue
{
    public function handle(PhotoUploaded $event): void
    {
        ProcessMinioUploadedFile::dispatch([
            'photo_id' => $event->photoId,
            'photo_url' => $event->url
        ])->onConnection('rabbitmq')->onQueue('photo');
    }
}
