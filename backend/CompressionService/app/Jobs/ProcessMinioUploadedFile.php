<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Application\Compression\CompressionService;
use App\Jobs\SendCompressedResultToPhoto;
use App\Events\PhotoCompressed;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMinioUploadedFile implements ShouldQueue
{ 
    use Dispatchable,Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle(CompressionService $service): void
    {
        $result = $service->compress([
            'photo_id'   => $this->payload['photo_id'],
            'source_url' => $this->payload['photo_url'],
        ]);

       event(new PhotoCompressed($result['photo_id'], $result['size']));

    }
}



   