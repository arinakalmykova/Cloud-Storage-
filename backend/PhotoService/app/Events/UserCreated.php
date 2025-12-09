<?php
namespace App\Events;

class UserCreated
{
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
