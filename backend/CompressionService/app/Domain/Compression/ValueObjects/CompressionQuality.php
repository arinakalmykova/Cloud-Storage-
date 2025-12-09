<?php

namespace App\Domain\Compression\ValueObjects;

class CompressionQuality
{
    private const LOW    = 60;
    private const MEDIUM = 80;
    private const HIGH   = 92;
    private const BEST   = 98;

    public int $value;

    private function __construct(int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException('Compression quality must be between 0 and 100');
        }
        $this->value = $value;
    }

    public static function low(): self
    {
        return new self(self::LOW);
    }

    public static function medium(): self
    {
        return new self(self::MEDIUM);
    }

    public static function high(): self
    {
        return new self(self::HIGH);
    }

    public static function best(): self
    {
        return new self(self::BEST);
    }

    public static function custom(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}