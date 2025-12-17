<?php 
namespace App\Domain\Photo\Repositories;

use App\Domain\Photo\Entities\Photo;

interface PhotoRepositoryInterface
{
    public function save(Photo $photo):void;
    public function findById(string $photoId): ?Photo;
}


