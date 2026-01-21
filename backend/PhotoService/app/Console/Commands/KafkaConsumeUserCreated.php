<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Consumers\UserCreatedConsumer;
use Illuminate\Support\Facades\Log;

class KafkaConsumeUserCreated extends Command
{
    protected $signature = 'kafka:consume-user-created
                            {--topic=auth_events : Kafka topic}
                            {--group=user-created-group-1 : Consumer group}
                            {--timeout=0 : Max seconds to run (0 = forever)}';

    protected $description = 'Consumes UserCreated events from Kafka and creates users';

    public function handle(): void
    {
        $topic   = $this->option('topic');
        $groupId = $this->option('group');

        $this->info("🚀 Starting Kafka consumer for UserCreated events");
        $this->info("  → Topic:       {$topic}");
        $this->info("  → Group:       {$groupId}");
        $this->line(str_repeat('-', 50));
        
        try {
            Kafka::consumer([$topic])
                ->subscribe('auth_events')
                ->withHandler(new UserCreatedConsumer())
                ->withConsumerGroupId($groupId)   
                ->build()
                ->consume();
        } catch (\Throwable $e) {
            $this->error("❌ Fatal consumer error: " . $e->getMessage());
            Log::emergency('Kafka consumer fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; 
        }
    }
}