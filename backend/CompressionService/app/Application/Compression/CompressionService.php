<?php 

namespace App\Application\Compression;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Compression\Entities\PhotoCompressionTask;
use App\Domain\Compression\Events\PhotoCompressionFailed;
use App\Domain\Compression\Events\Publisher\CompressionEventPublisherInterface;
use App\Domain\Compression\Repositories\CompressionTaskRepositoryInterface;
use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Photo\Services\PhotoManagementServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Events\PhotoCompressed;

class CompressionService {
    public function __construct(private CompressionEventPublisherInterface $publisher,
        private CompressionTaskRepositoryInterface $repositoryCompress,
        private CompressorServiceInterface $compressor,
        private PhotoManagementServiceInterface $minioSevice,
        private PhotoRepositoryInterface $photoRepository
    ){}

public function compressWhenUploaded(Photo $photo, CompressionQuality $quality): void
{
    if ($photo->getStatus()->value() !== 'uploaded') {
        return;
    }

    $photo->markCompressing();
    $this->photoRepository->save($photo);
    $compressedKey = $photo->getKey();
    $originalKey = "uploads/{$photo->getId()}/original";

    try {
        $compressedUrl = $this->compressor->compressAndUpload(
            sourceKey: $originalKey,
            quality: $quality
        );

        $webpBlob = $this->compressor->getLastCompressedBlob();
        $compressedSize = strlen($webpBlob);

        $photo->markCompressed(
            newUrl: $compressedUrl,
            newSize: $compressedSize
        );

        $this->photoRepository->save($photo);

    } catch (\Throwable $e) {
        $photo->markFailed();
        $this->photoRepository->save($photo);

        \Log::error('=== COMPRESSION TOTALLY FAILED ===', [
            'photoId'       => $photo->getId(),
            'original_url'  => $photo->getUrl(),
            'compressed_key'=> $compressedKey,
            'exception'     => get_class($e),
            'message'       => $e->getMessage(),
            'file'          => $e->getFile(),
            'line'          => $e->getLine(),
            'trace'         => $e->getTraceAsString(),
        ]);
        echo "COMPRESSION ERROR: " . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
}


}