<?php

namespace App\Infrastructure\Photo\Services;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\Services\PhotoManagementServiceInterface;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;
use Illuminate\Support\Facades\Log;

class MinioPhotoManagement implements PhotoManagementServiceInterface
{
    private $publicDisk;     
    private $backendDisk;     
    private string $bucket;

    public function __construct()
    {
        $this->publicDisk = Storage::disk('s3_public');
        $this->backendDisk = Storage::disk('s3_backend');
        $this->bucket = env('AWS_BUCKET');
    }

    public function getUploadUrl(Photo $photo): string
    {
        $key = "uploads/{$photo->getId()}";

        $cmd = $this->publicDisk->getClient()->getCommand('putObject', [
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        $request = $this->publicDisk->getClient()->createPresignedRequest($cmd, '+15 minutes');
        return (string) $request->getUri();
    }

    public function getPublicUrl(string $key): string
    {
        return $this->publicDisk->url($key);
    }

    public function uploadFile(string $key, string $filePath): void
    {
        $this->publicDisk->put($key, file_get_contents($filePath));
    }

    public function getTemporaryUrl(string $key, int $expires = 3600): string
    {
        return $this->publicDisk->temporaryUrl($key, now()->addSeconds($expires));
    }

    public function deleteFile(string $key): void
    {  
        $this->backendDisk->delete($key);
    }

    public function listContents(string $prefix = ''): array
    {
        $listing = $this->backendDisk->listContents($prefix, true);

        return array_filter(
            iterator_to_array($listing),
            fn(StorageAttributes $item) => $item->isFile()
        );
    }
}