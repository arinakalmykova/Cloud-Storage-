<?php 

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(string $id): ?User;
    public function save(User $user): void;
}
