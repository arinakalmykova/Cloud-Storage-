<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\CompressionService;

class CompressPhotoJob implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'photo';

    public function __construct(public array $payload) {}

    public function handle(CompressionService $service)
    {
       $result = $service->compress(['photo_id' => $this->payload['photo_id'], 'source_url' => $this->payload['url']]);
       dispatch(new SendCompressedResultToPhoto(
        photoId: $result['photo_id'],
        size: $result['size']
       ))->onQueue('compression')->onConnection('rabbitmq');
    }
}
