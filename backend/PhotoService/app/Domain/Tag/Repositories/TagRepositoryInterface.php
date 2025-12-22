<?php 
namespace App\Domain\Tag\Repositories;

use App\Domain\Tag\Entities\Tag;

interface TagRepositoryInterface
{
    public function findByName(string $name): ?Tag;
    public function save(Tag $tag): void;
    public function findById(array $id): array;
}

