<?php
namespace App\Infrastructure\Folder\Repositories;
use App\Domain\Folder\Repositories\FolderRepositoryInterface;
use App\Domain\Folder\Entities\Folder;
use App\Domain\Photo\Entities\Photo;
use App\Models\Folder as FolderModel;


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

        return $folders->map(function ($folder) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'photos' => $folder->photos
                    ->filter(function ($photo) {
                        return $photo->user_id !== null; 
                    })
                    ->map(function ($photo) {
                        return [
                            'id' => $photo->id,
                            'title' => $photo->file_name,
                            'description' => $photo->description,
                            'url' => $photo->url,
                            'size' => $photo->size,
                            'format' => $photo->format,
                            'folder' => FolderModel::find($photo->folder_id)->name ?? null,
                            'createdAt' => $photo->created_at->toDateTimeString(),
                        ];
                        
                    })
                    ->values()
                    ->toArray(),
            ];
        })->toArray();
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
}
