<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User as UserModel;
use RuntimeException;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param string $id
     * @return User|null
     */
    public function findById(string $id): ?User
    {
        /** @var UserModel|null $user */
        $user = UserModel::query()->find($id);

        if (!$user) {
            return null;
        }

        return new User(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password,
            createdAt: $user->created_at ?? date('Y-m-d H:i:s')
        );
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        /** @var UserModel|null $user */
        $user = UserModel::query()->where('email', $email)->first();

        if (!$user) {
            return null;
        }

        return new User(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password,
            createdAt: $user->created_at ?? date('Y-m-d H:i:s')
        );
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void
    {
        try {
            UserModel::query()->updateOrCreate(
                ['id' => $user->getId()],
                [
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => $user->getPasswordHash(),
                ]
            );
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to save user: ' . $e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return void
     */
    public function delete(User $user): void
    {
        try {
            $deleted = UserModel::query()->where('id', $user->getId())->delete();

            if ($deleted === 0) {
                throw new RuntimeException('User not found');
            }
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to delete user: ' . $e->getMessage());
        }
    }
}
