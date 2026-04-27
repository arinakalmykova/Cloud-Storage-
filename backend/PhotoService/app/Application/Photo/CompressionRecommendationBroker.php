<?php

namespace App\Application\Photo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Junges\Kafka\Facades\Kafka;
use RuntimeException;

class CompressionRecommendationBroker
{
    private const CACHE_PREFIX = 'compression_recommendation:';
    private const WAIT_TIMEOUT_SECONDS = 90;
    private const POLL_INTERVAL_MICROSECONDS = 250000;

    public function requestRecommendation(
        string $filePath,
        string $mimeType,
        string $contentType
    ): array {
        return $this->dispatchCompressionRequest(
            eventType: 'RecommendationRequested',
            filePath: $filePath,
            mimeType: $mimeType,
            contentType: $contentType
        );
    }

    public function requestEstimate(
        string $filePath,
        string $mimeType,
        string $contentType,
        string $format,
        int $quality
    ): array {
        return $this->dispatchCompressionRequest(
            eventType: 'CompressionEstimateRequested',
            filePath: $filePath,
            mimeType: $mimeType,
            contentType: $contentType,
            requestedFormat: $format,
            requestedQuality: $quality
        );
    }

    private function dispatchCompressionRequest(
        string $eventType,
        string $filePath,
        string $mimeType,
        string $contentType,
        ?string $requestedFormat = null,
        ?int $requestedQuality = null
    ): array {
        $correlationId = (string) Str::uuid();
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $tempKey = 'recommendations/' . $correlationId . ($extension !== '' ? '.' . $extension : '');
        $cacheKey = self::CACHE_PREFIX . $correlationId;

        $content = @file_get_contents($filePath);
        if ($content === false || $content === '') {
            throw new RuntimeException('Failed to read uploaded file for compression request.');
        }

        Storage::disk('s3_backend')->put($tempKey, $content, [
            'ContentType' => $mimeType,
        ]);

        try {
            Kafka::publish()
                ->onTopic('compression_events')
                ->withBody([
                    'eventType' => $eventType,
                    'correlation_id' => $correlationId,
                    'source_key' => $tempKey,
                    'content_type' => $contentType,
                    'requested_format' => $requestedFormat,
                    'requested_quality' => $requestedQuality,
                    'timestamp' => now()->toISOString(),
                ])
                ->send();

            $deadline = microtime(true) + self::WAIT_TIMEOUT_SECONDS;

            while (microtime(true) < $deadline) {
                $recommendation = Cache::get($cacheKey);

                if (is_array($recommendation)) {
                    Cache::forget($cacheKey);

                    return $recommendation;
                }

                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }
        } finally {
            if (Storage::disk('s3_backend')->exists($tempKey)) {
                Storage::disk('s3_backend')->delete($tempKey);
            }
        }

        throw new RuntimeException('Timed out while waiting for compression parameters.');
    }

    public static function cacheKey(string $correlationId): string
    {
        return self::CACHE_PREFIX . $correlationId;
    }
}
