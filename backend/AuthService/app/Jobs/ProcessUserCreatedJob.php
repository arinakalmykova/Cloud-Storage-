<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;

class ProcessUserCreatedJob implements ShouldQueue
{
    use Dispatchable,Queueable, InteractsWithQueue, SerializesModels;


    public function __construct(public array $payload) {}

    public function handle(): void
    {
    }
}