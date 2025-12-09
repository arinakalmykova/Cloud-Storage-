<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\CompressionService;
use App\Jobs\SendCompressedResultToPhoto;

class CompressPhotoJob implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'photo';

    public function __construct(public array $payload) {}

    public function handle(CompressionService $service)
    {
        $compressedUrl = $service->compress(
            $this->payload['photo_id'],
            $this->payload['url']
        );

        SendCompressedResultToPhoto::dispatch(
            $this->payload['photo_id'],
            $compressedUrl
        )->onQueue('compression')->onConnection('rabbitmq');
    }
}
