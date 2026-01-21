<?php

namespace App\Consumers;

use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Throwable;

class UserCreatedConsumer
{
    public function __invoke(ConsumerMessage $message): void
    {
        $payload = $message->getBody();

        $this->info('📩 Получено событие из Kafka', [
            'topic'     => $message->getTopicName(),
            'key'       => $message->getKey(),
            'offset'    => $message->getOffset(),
            'partition' => $message->getPartition(),
            'payload'   => $payload,
        ]);

        try {
            $userId    = $payload['userId']    ?? null;
            $eventType = $payload['eventType'] ?? 'unknown';

            if (!$userId || $eventType !== 'UserCreated') {
                Log::warning('Некорректное событие UserCreated — пропускаем', $payload);
                return;
            }

            $this->info("🔄 Обрабатываем создание пользователя #{$userId}");

            // Здесь можно добавить больше полей из payload, если они приходят
            User::create([
                'id'   => $userId,
            ]);

            $this->info('✅ Пользователь успешно создан из Kafka события', ['user_id' => $userId]);

        } catch (Throwable $e) {
            $this->info('❌ Ошибка при обработке UserCreated', [
                'payload' => $payload,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Опционально: можно бросить исключение, чтобы сообщение ушло в DLQ
            // throw $e;
        }
    }

    /**
     * Вспомогательный вывод в консоль (только если запущено в терминале)
     */
    private function info(string $message, array $context = []): void
    {
        if (app()->runningInConsole()) {
            info($message, $context);
        }
    }
}