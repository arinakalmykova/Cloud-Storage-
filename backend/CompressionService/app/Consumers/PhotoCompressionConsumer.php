<?php

namespace App\Consumers;

use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;
use App\Application\Compression\CompressionService;
use Junges\Kafka\Facades\Kafka;
use Throwable;

class PhotoCompressionConsumer
{
    public function __construct(
        protected CompressionService $compressionService
    ) {
        // Можно внедрить через контейнер, если используешь auto-resolution
    }

    public function __invoke(ConsumerMessage $message): void
    {
        $payload = $message->getBody();

        Log::info('📩 Kafka message received', [
            'topic'     => $message->getTopicName(),
            'key'       => $message->getKey(),
            'offset'    => $message->getOffset(),
            'partition' => $message->getPartition(),
            'payload'   => $payload,
        ]);

        try {
            if (empty($payload['photo_id']) || empty($payload['photo_url'])) {
                throw new \InvalidArgumentException('Missing required fields: photo_id or photo_url');
            }

            $this->info("🔧 Processing photo_id: " . ($payload['photo_id'] ?? '—'));

            $result = $this->compressionService->compress([
                'photo_id'   => $payload['photo_id'],
                'source_url' => $payload['photo_url'],
                'quality'    => $payload['quality'] ,
                'format'     => $payload['format'] ,
            ]);

            $this->info("✅ Compression success", [
                'photo_id' => $result['photo_id'],
                'size'     => $result['size'] ?? '—',
            ]);

            Log::info('Photo compression completed', $result);

            // Отправляем результат в другой топик
            $resultMessage = [
                'photo_id'         => $result['photo_id'],
                'size'             => $result['size']
            ];

            Kafka::publish()
                ->onTopic('photo_events')
                ->withBody($resultMessage)
                ->send();

            Log::info('→ Result published to photo_events', $resultMessage);

        } catch (Throwable $e) {
            Log::error('❌ Photo compression failed', [
                'payload' => $payload,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Здесь можно решать — rethrow, dead letter queue, retry и т.д.
            // Для простоты — просто логируем и продолжаем
        }
    }

    // Вспомогательный метод для вывода в консоль (если команда запущена в терминале)
    private function info(string $message, array $context = []): void
    {
        if (app()->runningInConsole()) {
            info($message, $context);
        }
    }
}