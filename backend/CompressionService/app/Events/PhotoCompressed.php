<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoCompressed
{
    use Dispatchable, SerializesModels;

    public string $photoId;
    public int $size;  

    public function __construct(string $photoId, int $size)
    {
        $this->photoId = $photoId;
        $this->size = $size;  
    }
}