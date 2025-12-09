<?php

namespace App\Domain\Photo\Entities;

use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Domain\Photo\ValueObjects\FileName;

class Photo
{
    private string $id;
    private string $userId;
    private FileName $fileName;
    private ?int $size = null;
    private ?string $key = null;      
    private ?string $url = null;
    private ?string $description = null;
    private ?string $targetFormat = null; 
    private ?string $presignedUrl = null; 

    private PhotoStatus $status;

    public function __construct(string $id, string $userId, string $fileName)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->fileName = new FileName($fileName);
        $this->status = PhotoStatus::pendingUpload();
    }

    public function markPendingUpload(string $key, string $presignedUrl): void
    {
        $this->key = $key;                    // например: uploads/uuid/original
        $this->presignedUrl = $presignedUrl;
        $this->status = PhotoStatus::pendingUpload();
    }

    public function markUploaded(string $url, ?int $size = null): void  
    {
        $this->url = $url;
        if ($size !== null) {
            $this->size = $size;
        }
        $this->status = PhotoStatus::uploaded();
    }

    public function markCompressing(): void
    {
        $this->status = PhotoStatus::compressing();
    }

    public function markCompressed(string $newUrl, ?int $newSize = null): void
    {
        $this->url = $newUrl;
        if ($newSize !== null) {
            $this->size = $newSize;
        }
        $this->status = PhotoStatus::compressed();
    }

    public function markFailed(): void
    {
        $this->status = PhotoStatus::failed();
    }

    // ─────── ГЕТТЕРЫ — ВСЁ ЧЕРЕЗ ОДНУ ССЫЛКУ ───────
    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getFileName(): string { return $this->fileName->value(); }
    public function getStatus(): PhotoStatus { return $this->status; }
    public function getOriginalSize(): ?int { return $this->size; }
    public function getKey(): ?string { return $this->key; }               
    public function getPresignedUrl(): ?string { return $this->presignedUrl; }

    // ← ВСЕГДА ОДНА ССЫЛКА — и для оригинала, и для сжатого
    public function getUrl(): ?string { return $this->url; }

    // ← Удобный алиас, чтобы везде использовать один метод
    public function getPublicUrl(): ?string { return $this->url; }

    public function isOwnedBy(string $userId): bool
    {
        return $this->userId === $userId;
    }
}