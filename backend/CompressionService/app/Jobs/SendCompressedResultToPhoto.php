<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class SendCompressedResultToPhoto implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'compression';

    public function __construct(
        public string $photoId,
        public string $compressedUrl
    ) {}

    public function handle()
    {
        return [
            'photo_id' => $this->photoId,
            'compressed_url' => $this->compressedUrl
        ];
    }
}
