<?php

namespace App\Infrastructure\Compression\Services;

use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;
use Illuminate\Support\Facades\Storage;
use Imagick;
use RuntimeException;

class ImageMagickCompressor implements CompressorServiceInterface
{
    private ?string $lastCompressedBlob = null;
    
    public function compressAndUpload(
    string $sourceKey,
    CompressionQuality $quality
): string {
    try {
        $originalContent = Storage::disk('s3')->get($sourceKey);

        if ($originalContent === null) {
            throw new RuntimeException("File not found: {$sourceKey}");
        }

        $image = new Imagick();
        $image->readImageBlob($originalContent);
        $image->setImageFormat('webp');
        $image->setOption('webp:lossless', 'false');
        $image->setOption('webp:method', '6');
        $image->setImageCompressionQuality($quality->value());
        $image->stripImage();

        $webpBlob = $image->getImageBlob();
        $this->lastCompressedBlob = $webpBlob;
        $image->clear(); $image->destroy();

        Storage::disk('s3')->put($sourceKey, $webpBlob, [
            'ContentType' => 'image/webp',
            'Metadata' => [
                'compressed' => 'true'  // ← ЭТО СПАСЁТ ТЕБЯ ОТ ЦИКЛА
            ]
        ]);

        return 'http://127.0.0.1:9000/photo/' . $sourceKey;

    } catch (\Throwable $e) {
        throw new RuntimeException(
            "Compression failed: {$e->getMessage()} (key: {$sourceKey})"
        );
    }
}


    public function getLastCompressedBlob(): ?string
    {
        return $this->lastCompressedBlob;
    }
}