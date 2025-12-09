<?php
namespace App\Events;

use App\Domain\Photo\Entities\Photo;

class PhotoUploadFailed
{
    public Photo $photo;
    public string $reason;
    public \DateTimeImmutable $occurredAt;

    public function __construct(Photo $photo, string $reason)
    {
        $this->photo = $photo;
        $this->reason = $reason;
        $this->occurredAt = new \DateTimeImmutable();
    }
}
