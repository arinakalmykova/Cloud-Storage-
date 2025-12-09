<?php 
namespace App\Domain\Compression\Services;
use App\Domain\Compression\ValueObjects\CompressionQuality;

interface CompressorServiceInterface
{
   public function compressAndUpload(
        string $sourceKey,
        CompressionQuality $quality
    ): string;
}