<?php
namespace App\Domain\Compression\Events;

use App\Domain\Compression\Entities\PhotoCompressionTask;

class PhotoCompressionFailed
{
    public PhotoCompressionTask $task;
    public string $reason;
    public \DateTimeImmutable $occurredAt;

    public function __construct(PhotoCompressionTask $task,string $reason)
    {
        $this->task = $task;
        $this->reason = $reason;
        $this->occurredAt = new \DateTimeImmutable();
    }
}
