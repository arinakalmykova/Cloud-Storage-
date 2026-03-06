<?php 
namespace App\Domain\Photo\Repositories;

use App\Domain\Photo\Entities\Photo;

interface PhotoRepositoryInterface
{
    public function save(Photo $photo):void;
    public function findById(string $photoId): ?Photo;
    public function syncTags(Photo $photo, array $tagIds): void;
    public function syncColors(Photo $photo, array $colorIds): void;
    public function delete(Photo $photo): void;
    public function findRecentByUserId(string $userId, int $limit = 10): array;
    public function search( string $userId, ?string $query, array $filters): array;
}


