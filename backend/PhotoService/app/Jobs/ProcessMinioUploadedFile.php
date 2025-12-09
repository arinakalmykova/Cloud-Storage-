<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMinioUploadedFile implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'photo';

    public function __construct(public array $event) {}

    public function handle()
    {
        return [
            'photo_id' => $this->event['photo_id'],
            'url' => $this->event['photo_url']
        ];
    }
}
