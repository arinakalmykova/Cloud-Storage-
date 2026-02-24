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
    private ?string $folderId = null;
    private ?string $createdAt = null;

    public function __construct(
    string $id, 
    string $userId,  
    string $fileName,
    ?string $description, 
    ?string $url, 
    PhotoStatus $status,
    ?int $size = null,
    ?string $format = null,
    ?string $dominantColor = null,
    ?string $folderId = null,
    ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->fileName = $fileName;
        $this->description = $description;
        $this->url = $url;
        $this->status = $status;
        $this->size = $size;
        $this->dominantColor = $dominantColor;
        $this->folderId = $folderId;
        $this->format = $format;
        $this->createdAt = $createdAt;
    }

    public function markPendingUpload(string $key, string $presignedUrl): void
    {
        $this->key = $key;                    
        $this->presignedUrl = $presignedUrl;
        $this->status = PhotoStatus::pendingUpload();
    }

    public function markUploaded(string $url, int $size, ?int $quality = null, ?string $format = null, ?string $folder_id = null): void  
    {
        $this->url = $url;
        $this->size = $size;
        $this->quality = $quality;
        $this->format = $format;
        $this->folderId = $folder_id;
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

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function setFolderId(?string $folderId): void
    {
        $this->folderId = $folderId;
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
    public function getFolderId(): ?string { return $this->folderId; }

    public function isOwnedBy(string $userId): bool
    {
        return $this->userId === $userId;
    }

     public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->fileName,
            'description' => $this->description,
            'url' => $this->url,
            'size' => $this->size,
            'format' => $this->format,
            'folder' => $this->folderId,
            'createdAt' => $this->createdAt,
        ];
    }
}