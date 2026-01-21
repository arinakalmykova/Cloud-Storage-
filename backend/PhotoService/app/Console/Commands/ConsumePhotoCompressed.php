<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Consumers\PhotoCompressedConsumer;
use App\Application\Photo\PhotoService;
use Illuminate\Support\Facades\Log;

class ConsumePhotoCompressed extends Command
{
    protected $signature = 'kafka:consume-photo-compressed
                           {--topic=photo_events : Kafka topic to consume from}
                           {--group=photo-events-group : Consumer group ID}';

    protected $description = 'Consume photo compressed events from Kafka (photo_events topic)';

    public function handle(PhotoService $photoService): void
    {
        $topic = $this->option('topic');
        $group = $this->option('group');

        $this->info("Starting Kafka consumer: photo_events @ group: {$group}");

        Kafka::consumer()
            ->subscribe('photo_events')
            ->withHandler(new PhotoCompressedConsumer($photoService))
            ->withConsumerGroupId($group)
            ->build()
            ->consume();
    }
}