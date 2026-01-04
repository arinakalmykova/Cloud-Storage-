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
    CompressionQuality $quality,
    string $format // добавили параметр формата
): string {
    $originalContent = Storage::disk('s3')->get($sourceKey);

    $image = new \Imagick();
    $image->readImageBlob($originalContent);
    $image->setImageFormat($format);
    $image->setImageCompressionQuality($quality->value());
    $image->stripImage();

    $blob = $image->getImageBlob();
    $this->lastCompressedBlob = $blob;

    Storage::disk('s3')->put($sourceKey, $blob, [
        'ContentType' => "image/{$format}",
        'Metadata' => ['compressed' => 'true']
    ]);

    $image->clear(); $image->destroy();

    return 'http://127.0.0.1:9000/photo/' . $sourceKey;
}



    public function getLastCompressedBlob(): ?string
    {
        return $this->lastCompressedBlob;
    }
}