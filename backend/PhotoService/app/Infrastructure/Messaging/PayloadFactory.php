<?php
namespace App\Infrastructure\Messaging;

class PayloadFactory
{
    public static function photoUploaded($event): array
    {
        return [
            'event' => 'PhotoUploaded',
            'photo_id' => $event->photo->id,
            'user_id' => $event->photo->userId,
            'upload_url' => $event->uploadUrl,
            'status' => $event->photo->status->value(),
            'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function photoUploadFailed($event): array
    {
        return [
            'event' => 'PhotoUploadFailed',
            'photo_id' => $event->photo->id,
            'user_id' => $event->photo->userId,
            'error_message' => $event->reason,
            'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
