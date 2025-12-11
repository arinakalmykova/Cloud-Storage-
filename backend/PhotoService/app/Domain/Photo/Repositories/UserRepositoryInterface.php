<?php 

namespace App\Domain\Photo\Repositories;

use App\Domain\Photo\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function save(User $user): void;
}
