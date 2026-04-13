<?php

namespace App\Consumers;

use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;
use App\Application\Photo\PhotoService;
use App\Events\PhotoCompressed;
use Throwable;

class PhotoCompressedConsumer
{
    private string $logFile;

    public function __construct(
        protected PhotoService $photoService
    ) {
        $this->logFile = storage_path('logs/kafka-forced.log');
    }


    public function __invoke(ConsumerMessage $message): void
    {
        Log::info('Kafka consumer invoked', [
            'topic'     => $message->getTopicName(),
            'key'       => $message->getKey(),
            'offset'    => $message->getOffset(),
            'partition' => $message->getPartition(),
            'timestamp' => $message->getTimestamp(),
            'body_type' => gettype($message->getBody()),
        ]);

        $payload = $message->getBody();

        Log::info('Raw payload received', ['payload' => $payload]);

        try {
            if (empty($payload['photo_id']) || !isset($payload['size'])) {
                throw new \InvalidArgumentException('Missing required fields: photo_id or size');
            }

            $photoId = $payload['photo_id'];
            $compressedSize = $payload['size'];

            Log::info("Starting to process compressed photo", [
                'photo_id' => $photoId,
                'size'     => $compressedSize,
            ]);

            $photo = $this->photoService->getById($photoId);

            if (!$photo) {
                Log::info('Photo not found', ['photo_id' => $photoId]);
                return;
            }

            Log::info('Photo entity loaded successfully', ['photo_id' => $photoId]);

            $compressedUrl = $photo->getUrl();

            Log::info('Determined compressed URL', ['url' => $compressedUrl]);

            $photo->markCompressed($compressedUrl, $compressedSize);

            $this->photoService->save($photo);
            broadcast(new PhotoCompressed($photoId,$compressedUrl,$photo->getUserId(),$compressedSize)); 
            Log::info('Photo successfully marked as compressed and saved', [
                'photo_id'       => $photoId,
                'compressed_url' => $compressedUrl,
                'compressed_size'=> $compressedSize,
            ]);

            Log::info('Processing completed successfully ✓', [
                'photo_id' => $photoId,
            ]);
        }
        catch (Throwable $e) {
            Log::info('ERROR during processing', [
                'payload' => $payload,
                'error'   => $e->getMessage(),
                'class'   => get_class($e),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }
}