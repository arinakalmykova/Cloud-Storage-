<?php

namespace App\Console\Commands;

use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use App\Application\Compression\CompressionService;
use Illuminate\Support\Facades\Log;
use App\Consumers\PhotoCompressionConsumer; // ← добавьте этот импорт

class ConsumePhotoCompression extends Command
{
    protected $signature = 'kafka:consume-photo-compression
                           {--topic=compression_events : Kafka topic to consume from}
                           {--group=photo-compression-group : Consumer group ID}';
    
    protected $description = 'Consume photo compression events from Kafka and process them';

    public function handle(CompressionService $compressionService): void
    {
        $topic = $this->option('topic');
        $groupId = $this->option('group');
        
        $this->info("🚀 Starting Kafka consumer for topic: {$topic}");
        $this->info("📋 Consumer group: {$groupId}");
        $this->line(str_repeat('-', 50));

        $consumer = Kafka::consumer()
            ->subscribe($topic)
            ->withHandler(new PhotoCompressionConsumer($compressionService))
            ->withConsumerGroupId($groupId)
            ->withAutoCommit()
            ->build();

        $this->info('✅ Consumer started. Waiting for messages...');
        $this->info('Press Ctrl+C to stop');
        
        try {
            $consumer->consume();
        } catch (\Exception $e) {
            $this->error('❌ Consumer error: ' . $e->getMessage());
            Log::error('Kafka consumer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

  
}