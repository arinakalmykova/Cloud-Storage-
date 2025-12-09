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
            ['id' => $photo->getId()], // ← ГЕТТЕР!
            [
                'user_id'         => $photo->getUserId(),
                'file_name'       => $photo->getFileName(),
                'status'          => $photo->getStatus()->value(),
                'url'    => $photo->getUrl(),     
                'size_original'   => $photo->getOriginalSize()
            ]
        );
    }

   public function findById(string $photoId): Photo
{
    $model = PhotoModel::find($photoId);

    if (!$model) {
        return null;
    }

    // Создаем доменную сущность
    $photo = new Photo(
        id: $model->id,
        userId: $model->user_id,
        fileName: 'photo',
    );

    // Заполняем данные из БД (ОБЯЗАТЕЛЬНО!)
    if ($model->url) {
        $photo->markUploaded($model->url, $model->size_original);
    }

    // Восстановление финального статуса
    switch ($model->status) {
        case 'uploaded':
            $photo->markUploaded($model->url, $model->size_original);
            break;

        case 'compressing':
            $photo->markCompressing();
            break;

        case 'compressed':
            $photo->markCompressed($model->url, $model->size_original);
            break;

        case 'failed':
            $photo->markFailed();
            break;
    }

    return $photo;
}


    public function updateCompressedUrl(string $photoId, string $compressedUrl): void
    {
        PhotoModel::where('id', $photoId)->update(['compressed_url' => $compressedUrl]);
    }
}