<?php
namespace App\Listeners;

use App\Events\UserCreated;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Support\Facades\Log;

class PushUserCreatedToKafka
{
    public function handle(UserCreated $event): void
    {
        Log::info('PushUserCreatedToKafka: Event received', [
            'user_id' => $event->userId
        ]);
        
        try {
            // Используем фасад Kafka для отправки сообщения
            Kafka::publish()
                ->onTopic('auth_events')
                ->withBody([
                    'userId' => $event->userId,
                    'eventType' => 'UserCreated',
                    'timestamp' => now()->toISOString()
                ])
                ->send();
            
            Log::info('✅ UserCreated event sent to Kafka', [
                'user_id' => $event->userId,
                'topic' => 'auth_events'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to send event to Kafka', [
                'user_id' => $event->userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}