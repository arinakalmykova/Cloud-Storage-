<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

class User
{
    private string $id;
    private string $name;
    private string $email;
    private string $passwordHash;
    private string $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $passwordHash,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function changePassword(string $newPassword): void
    {
        $this->passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
