<?php

namespace App\Infrastructure\Photo\Repositories;

use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Models\Photo as PhotoModel;

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
        tags: $model->tags ?? [],
        url: $model->url,
        size: $model->size,
        status: new PhotoStatus($model->status)
    );
    }

    public function syncTags(Photo $photo, array $tagIds): void
    {
        PhotoModel::find($photo->getId())
            ->tags()
            ->sync($tagIds);
    }
}