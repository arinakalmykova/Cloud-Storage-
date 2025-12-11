<?php 

namespace App\Application\Compression;

use App\Domain\Compression\Repositories\CompressionTaskRepositoryInterface;
use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;
use App\Events\PhotoCompressed;

class CompressionService {
    public function __construct(
        private CompressionTaskRepositoryInterface $repositoryCompress,
        private CompressorServiceInterface $compressor
    ){}
 
    public function compress(array $task):array
    {
        $photoId = $task['photo_id'];
        $sourceKey = str_replace('http://127.0.0.1:9000/photo/', '', $task['source_url']);

        $compressedUrl = $this->compressor->compressAndUpload(
            sourceKey: $sourceKey,
            quality: new CompressionQuality(80)
        );

        $webpBlob = $this->compressor->getLastCompressedBlob();
        $compressedSize = strlen($webpBlob);

        return [
            'photo_id' => $photoId,
            'size' => $compressedSize
        ];
    }
}