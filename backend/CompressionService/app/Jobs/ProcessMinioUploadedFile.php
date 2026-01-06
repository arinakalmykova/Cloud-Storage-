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
use Illuminate\Support\Facades\Log;

class ProcessMinioUploadedFile implements ShouldQueue
{ 
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public array $payload) 
    {
        // Логируем полученные данные
        Log::info('ProcessMinioUploadedFile конструктор', [
            'payload' => $this->payload,
            'quality_is_null' => is_null($this->payload['quality'] ?? null),
            'format_is_null' => is_null($this->payload['format'] ?? null),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);
    }

    public function handle(CompressionService $service): void
    {
        // Логируем перед обработкой
        Log::info('ProcessMinioUploadedFile обработка начинается', [
            'payload' => $this->payload,
            'job_id' => $this->job ? $this->job->getJobId() : 'unknown'
        ]);

        try {
            $result = $service->compress([
                'photo_id'   => $this->payload['photo_id'],
                'source_url' => $this->payload['photo_url'],
                'quality'    => $this->payload['quality'],
                'format'     => $this->payload['format']
            ]);

            event(new PhotoCompressed($result['photo_id'], $result['size']));
            
            Log::info('ProcessMinioUploadedFile успешно завершен', [
                'photo_id' => $result['photo_id'],
                'size' => $result['size']
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProcessMinioUploadedFile ошибка', [
                'payload' => $this->payload,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}