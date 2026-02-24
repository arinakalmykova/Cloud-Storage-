<?php

namespace App\Infrastructure\Photo\Repositories;

use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Models\Photo as PhotoModel;
use App\Models\Folder as FolderModel;

class EloquentPhotoRepository implements PhotoRepositoryInterface
{
    public function save(Photo $photo): void
    {
        PhotoModel::updateOrCreate(
            ['id' => $photo->getId()],
            [
                'user_id' => $photo->getUserId(),
                'status' => $photo->getStatus()->value(),
                'url'    => $photo->getUrl(),     
                'size'   => $photo->getSize(),
                'file_name' => $photo->getFileName(),
                'description' => $photo->getDescription(),
                'dominant_color' => $photo->getDominantColor(),
                'format' => $photo->getFormat(),
                'folder_id' => $photo->getFolderId()
            ]
        );
    }

   public function findById(string $photoId): ?Photo
    {
        $model = PhotoModel::find($photoId);
        if (!$model) {
            return null;
        }

        return new Photo(
        id: $model->id,
        userId: $model->user_id,
        fileName: $model->file_name,
        description: $model->description,
        url: $model->url,
        size: $model->size,
        format: $model->format,
        status: new PhotoStatus($model->status),
        dominantColor: $model->dominant_color,
        folderId: $model->folder_id
    );
    }

    public function syncTags(Photo $photo, array $tagIds): void
    {
        PhotoModel::find($photo->getId())
            ->tags()
            ->sync($tagIds);
    }

    public function delete(Photo $photo): void
    {
        PhotoModel::destroy($photo->getId());
    }

    public function findRecentByUserId(string $userId, int $limit = 10): array
{
    return PhotoModel::where('user_id', $userId)
        ->latest()
        ->limit($limit)
        ->get()
        ->map(function (PhotoModel $model) {
            $photo = new Photo(
                id: $model->id,
                userId: $model->user_id,
                fileName: $model->file_name,
                description: $model->description,
                url: $model->url,
                size: $model->size,
                format: $model->format,
                status: new PhotoStatus($model->status),
                dominantColor: $model->dominant_color,
                createdAt: $model->created_at,
                folderId: FolderModel::find($model->folder_id)->name ?? null
            );
            
            return $photo->toArray(); 
        })
        ->values() 
        ->toArray(); 
}

}