<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $user = UserModel::find($id); 
        if (!$user) return null;

        return new User(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password
        );
    }

    public function findByEmail(string $email): ?User
    {
        $user = UserModel::where('email', $email)->first();
        if (!$user) return null;

        return new User(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password
        );
    }

    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->getId()],
            [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => $user->getPasswordHash()
            ]
        );
    }
}
