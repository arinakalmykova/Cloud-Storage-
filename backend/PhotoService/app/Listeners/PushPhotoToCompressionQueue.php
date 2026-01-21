<?php
namespace App\Listeners;

use App\Events\PhotoUploaded;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Support\Facades\Log;

class PushPhotoToCompressionQueue
{
    public function handle(PhotoUploaded $event): void
    {
        Log::info('📤 Sending photo to Kafka compression queue', [
            'photo_id' => $event->photoId,
            'url' => $event->url
        ]);

        // Отправляем в Kafka
        Kafka::publish()
            ->onTopic('compression_events')
            ->withBody([
                'eventType' => 'PhotoUploaded',
                'photo_id' => $event->photoId,
                'photo_url' => $event->url,
                'quality' => $event->quality,
                'format' => $event->format,
                'timestamp' => now()->toISOString()
            ])
            ->send();
            
        Log::info('✅ Photo event sent to Kafka', ['photo_id' => $event->photoId]);
    }
}