<?php 

namespace App\Application\Compression;

use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;
use App\Events\PhotoCompressed;
use App\Infrastructure\MLService\MLServiceClient;

class CompressionService {
    public function __construct(
        private CompressorServiceInterface $compressor
    ){}
 
    public function compress(array $task):array
    {
        $photoId = $task['photo_id'];
        $sourceKey = str_replace('http://127.0.0.1:9000/photo/', '', $task['source_url']);

         $tmp = tempnam(sys_get_temp_dir(), 'photo_');
        \file_put_contents($tmp, \file_get_contents("http://127.0.0.1:9000/photo/{$sourceKey}"));
        
        $mlClient = new MLServiceClient();
        $mlResult = $mlClient->classify($tmp);

        // Определяем формат и качество по типу
        switch ($mlResult['content_type']) {
            case 'photo':
                $mlFormat = 'webp';
                $mlQuality = 85;
                break;
            case 'text_graphics':
                $mlFormat = 'png';
                $mlQuality = 100;
                break;
            case 'illustration':
                $mlFormat = 'avif';
                $mlQuality = 80;
                break;
            case 'ui_screenshot':
                $mlFormat = 'png';
                $mlQuality = 95;
                break;
            case 'mixed':
            default:
                $mlFormat = 'webp';
                $mlQuality = 80;
        }

        $format = $task['format'] ?? $mlFormat;
        $quality = $task['quality'] ?? $mlQuality;

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