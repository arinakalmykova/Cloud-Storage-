<?php

namespace App\Application\Photo;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Services\PhotoManagementServiceInterface;
use Illuminate\Support\Str;
use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Application\DTOs\CreatePhotoDTO;
use App\Events\PhotoUploaded;
use App\Application\Tag\TagService;
use App\Application\Color\ColorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ColorThief\ColorThief;
use Carbon\Carbon;
use Imagick;

class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $repository,
        private PhotoManagementServiceInterface $minioService,
        private TagService $tagService,
        private ColorService $colorService
        
    ) {}

    public function createUploadIntent(CreatePhotoDTO $dto): Photo 
    {
        $photo = new Photo(
            id: Str::uuid()->toString(),
            userId: $dto->userId,
            fileName: $dto->fileName,
            description: $dto->description,
            url: null, 
            status: PhotoStatus::pendingUpload(),
            size: null,
            folderId: null,  
            folderName: null,
            format: null,      
            createdAt: null,   
            tags: [] 
        );

        $presignedUrl = $this->minioService->getUploadUrl($photo);
        $originalKey = "uploads/{$photo->getId()}";
        $photo->markPendingUpload($originalKey, $presignedUrl);
        $this->repository->save($photo);

        return $photo;
    }

    public function getById(string $id): ?Photo
    {
        return $this->repository->findById($id);
    }
    
    public function save(Photo $photo): void
    {
        $this->repository->save($photo);
    }

    public function updateTags(string $photoId, string $userId, array $tagNames): void
    {
        $photo = $this->repository->findById($photoId);

        if (!$photo || !$photo->isOwnedBy($userId)) {
            throw new \Exception('Not found');
        }

        $tagIds = [];

        foreach ($tagNames as $name) {
            $tag = $this->tagService->getOrCreate($name);
            $tagIds[] = $tag->getId();
        }

        $this->repository->syncTags($photo, $tagIds);
    }

    
    public function processUploadedPhoto(Photo $photo): bool
    {
        $key = 'uploads/' . $photo->getId();

        try {
            $content = Storage::disk('s3_backend')->get($key);
            if (!$content || strlen($content) === 0) {
                Log::error('File is empty or not found', ['key' => $key]);
                $photo->markFailed();
                $this->save($photo);
                return false;
            }

            $hexColor = null;
            
            if (extension_loaded('imagick') && class_exists('Imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->readImageBlob($content);
                    
                    $dominantColor = ColorThief::getColor($imagick);
                    
                    if ($dominantColor && is_array($dominantColor) && count($dominantColor) === 3) {
                        $hexColor = sprintf("#%02x%02x%02x", 
                            $dominantColor[0], $dominantColor[1], $dominantColor[2]);
                        
                    }
                } catch (\Exception $e) {
                    Log::warning('Imagick method failed, trying fallback', [
                        'photo_id' => $photo->getId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            
            if ($hexColor) {
                 $existingColors = $this->colorService->getColors($photo->getUserId()); 
                if (in_array($hexColor, $existingColors)) {
                    $color = $this->colorService->findByHex($hexColor);
                    $colorId = $color->getId();
                } else {
                    $color = $this->colorService->createColor($hexColor);
                    $colorId = $color->getId();
                }

                
                if ($photo->getStatus()->value() === 'pending_upload') {
                    $photo->markUploaded($photo->getUrl(), strlen($content));
                }
                $this->repository->syncColors($photo,  [$colorId]);
                event(new PhotoUploaded($photo->getId(), $key, $photo->getQuality(), $photo->getFormat()));
                return true;
            } 

            else {
                $photo->markFailed();
                $this->save($photo);
                return false;
            }

        } catch (\Throwable $e) {

            $photo->markFailed();
            $this->save($photo);
            throw new \Exception($e->getMessage());
        }
    }


    public function movePhotoToFolder( string $userId,string $photoId, string $folderId): void
    {
        $photo = $this->repository->findById($photoId);

        if (!$photo || !$photo->isOwnedBy($userId)) {
            throw new \Exception('Not found');
        }

        $photo->setFolderId($folderId);
        $this->repository->save($photo);
    }

    public function getRecentPhotos(string $userId, int $limit = 10): array
    {
        return $this->repository->findRecentByUserId($userId, $limit);
    }

    public function deletePhoto(string $photoId, string $userId): void
    {
        
        $photo = $this->repository->findById($photoId);

        if (!$photo || !$photo->isOwnedBy($userId)) {
            throw new \Exception('Not found');
        }

        $key = "uploads/{$photo->getId()}";
        $this->minioService->deleteFile($key);
        $this->repository->delete($photo);
    }

    public function renamePhoto(string $userId, string $photoId, string $newName): void
    {
        $photo = $this->repository->findById($photoId);

        if (!$photo || !$photo->isOwnedBy($userId)) {
            throw new \Exception('Not found');
        }

        $photo->setFileName($newName);
        $this->repository->save($photo);
    }

    public function searchPhotos(string $userId, ?string $query, array $filters): array
    {
        return $this->repository->search($userId, $query, $filters);
    }

    public function getContentTypes(string $userId): array
    {
        return $this->repository->getContentTypes($userId);
    }


public function getStorageStats(string $userId): array
{
    $photos = $this->repository->findByUserId($userId);
    
    $totalSize = 0;
    $photoCount = 0;
    $timeline = [];
    
    foreach ($photos as $photo) {
        $totalSize += $photo->getSize() ?? 0;
        $photoCount++;
        
        $createdAt = $photo->getCreatedAt();
        if ($createdAt) {
            $date = Carbon::parse($createdAt); 
            $month = $date->format('M'); 
            if (!isset($timeline[$month])) {
                $timeline[$month] = 0;
            }
            $timeline[$month]++;
        }
    }
    
    $totalStorageBytes = 50 * 1024 * 1024 * 1024;
    $percent = $totalStorageBytes > 0
        ? round(($totalSize / $totalStorageBytes) * 100, 4)
        : 0;
    
    $timelineFormatted = [];
    foreach ($timeline as $month => $value) {
        $timelineFormatted[] = [
            'month' => $month,
            'storage' => round($value, 2)
        ];
    }
    
    return [
        'photoCount' => $photoCount,
        'usedBytes' => $totalSize,
        'totalBytes' => $totalStorageBytes,
        'percent' => $percent,
        'uploadSpeed' => '0 MB/s',
        'timeline' => $timelineFormatted
    ];
}
}
