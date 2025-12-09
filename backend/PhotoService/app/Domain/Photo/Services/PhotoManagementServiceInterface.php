<?php

namespace App\Domain\Photo\Services;

use App\Domain\Photo\Entities\Photo;

interface PhotoManagementServiceInterface
{
    public function getUploadUrl(Photo $photo): string;
    
    public function getPublicUrl(string $key): string;
    
    public function uploadFile(string $key, string $filePath): void;
    
    public function getTemporaryUrl(string $key, int $expires = 3600): string;
}