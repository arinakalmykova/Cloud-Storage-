<?php

namespace App\Infrastructure\Compression\Events;

use Illuminate\Support\Facades\Queue;
use App\Domain\Compression\Events\PhotoCompressed;
use App\Domain\Compression\Events\Publisher\CompressionEventPublisherInterface;
use App\Infrastructure\Messaging\AmqpPublisher;
use App\Infrastructure\Messaging\PayloadFactory;

class CompressEventPublisher implements CompressionEventPublisherInterface
{
    public function __construct(private AmqpPublisher $publisher) {}

    public function publish(object $event): void
    {
        if ($event instanceof PhotoCompressed) {
            $payload = PayloadFactory::photoUploaded($event);
            $this->publisher->publish('photo', $payload);
        } elseif ($event instanceof PhotoCompressionFailed) {
            $payload = PayloadFactory::photoUploadFailed($event);
            $this->publisher->publish('photo', $payload);
        }
    }
}






