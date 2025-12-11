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
                'size'   => $photo->getSize()
            ]
        );
    }

   public function findById(string $photoId): Photo
    {
        $model = PhotoModel::find($photoId);

        if (!$model) {
            return null;
        }

        $photo = new Photo(
            id: $model->id,
            userId: $model->user_id,
            fileName: 'photo',
        );

        return $photo;
    }
}