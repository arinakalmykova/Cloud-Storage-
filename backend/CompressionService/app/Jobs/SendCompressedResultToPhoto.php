<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCompressedResultToPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle()
    {
        // Тут логика отправки результата куда нужно
        \Log::info('Sending compressed result to photo service', [
            'photo_id' => $this->photoId,
            'size' => $this->size,
        ]);
    }
}
