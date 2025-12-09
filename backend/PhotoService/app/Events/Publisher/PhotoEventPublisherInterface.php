<?php
namespace App\Events\Publisher;

interface PhotoEventPublisherInterface
{
    public function publish(object $event): void;
}
