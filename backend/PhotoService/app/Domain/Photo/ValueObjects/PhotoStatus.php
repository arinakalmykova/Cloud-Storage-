<?php

namespace App\Domain\Photo\ValueObjects;

final class PhotoStatus
{
    public const PENDING_UPLOAD   = 'pending_upload'; 
    public const UPLOADED         = 'uploaded';   
    public const COMPRESSING      = 'compressing';
    public const COMPRESSED       = 'compressed';
    public const FAILED           = 'failed';

    private function __construct(private string $value) {
        if (!in_array($value, [
            self::PENDING_UPLOAD,
            self::UPLOADED,
            self::COMPRESSING,
            self::COMPRESSED,
            self::FAILED,
        ])) {
            throw new \InvalidArgumentException("Invalid photo status: $value");
        }
    }

    public static function pendingUpload(): self { return new self(self::PENDING_UPLOAD); }
    public static function uploaded(): self       { return new self(self::UPLOADED); }
    public static function compressing(): self    { return new self(self::COMPRESSING); }
    public static function compressed(): self     { return new self(self::COMPRESSED); }
    public static function failed(): self         { return new self(self::FAILED); }

    public function is(string $status): bool { return $this->value === $status; }
    public function value(): string { return $this->value; }

    public function __toString(): string { return $this->value; }
}