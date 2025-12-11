<?php

namespace App\Infrastructure\Photo\Repositories;

use App\Domain\Photo\Entities\User;
use App\Domain\Photo\Repositories\UserRepositoryInterface;
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
