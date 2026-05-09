<?php

namespace App\Application\Compression;

use Imagick;
use ImagickPixel;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Symfony\Component\Process\Process;

class CompressionCandidateScorer
{
    private const SSIM_SAMPLE_SIZE = 128;

    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new ImagickDriver());
    }

    public function score(
        string $originalBlob,
        string $format,
        int $quality,
        float $visualWeight,
        float $sizeWeight
    ): ?array {
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
            if (strtolower($format) === 'avif') {
                return $this->encodeAvifCandidate($originalBlob, $quality);
            }

            $image = $this->imageManager->read($originalBlob);

            $encoder = match (strtolower($format)) {
                'jpg', 'jpeg' => new JpegEncoder(quality: $quality, progressive: true, strip: true),
                'png' => new PngEncoder(),
                'webp' => new WebpEncoder(quality: $quality, strip: true),
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

    private function encodeAvifCandidate(string $originalBlob, int $quality): ?string
    {
        $avifencPath = $this->findAvifenc();

        if ($avifencPath === null) {
            return null;
        }

        $tempInput = tempnam(sys_get_temp_dir(), 'avif_score_in_');
        $tempOutputBase = tempnam(sys_get_temp_dir(), 'avif_score_out_');

        if ($tempInput === false || $tempOutputBase === false) {
            return null;
        }

        $tempOutput = $tempOutputBase . '.avif';
        file_put_contents($tempInput, $originalBlob);

        try {
            $process = new Process([
                $avifencPath,
                '--qcolor', (string) $quality,
                '--qalpha', (string) $quality,
                '--speed', '4',
                $tempInput,
                $tempOutput,
            ]);

            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful() || !is_file($tempOutput) || filesize($tempOutput) === 0) {
                return null;
            }

            $encodedBlob = file_get_contents($tempOutput);

            return $encodedBlob === false ? null : $encodedBlob;
        } finally {
            @unlink($tempInput);
            @unlink($tempOutputBase);
            @unlink($tempOutput);
        }
    }

    private function findAvifenc(): ?string
    {
        $possiblePaths = [
            '/usr/local/bin/avifenc',
            '/usr/bin/avifenc',
            '/opt/bin/avifenc',
            '/bin/avifenc',
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        try {
            $process = new Process(['which', 'avifenc']);
            $process->run();

            if ($process->isSuccessful()) {
                $path = trim($process->getOutput());

                if ($path !== '' && is_file($path) && is_executable($path)) {
                    return $path;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function calculateVisualScore(string $originalBlob, string $candidateBlob): float
    {
        try {
            $original = $this->prepareSsimImage($originalBlob);
            $candidate = $this->prepareSsimImage($candidateBlob);

            $originalPixels = $original->exportImagePixels(
                0,
                0,
                $original->getImageWidth(),
                $original->getImageHeight(),
                'I',
                Imagick::PIXEL_FLOAT
            );

            $candidatePixels = $candidate->exportImagePixels(
                0,
                0,
                $candidate->getImageWidth(),
                $candidate->getImageHeight(),
                'I',
                Imagick::PIXEL_FLOAT
            );

            $score = $this->calculateSsim($originalPixels, $candidatePixels);

            $original->clear();
            $original->destroy();
            $candidate->clear();
            $candidate->destroy();

            return $score;
        } catch (\Throwable) {
            return 0.0;
        }
    }

    private function prepareSsimImage(string $blob): Imagick
    {
        $image = new Imagick();
        $image->readImageBlob($blob);
        $image->setImageBackgroundColor(new ImagickPixel('white'));
        $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $image->setImageColorspace(Imagick::COLORSPACE_GRAY);
        $image->resizeImage(
            self::SSIM_SAMPLE_SIZE,
            self::SSIM_SAMPLE_SIZE,
            Imagick::FILTER_TRIANGLE,
            1,
            true
        );
        $image->extentImage(
            self::SSIM_SAMPLE_SIZE,
            self::SSIM_SAMPLE_SIZE,
            0,
            0
        );

        return $image;
    }

    private function calculateSsim(array $originalPixels, array $candidatePixels): float
    {
        $count = min(count($originalPixels), count($candidatePixels));

        if ($count === 0) {
            return 0.0;
        }

        $meanOriginal = array_sum(array_slice($originalPixels, 0, $count)) / $count;
        $meanCandidate = array_sum(array_slice($candidatePixels, 0, $count)) / $count;

        $varianceOriginal = 0.0;
        $varianceCandidate = 0.0;
        $covariance = 0.0;

        for ($index = 0; $index < $count; $index++) {
            $originalDelta = $originalPixels[$index] - $meanOriginal;
            $candidateDelta = $candidatePixels[$index] - $meanCandidate;

            $varianceOriginal += $originalDelta ** 2;
            $varianceCandidate += $candidateDelta ** 2;
            $covariance += $originalDelta * $candidateDelta;
        }

        $denominator = max(1, $count - 1);
        $varianceOriginal /= $denominator;
        $varianceCandidate /= $denominator;
        $covariance /= $denominator;

        $c1 = 0.01 ** 2;
        $c2 = 0.03 ** 2;

        $numerator = (2 * $meanOriginal * $meanCandidate + $c1)
            * (2 * $covariance + $c2);
        $denominatorValue = ($meanOriginal ** 2 + $meanCandidate ** 2 + $c1)
            * ($varianceOriginal + $varianceCandidate + $c2);

        if ($denominatorValue <= 0.0) {
            return 0.0;
        }

        return max(0.0, min(1.0, $numerator / $denominatorValue));
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
