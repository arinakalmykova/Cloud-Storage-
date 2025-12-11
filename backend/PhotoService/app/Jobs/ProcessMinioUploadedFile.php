<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMinioUploadedFile implements ShouldQueue
{

    use Dispatchable,Queueable, InteractsWithQueue, SerializesModels;


    public function __construct(public array $payload) {}

    public function handle(): void
    {
    }
}
