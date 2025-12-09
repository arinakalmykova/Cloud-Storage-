<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Photo;

class UpdatePhotoAfterCompression implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'compression';

    public function __construct(public array $payload) {}

    public function handle()
    {
        $photo = Photo::find($this->payload['photo_id']);

        if (!$photo) {
            \Log::error('Photo not found in DB');
            return;
        }

        $photo->url = $this->payload['compressed_url'];
        $photo->status = 'compressed';
        $photo->save();
    }
}
