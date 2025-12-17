<?php
namespace App\Listeners;

use App\Events\PhotoCompressed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\SendCompressedResultToPhoto;

class SendCompressedResultListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PhotoCompressed $event)
    {
        SendCompressedResultToPhoto::dispatch(['photo_id' => $event->photoId, 'size' => $event->size])->onQueue('compression') ->onConnection('rabbitmq');

        \Log::info('Compression result dispatched to SendCompressedResultToPhoto', [
            'photo_id' => $event->photoId,
            'size' => $event->size,
        ]);
    }
}
