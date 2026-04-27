<?php

namespace App\Application\Folder;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Folder\Entities\Folder;
use App\Domain\Folder\Repositories\FolderRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class FolderService
{
    public function __construct(
        private FolderRepositoryInterface $folderRepository
    ) {}


    public function getById(string $id): ?Folder
    {
        return $this->folderRepository->findById($id);
    }
    
    public function save( Folder $folder): void
    {
        $this->folderRepository->save($folder);
    }


    public function getFolders(string $userId): array
    {
        return $this->folderRepository->getFoldersByUser($userId);
    }

    public function createFolder(string $userId, string $name): Folder
    {
        $folder = new Folder(
            userId: $userId,
            name: $name
        );

        $this->folderRepository->save($folder);

        return $folder;
    }


    public function deleteFolder(string $userId, string $folderId): void
    {
        $this->folderRepository->deleteFolder($userId, $folderId);
    }

    public function renameFolder(string $userId, string $folderId, string $newName): void
    {
        $this->folderRepository->renameFolder($userId, $folderId, $newName);
    }

}