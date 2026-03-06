<?php
namespace App\Domain\Color\Entities;

class Color
{
    public function __construct(
        private string $id,
        private string $color
    ) {}

    public function getId(): string { return $this->id; }
    public function getColor(): string { return $this->color; }
}
