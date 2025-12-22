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

class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $repository,
        private PhotoManagementServiceInterface $minioService,
        private TagService $tagService
    ) {}

    public function createUploadIntent(CreatePhotoDTO $dto): Photo {
        $photo = new Photo(
            id: Str::uuid()->toString(),
            userId: $dto->userId,
            fileName: $dto->fileName,
            description: $dto->description,
            tags: $dto->tags,
            url: null,
            size: null,
            status: PhotoStatus::pendingUpload()
        );

        $presignedUrl = $this->minioService->getUploadUrl($photo);
        $originalKey = "uploads/{$photo->getId()}/original";
        $photo->markPendingUpload($originalKey, $presignedUrl);
        $this->repository->save($photo);

        event(new PhotoUploaded($photo->getId(), $photo->getKey()));

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
}