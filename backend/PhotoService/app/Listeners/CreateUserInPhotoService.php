<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;

class CreateUserInPhotoService
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
   public function handle(UserCreated $event)
    {
        User::firstOrCreate([
            'id' => $event->payload['user_id']
        ]);
    }
}
