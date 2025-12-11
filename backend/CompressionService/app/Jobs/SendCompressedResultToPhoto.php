<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;

class SendCompressedResultToPhoto implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'compression';

    public function __construct(
        public string $photoId,
        public string $compressedUrl,
        public int $size
    ) {}

    public function handle()
    {
        return [
            'photo_id' => $this->photoId,
            'size' => $this->size,
        ];
    }
}
