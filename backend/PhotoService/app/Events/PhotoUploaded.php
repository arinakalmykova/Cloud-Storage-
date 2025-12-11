<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoUploaded
{
    use Dispatchable, SerializesModels;
    
    public string $photoId;
    public string $url;
    
    public function __construct(string $photoId, string $url)
    {
        $this->photoId = $photoId;
        $this->url = $url;
    }
}