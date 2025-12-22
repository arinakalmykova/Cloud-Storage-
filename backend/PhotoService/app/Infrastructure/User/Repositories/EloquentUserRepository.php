<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $user = UserModel::find($id); 
        if (!$user) return null;

        return new User(
            id: $user->id
        );
    }

    public function save(User $user): void
    {
        UserModel::firstOrCreate([
            'id' => $user->getId()
        ]);
    }
}
