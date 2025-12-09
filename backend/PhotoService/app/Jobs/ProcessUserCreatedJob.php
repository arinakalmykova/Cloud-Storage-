<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB; // ← ДОБАВЬ ЭТО
use Illuminate\Support\Facades\Log; // ← И ЭТО ТОЖЕ ЛУЧШЕ ДОБАВИТЬ
use App\Models\User;

class ProcessUserCreatedJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        try {
            \Log::info('[PHOTO] DB: ' . DB::connection()->getDatabaseName());
            \Log::info('[PHOTO] Payload: ', $this->payload);

            if (!isset($this->payload['user_id'])) {
                throw new \Exception('No user_id in payload');
            }

            // Попробуй сначала простое создание
            User::create([
                'id' => $this->payload['user_id']
            ]);

            \Log::info('[PHOTO] User created successfully', ['id' => $this->payload['user_id']]);

        } catch (\Throwable $e) {
            \Log::error('[PHOTO] JOB FAILED: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}