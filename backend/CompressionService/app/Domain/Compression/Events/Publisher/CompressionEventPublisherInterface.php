<?php
namespace App\Domain\Compression\Events\Publisher;

interface CompressionEventPublisherInterface
{
    public function publish(object $event): void;
}
