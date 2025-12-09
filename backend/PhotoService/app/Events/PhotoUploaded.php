<?php
namespace App\Events;

use App\Domain\Photo\Entities\Photo;

class PhotoUploaded 
{
    public Photo $photo;
    public string $uploadUrl;
    public \DateTimeImmutable $occurredAt;

    public function __construct(Photo $photo,string $uploadUrl)
    {
        $this->photo=$photo;
        $this->uploadUrl=$uploadUrl;
        $this->occurredAt= new \DateTimeImmutable();
    }
}