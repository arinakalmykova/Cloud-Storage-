<?php

namespace App\Application\Compression;

use Imagick;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class CompressionCandidateScorer
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new ImagickDriver());
    }

    public function score(string $originalBlob, string $format, int $quality, float $visualWeight, float $sizeWeight): ?array
    {
        $encodedBlob = $this->encodeCandidate($originalBlob, $format, $quality);

        if ($encodedBlob === null || $encodedBlob === '') {
            return null;
        }

        $originalSize = strlen($originalBlob);
        $compressedSize = strlen($encodedBlob);
        $visualScore = $this->calculateVisualScore($originalBlob, $encodedBlob);
        $sizeScore = $this->calculateSizeScore($originalSize, $compressedSize);
        $totalScore = $visualWeight * $visualScore + $sizeWeight * $sizeScore;

        return [
            'format' => $format,
            'quality' => $quality,
            'score' => $totalScore,
            'visual_score' => $visualScore,
            'size_score' => $sizeScore,
            'compressed_size' => $compressedSize,
        ];
    }

    private function encodeCandidate(string $originalBlob, string $format, int $quality): ?string
    {
        try {
            $image = $this->imageManager->read($originalBlob);

            $encoder = match (strtolower($format)) {
                'jpg', 'jpeg' => new JpegEncoder(quality: $quality, progressive: true, strip: true),
                'png' => new PngEncoder(),
                'webp' => new WebpEncoder(quality: $quality, strip: true),
                'avif' => new AvifEncoder(quality: $quality, strip: true),
                default => null,
            };

            if ($encoder === null) {
                return null;
            }

            return (string) $image->encode($encoder);
        } catch (\Throwable) {
            return null;
        }
    }

    private function calculateVisualScore(string $originalBlob, string $candidateBlob): float
    {
        try {
            $original = new Imagick();
            $original->readImageBlob($originalBlob);

            $candidate = new Imagick();
            $candidate->readImageBlob($candidateBlob);

            [$diffImage, $metric] = $original->compareImages($candidate, Imagick::METRIC_MEANSQUAREERROR);

            if ($diffImage instanceof Imagick) {
                $diffImage->clear();
                $diffImage->destroy();
            }

            $original->clear();
            $original->destroy();
            $candidate->clear();
            $candidate->destroy();

            return 1 / (1 + max(0.0, (float) $metric));
        } catch (\Throwable) {
            return 0.0;
        }
    }

    private function calculateSizeScore(int $originalSize, int $compressedSize): float
    {
        if ($originalSize <= 0) {
            return 0.0;
        }

        $score = 1 - ($compressedSize / $originalSize);

        return max(0.0, min(1.0, $score));
    }
}
