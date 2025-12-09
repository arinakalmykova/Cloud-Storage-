<?php 

namespace App\Domain\Auth\Entities;

class User
{
    private string $id;
    private string $name;
    private string $email;
    private string $passwordHash;

    public function __construct(string $id, string $name, string $email, string $passwordHash)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public function getId(): string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getName(): string { return $this->name; }

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
}
