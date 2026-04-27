<?php

namespace App\Infrastructure\Folder\Repositories;

use App\Domain\Folder\Repositories\FolderRepositoryInterface;
use App\Domain\Folder\Entities\Folder;
use App\Domain\Photo\Entities\Photo;
use App\Models\Folder as FolderModel;
use App\Models\Photo as PhotoModel;

class EloquentFolderRepository implements FolderRepositoryInterface
{
    public function save(Folder $folder): void
    {
        FolderModel::updateOrCreate(
            ['id' => $folder->getId()],
            [
                'user_id' => $folder->getUserId(),
                'name' => $folder->getName()
            ]
        );
    }

    public function getFoldersByUser(string $userId): array
    {
        $folders = FolderModel::with('photos')
            ->where('user_id', $userId)
            ->get();

        return $folders->map(function ($folderModel) {
            // Преобразуем фото в Domain сущности
            $photos = $folderModel->photos->map(function ($photoModel) {
                return $this->toPhotoEntity($photoModel);
            })->filter()->values()->toArray();

            // Создаём Domain сущность папки
            $folder = new Folder(
                id: $folderModel->id,
                userId: $folderModel->user_id,
                name: $folderModel->name
            );

            return [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'photos' => $photos,
            ];
        })->toArray();
    }

    private function toPhotoEntity(PhotoModel $model): ?array
    {
        if ($model->user_id === null) {
            return null;
        }

        // Создаём Domain сущность Photo
        $photo = new Photo(
            id: $model->id,
            userId: $model->user_id,
            fileName: $model->file_name,
            description: $model->description,
            url: $model->url,
            status: new \App\Domain\Photo\ValueObjects\PhotoStatus($model->status),
            size: $model->size,
            format: $model->format,
            createdAt: $model->created_at?->toDateTimeString(),
            folderId: $model->folder_id,
            folderName: $model->folder?->name,
            tags: $model->tags->pluck('name')->toArray(),
        );

        return [
            'id' => $photo->getId(),
            'title' => $photo->getFileName(),
            'description' => $photo->getDescription(),
            'url' => $photo->getUrl(),
            'size' => $photo->formatFileSize($photo->getSize()), 
            'format' => $photo->getFormat(),
            'folder' => $photo->getFolderName(),
            'createdAt' => $photo->getCreatedAt(),
            'tags' => $photo->getTags(),
        ];
    }

    public function deleteFolder(string $userId, string $folderId): void
    {
        FolderModel::where('user_id', $userId)
            ->where('id', $folderId)
            ->delete();
    }

    public function renameFolder(string $userId, string $folderId, string $newName): void
    {
        FolderModel::where('user_id', $userId)
            ->where('id', $folderId)
            ->update(['name' => $newName]);
    }

    public function findById(string $id): Folder|null
    {
        return FolderModel::find($id);
    }
}