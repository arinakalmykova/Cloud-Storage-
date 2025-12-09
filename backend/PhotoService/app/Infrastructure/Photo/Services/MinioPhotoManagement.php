<?php

namespace App\Infrastructure\Photo\Services;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\Services\PhotoManagementServiceInterface;
use Illuminate\Support\Facades\Storage;

class MinioPhotoManagement implements PhotoManagementServiceInterface
{
    private $disk;
    private string $bucket;

    public function __construct()
    {
        $this->disk = Storage::disk('s3');
        $this->bucket = env('AWS_BUCKET');
    }

    public function getUploadUrl(Photo $photo): string
    {
        $key = "uploads/{$photo->getId()}/original";

        $cmd = $this->disk->getClient()->getCommand('putObject', [
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        $request = $this->disk->getClient()->createPresignedRequest($cmd, '+15 minutes');
        return (string) $request->getUri();
    }

    public function getPublicUrl(string $key): string
    {
        return 'http://127.0.0.1:9000/photo/' . ltrim($key, '/');
    }

    public function uploadFile(string $key, string $filePath): void
    {
        $this->disk->put($key, file_get_contents($filePath));
    }

    public function getTemporaryUrl(string $key, int $expires = 3600): string
    {
        return $this->disk->temporaryUrl($key, now()->addSeconds($expires));
    }
}