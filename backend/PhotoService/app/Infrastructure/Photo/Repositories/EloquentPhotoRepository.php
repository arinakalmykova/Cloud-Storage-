<?php

namespace App\Infrastructure\Photo\Repositories;

use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Models\Photo as PhotoModel;
use App\Models\Folder as FolderModel;
use App\Models\Tag as TagModel;

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
                'quality' => $photo->getQuality(),
                'description' => $photo->getDescription(),
                'format' => $photo->getFormat(),
                'folder_id' => $photo->getFolderId() 
            ]
        );
    }

    public function findById(string $photoId): ?Photo
    {
        $model = PhotoModel::with('tags', 'folder')->find($photoId);
        if (!$model) {
            return null;
        }
        
        $tags = $model->tags->map(fn($tag) => $tag->name)->toArray();
        
        $folderId = $model->folder_id;
        
        $folderName = null;
        if ($model->folder) {
            $folderName = $model->folder->name;
        }
        
        return new Photo(
            id: $model->id,
            userId: $model->user_id,
            fileName: $model->file_name,
            description: $model->description,
            url: $model->url,
            status: new PhotoStatus($model->status),
            size: $model->size,
            format: $model->format,
            createdAt: $model->created_at,
            folderId: $folderId,     
            folderName: $folderName, 
            tags: $tags,
        );
    }

    public function syncTags(Photo $photo, array $tagIds): void
    {
        PhotoModel::find($photo->getId())
            ->tags()
            ->sync($tagIds);
    }

    public function syncColors(Photo $photo, array $colorIds): void
    {
        PhotoModel::find($photo->getId())
            ->colors()
            ->sync($colorIds);
    }

    public function delete(Photo $photo): void
    {
        PhotoModel::destroy($photo->getId());
    }

    public function findRecentByUserId(string $userId, int $limit = 10): array
    {
        return PhotoModel::where('user_id', $userId)
            ->latest()
            ->with('tags', 'folder')
            ->limit($limit)
            ->get()
            ->map(function (PhotoModel $model) {
                $tags = $model->tags->map(fn($tag) => $tag->name)->toArray();
                
                // Получаем UUID и имя папки
                $folderId = $model->folder_id;
                $folderName = $model->folder?->name;
                
                $photo = new Photo(
                    id: $model->id,
                    userId: $model->user_id,
                    fileName: $model->file_name,
                    description: $model->description,
                    url: $model->url,
                    status: new PhotoStatus($model->status),
                    size: $model->size,
                    format: $model->format,
                    createdAt: $model->created_at,
                    folderId: $folderId,    
                    folderName: $folderName,  
                    tags: $tags,
                );
                
                return $photo->toArray(); 
            })
            ->values() 
            ->toArray(); 
    }

    public function search(string $userId, ?string $query, array $filters): array
    {
        $qb = PhotoModel::query();
        $qb->where('user_id', $userId);
        
        if (!empty($query)) {
            $qb->where(function ($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if (!empty($filters['file_name'])) {
            $qb->where('file_name', 'like', "%{$filters['file_name']}%");
        }

        if (!empty($filters['tags'])) {
            if (is_string($filters['tags'])) {
                $tags = explode(',', $filters['tags']);
            } else {
                $tags = $filters['tags'];
            }
            
            $qb->whereHas('tags', function ($q) use ($tags) { 
                $q->whereIn('name', $tags);
            });
        }

        if (!empty($filters['dominant_color'])) {
            $colors = is_string($filters['dominant_color']) 
                ? explode(',', $filters['dominant_color']) 
                : $filters['dominant_color'];
                
            $qb->whereHas('colors', function ($q) use ($colors) {
                $q->whereIn('color', $colors);
            });
        }

        if (!empty($filters['dateFrom'])) {
            $qb->whereDate('created_at', '>=', $filters['dateFrom']);
        }
        
        if (!empty($filters['dateTo'])) {
            $qb->whereDate('created_at', '<=', $filters['dateTo']);
        }

        if (!empty($filters['format'])) {
            $qb->where('format', $filters['format']);
        }

        $models = $qb->with('tags', 'colors', 'folder')->get();

        return $models->map(function (PhotoModel $model) {
            $folderId = $model->folder_id;
            $folderName = $model->folder?->name;
            
            $photo = new Photo(
                id: $model->id,
                userId: $model->user_id,
                fileName: $model->file_name,
                description: $model->description,
                url: $model->url,
                status: new PhotoStatus($model->status),
                size: $model->size,
                format: $model->format,
                createdAt: $model->created_at,
                folderId: $folderId,      
                folderName: $folderName,  
                tags: $model->tags->map(fn($tag) => $tag->name)->toArray(),
            );
            return $photo->toArray();
        })->values()->toArray();
    }

    public function findByUserId(string $userId): array
    {
        $models = PhotoModel::where('user_id', $userId)
            ->with('tags', 'folder')
            ->get();

        return $models->map(function (PhotoModel $model) {
            $tags = $model->tags->map(fn($tag) => $tag->name)->toArray();
            
            $folderId = $model->folder_id;
            $folderName = $model->folder?->name;
            
            $photo = new Photo(
                id: $model->id,
                userId: $model->user_id,
                fileName: $model->file_name,
                description: $model->description,
                url: $model->url,
                status: new PhotoStatus($model->status),
                size: $model->size,
                format: $model->format,
                createdAt: $model->created_at,
                folderId: $folderId,
                folderName: $folderName,
                tags: $tags,
            );
            
            return $photo;
        })->toArray();
    }
}