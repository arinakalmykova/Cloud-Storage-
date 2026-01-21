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

    private function forcedLog(string $message, array $context = []): void
    {
        $entry = date('c') . "  " . $message . "\n";
        if ($context !== []) {
            $entry .= "    " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        }
        $entry .= "\n";

        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public function __invoke(ConsumerMessage $message): void
    {
        $this->forcedLog('Kafka consumer invoked', [
            'topic'     => $message->getTopicName(),
            'key'       => $message->getKey(),
            'offset'    => $message->getOffset(),
            'partition' => $message->getPartition(),
            'timestamp' => $message->getTimestamp(),
            'body_type' => gettype($message->getBody()),
        ]);

        $payload = $message->getBody();

        $this->forcedLog('Raw payload received', ['payload' => $payload]);

        try {
            if (empty($payload['photo_id']) || !isset($payload['size'])) {
                throw new \InvalidArgumentException('Missing required fields: photo_id or size');
            }

            $photoId = $payload['photo_id'];
            $compressedSize = $payload['size'];

            $this->forcedLog("Starting to process compressed photo", [
                'photo_id' => $photoId,
                'size'     => $compressedSize,
            ]);

            $photo = $this->photoService->getById($photoId);

            if (!$photo) {
                $this->forcedLog('Photo not found', ['photo_id' => $photoId]);
                return;
            }

            $this->forcedLog('Photo entity loaded successfully', ['photo_id' => $photoId]);

            $compressedUrl = $photo->getUrl();

            $this->forcedLog('Determined compressed URL', ['url' => $compressedUrl]);

            $photo->markCompressed($compressedUrl, $compressedSize);

            $this->photoService->save($photo);
            broadcast(new PhotoCompressed($photoId,$compressedUrl,$photo->getUserId())); 
            $this->forcedLog('Photo successfully marked as compressed and saved', [
                'photo_id'       => $photoId,
                'compressed_url' => $compressedUrl,
                'compressed_size'=> $compressedSize,
            ]);

            $this->forcedLog('Processing completed successfully ✓', [
                'photo_id' => $photoId,
            ]);
        }
        catch (Throwable $e) {
            $this->forcedLog('ERROR during processing', [
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