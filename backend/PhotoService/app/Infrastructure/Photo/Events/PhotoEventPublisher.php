<?php

namespace App\Infrastructure\Photo\Events;

use App\Events\PhotoUploaded;
use App\Events\PhotoUploadFailed;
use App\Events\Publisher\PhotoEventPublisherInterface;
use App\Infrastructure\Messaging\AmqpPublisher;
use App\Infrastructure\Messaging\PayloadFactory;

class PhotoEventPublisher implements PhotoEventPublisherInterface
{
    public function __construct(private AmqpPublisher $publisher) {}

    public function publish(object $event): void
    {
        if ($event instanceof PhotoUploaded) {
            $payload = PayloadFactory::photoUploaded($event);
            $this->publisher->publish('compression', $payload);
        } elseif ($event instanceof PhotoUploadFailed) {
            $payload = PayloadFactory::photoUploadFailed($event);
            $this->publisher->publish('compression', $payload);
        }
    }
}
