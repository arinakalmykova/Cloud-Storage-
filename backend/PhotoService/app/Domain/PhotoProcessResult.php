<?php

namespace App\Domain;

class PhotoProcessResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly ?string $dominantColor = null,
        public readonly ?\Throwable $exception = null
    ) {}

    public static function success(string $color = null): self
    {
        return new self(true, 'Фото успешно обработано', $color);
    }

    public static function failed(string $reason, ?\Throwable $e = null): self
    {
        return new self(false, $reason, null, $e);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}