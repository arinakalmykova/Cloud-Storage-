<?php
namespace App\Domain\Tag\Entities;

class Tag
{
    public function __construct(
        private string $id,
        private string $name
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
}
