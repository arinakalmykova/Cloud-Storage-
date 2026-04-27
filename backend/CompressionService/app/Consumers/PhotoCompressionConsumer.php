<?php

namespace App\Consumers;

use App\Application\Compression\CompressionRecommendationService;
use App\Application\Compression\CompressionService;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Throwable;

class PhotoCompressionConsumer
{
    public function __construct(
        protected CompressionService $compressionService,
        protected CompressionRecommendationService $compressionRecommendationService
    ) {
    }

    public function __invoke(ConsumerMessage $message): void
    {
        $payload = $message->getBody();

        Log::info('Kafka message received', [
            'topic' => $message->getTopicName(),
            'key' => $message->getKey(),
            'offset' => $message->getOffset(),
            'partition' => $message->getPartition(),
            'payload' => $payload,
        ]);

        try {
            if (($payload['eventType'] ?? null) === 'RecommendationRequested') {
                $this->handleRecommendationRequest($payload);
                return;
            }

            if (($payload['eventType'] ?? null) === 'CompressionEstimateRequested') {
                $this->handleCompressionEstimateRequest($payload);
                return;
            }

            $this->handleCompressionRequest($payload);
        } catch (Throwable $e) {
            Log::error('Photo compression consumer failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function handleRecommendationRequest(array $payload): void
    {
        if (empty($payload['correlation_id']) || empty($payload['source_key'])) {
            throw new \InvalidArgumentException('Missing required fields: correlation_id or source_key');
        }

        $contentType = (string) ($payload['content_type'] ?? 'mixed');

        $recommendation = $this->compressionRecommendationService->recommend(
            $payload['source_key'],
            $contentType
        );

        $resultMessage = [
            'eventType' => 'RecommendationReady',
            'correlation_id' => $payload['correlation_id'],
            'format' => $recommendation['format'],
            'quality' => $recommendation['quality'],
            'estimated_size' => $recommendation['estimated_size'],
            'saved_bytes' => $recommendation['saved_bytes'],
            'saved_percent' => $recommendation['saved_percent'],
        ];

        Kafka::publish()
            ->onTopic('photo_events')
            ->withBody($resultMessage)
            ->send();

        Log::info('Recommendation result published to photo_events', $resultMessage);
    }

    private function handleCompressionEstimateRequest(array $payload): void
    {
        if (
            empty($payload['correlation_id']) ||
            empty($payload['source_key']) ||
            empty($payload['requested_format']) ||
            !isset($payload['requested_quality'])
        ) {
            throw new \InvalidArgumentException(
                'Missing required fields: correlation_id, source_key, requested_format or requested_quality'
            );
        }

        $contentType = (string) ($payload['content_type'] ?? 'mixed');

        $estimate = $this->compressionRecommendationService->estimateSpecific(
            $payload['source_key'],
            $contentType,
            (string) $payload['requested_format'],
            (int) $payload['requested_quality']
        );

        $resultMessage = [
            'eventType' => 'CompressionEstimateReady',
            'correlation_id' => $payload['correlation_id'],
            'format' => $estimate['format'],
            'quality' => $estimate['quality'],
            'estimated_size' => $estimate['estimated_size'],
            'saved_bytes' => $estimate['saved_bytes'],
            'saved_percent' => $estimate['saved_percent'],
        ];

        Kafka::publish()
            ->onTopic('photo_events')
            ->withBody($resultMessage)
            ->send();

        Log::info('Compression estimate published to photo_events', $resultMessage);
    }

    private function handleCompressionRequest(array $payload): void
    {
        if (empty($payload['photo_id']) || empty($payload['photo_url'])) {
            throw new \InvalidArgumentException('Missing required fields: photo_id or photo_url');
        }

        $this->info('Processing photo compression', [
            'photo_id' => $payload['photo_id'],
        ]);

        $result = $this->compressionService->compress([
            'photo_id' => $payload['photo_id'],
            'source_url' => $payload['photo_url'],
            'quality' => $payload['quality'],
            'format' => $payload['format'],
        ]);

        Log::info('Photo compression completed', $result);

        $resultMessage = [
            'eventType' => 'PhotoCompressed',
            'photo_id' => $result['photo_id'],
            'size' => $result['size'],
        ];

        Kafka::publish()
            ->onTopic('photo_events')
            ->withBody($resultMessage)
            ->send();

        Log::info('Compression result published to photo_events', $resultMessage);
    }

    private function info(string $message, array $context = []): void
    {
        if (app()->runningInConsole()) {
            info($message, $context);
        }
    }
}
