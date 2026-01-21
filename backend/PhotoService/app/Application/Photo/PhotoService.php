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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ColorThief\ColorThief;
use App\Domain\PhotoProcessResult;
use Imagick;

class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $repository,
        private PhotoManagementServiceInterface $minioService,
        private TagService $tagService
    ) {}

    public function createUploadIntent(CreatePhotoDTO $dto): Photo 
    {
        $photo = new Photo(
            id: Str::uuid()->toString(),
            userId: $dto->userId,
            fileName: $dto->fileName,
            description: $dto->description,
            url: null,
            size: null,
            status: PhotoStatus::pendingUpload()
        );

        $presignedUrl = $this->minioService->getUploadUrl($photo);
        $originalKey = "uploads/{$photo->getId()}/original";
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
    $key = 'uploads/' . $photo->getId() . '/original';

    try {
        // 1. Получаем файл из хранилища
        $content = Storage::disk('s3_backend')->get($key);
        if (!$content || strlen($content) === 0) {
            Log::error('File is empty or not found', ['key' => $key]);
            $photo->markFailed();
            $this->save($photo);
            return false;
        }

        $hexColor = null;
        
        // 2. Пробуем через Imagick (если доступен) - более эффективно
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
        
        
        // 4. Сохраняем результат
        if ($hexColor) {
            $photo->setDominantColor($hexColor);
            
            // Обновляем статус на uploaded (если еще не установлен)
            if ($photo->getStatus()->value() === 'pending_upload') {
                $photo->markUploaded($photo->getUrl(), strlen($content));
            }
            
            $this->save($photo);
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
        
        return false;
    }
}

}