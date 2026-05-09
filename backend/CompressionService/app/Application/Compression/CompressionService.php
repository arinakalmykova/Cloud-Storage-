<?php 

namespace App\Application\Compression;

use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;

class CompressionService {
    public function __construct(
        private CompressorServiceInterface $compressor
    ){}
 
    public function compress(array $task):array
    {
        $photoId = $task['photo_id'];
        $sourceKey = str_replace('http://127.0.0.1:9000/photo/', '', $task['source_url']);

        // Определяем формат и качество по типу
        $format = $task['format'];
        $quality = $task['quality'];

        $compressedUrl = $this->compressor->compressAndUpload(
            sourceKey: $sourceKey,
            quality: new CompressionQuality($quality),
            format: $format
        );

        $webpBlob = $this->compressor->getLastCompressedBlob();
        $compressedSize = strlen($webpBlob);

        return [
            'photo_id' => $photoId,
            'size' => $compressedSize
        ];
    }
}
