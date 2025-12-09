<?php

namespace App\Application\Photo;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Services\PhotoManagementServiceInterface;
use Illuminate\Support\Str;
use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Application\DTOs\CreatePhotoDTO;

class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $repository,
        private PhotoManagementServiceInterface $minioService
    ) {}

    /**
     * Создаёт запись о фото и возвращает presigned URL для прямой загрузки в MinIO
     */
    public function createUploadIntent(CreatePhotoDTO $dto): Photo {
        // 1. Генерируем UUID для фото
        $photoId = Str::uuid()->toString();

        // 2. Создаём доменную сущность (пока без presigned URL)
        $photo = new Photo(
            id: $photoId,
            userId: $dto->userId,
            fileName: $dto->fileName,
        );

        // 3. Используем твой уже готовый и рабочий метод из MinioPhotoManagement
        $presignedUrl = $this->minioService->getUploadUrl($photo);

        // 4. Сохраняем ключ и временный presigned URL в сущность
        $originalKey = "uploads/{$photo->getId()}/original";

       $photo->markPendingUpload($originalKey, $presignedUrl);

        // 5. Сохраняем в БД
        $this->repository->save($photo);

        // 6. Возвращаем готовый объект
        return $photo;
    }

    // App\Application\Photo\PhotoService.php

    public function getById(string $id): ?Photo
    {
        return $this->repository->findById($id);
    }
}