<?php
namespace App\Domain\Folder\Entities;

use Illuminate\Support\Str;

class Folder
{
    private string $id;
    private string $userId;
    private string $name;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(string $userId, string $name, ?string $id = null)
    {
        $this->id = $id ?? (string) Str::uuid();
        $this->userId = $userId;
        $this->name = $name;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
}
