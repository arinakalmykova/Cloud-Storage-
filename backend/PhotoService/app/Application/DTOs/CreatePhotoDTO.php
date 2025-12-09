<?php
namespace App\Application\DTOs;

class CreatePhotoDTO
{
    public function __construct(
        public string $userId,
        public string $fileName,
        public string $mimeType = 'image/jpeg'
    ) {}
}
