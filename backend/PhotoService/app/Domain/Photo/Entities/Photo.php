<?php

namespace App\Domain\Photo\Entities;

use App\Domain\Photo\ValueObjects\PhotoStatus;
use App\Domain\Photo\ValueObjects\FileName;

class Photo
{
    private string $id;
    private string $userId;
    private ?int $size = null;
    private ?string $key = null;      
    private ?string $url = null; 
    private FileName $fileName;
    private ?string $description = null;
    private ?string $format = null;
    private array $tags = [];
    private ?string $dominantColor = null;
    private ?string $presignedUrl = null; 
    private PhotoStatus $status;

    public function __construct(string $id, string $userId,  string $fileName,?string $description,array $tags, ?int $size = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->fileName = new FileName($fileName);
        $this->status = PhotoStatus::pendingUpload();
        $this->size = $size;
        $this->description = $description;
        $this->tags = $tags;
        
    }

    public function markPendingUpload(string $key, string $presignedUrl): void
    {
        $this->key = $key;                    
        $this->presignedUrl = $presignedUrl;
        $this->status = PhotoStatus::pendingUpload();
    }

    public function markUploaded(string $url, int $size): void  
    {
        $this->url = $url;
        $this->size = $size;
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

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getFileName(): string { return $this->fileName->value(); }
    public function getStatus(): PhotoStatus { return $this->status; }
    public function getSize(): ?int { return $this->size; }
    public function getKey(): ?string { return $this->key; }               
    public function getPresignedUrl(): ?string { return $this->presignedUrl; }
    public function getUrl(): ?string { return $this->url; }
    

    public function isOwnedBy(string $userId): bool
    {
        return $this->userId === $userId;
    }
}