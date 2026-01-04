<?php

namespace App\Domain\Photo\Entities;

use App\Domain\Photo\ValueObjects\PhotoStatus;

class Photo
{
    private string $id;
    private string $userId;
    private ?int $size = null;
    private ?string $key = null;      
    private ?string $url = null; 
    private string $fileName;
    private ?string $description = null;
    private ?string $format = null;
    private array $tags = [];
    private ?string $dominantColor = null;
    private ?string $presignedUrl = null; 
    private PhotoStatus $status;
    private ?int $quality = null;

    public function __construct(
    string $id, 
    string $userId,  
    string $fileName,
    ?string $description, 
    ?string $url, 
    PhotoStatus $status,
    ?int $size = null,
    ?string $dominantColor = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->fileName = $fileName;
        $this->description = $description;
        $this->url = $url;
        $this->status = $status;
        $this->size = $size;
        $this->dominantColor = $dominantColor;
    }

    public function markPendingUpload(string $key, string $presignedUrl): void
    {
        $this->key = $key;                    
        $this->presignedUrl = $presignedUrl;
        $this->status = PhotoStatus::pendingUpload();
    }

    public function markUploaded(string $url, int $size, ?int $quality = null, ?string $format = null): void  
    {
        $this->url = $url;
        $this->size = $size;
        $this->quality = $quality;
        $this->format = $format;
        $this->status = PhotoStatus::uploaded();
    }

    public function markCompressed(string $url,int $newSize): void
    {
        $this->url = $url;
        $this->size = $newSize;
        $this->status = PhotoStatus::compressed();
    }

    public function markFailed(): void
    {
        $this->status = PhotoStatus::failed();
    }

    public function setDominantColor(string $color): void
    {
        $this->dominantColor = $color;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getFileName(): string { return $this->fileName; }
    public function getDescription(): ?string { return $this->description; }
    public function getTags(): array { return $this->tags; }
    public function getStatus(): PhotoStatus { return $this->status; }
    public function getSize(): ?int { return $this->size; }
    public function getKey(): ?string { return $this->key; }               
    public function getPresignedUrl(): ?string { return $this->presignedUrl; }
    public function getUrl(): ?string { return $this->url; }
    public function getDominantColor(): ?string { return $this->dominantColor; }
    public function getQuality(): ?int { return $this->quality; }
    public function getFormat(): ?string { return $this->format; }

    public function isOwnedBy(string $userId): bool
    {
        return $this->userId === $userId;
    }
}