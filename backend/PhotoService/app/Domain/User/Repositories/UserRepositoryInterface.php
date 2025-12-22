<?php 

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function save(User $user): void;
}
