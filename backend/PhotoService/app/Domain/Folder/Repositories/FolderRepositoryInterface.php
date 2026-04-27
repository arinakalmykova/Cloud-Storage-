<?php 
namespace App\Domain\Folder\Repositories;

use App\Domain\Folder\Entities\Folder;

interface FolderRepositoryInterface
{
    public function save(Folder $folder):void;
    public function getFoldersByUser(string $userId): array;
    public function deleteFolder(string $userId, string $folderId): void;
    public function renameFolder(string $userId, string $folderId, string $newName): void;
    public function findById(string $id): ?Folder;
}


