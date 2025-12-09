<?php
namespace App\Domain\Compression\Events;

use App\Domain\Compression\Entities\PhotoCompressionTask;

class PhotoCompressed
{
    public PhotoCompressionTask $task;
    public string $compressedUrl;
    public \DateTimeImmutable $occurredAt;

    public function __construct(PhotoCompressionTask $task, string $compressedUrl)
    {
        $this->task = $task;
        $this->compressedUrl = $compressedUrl;
        $this->occurredAt = new \DateTimeImmutable();
    }
}
