<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoUploaded
{
    use Dispatchable, SerializesModels;
    
    public string $photoId;
    public string $url;
    public ?int $quality = null;
    public ?string $format = null;
    
    public function __construct(string $photoId, string $url, ?int $quality = null, ?string $format = null)
    {
        $this->photoId = $photoId;
        $this->url = $url;
        $this->quality = $quality;
        $this->format = $format;
    }
}