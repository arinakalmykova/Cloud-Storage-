<?php

namespace App\Application\Compression;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class CompressionRecommendationService
{
    private const VISUAL_WEIGHT = 0.92;
    private const SIZE_WEIGHT = 0.08;
    private const MAX_PARALLEL_PROCESSES = 4;
    private const POLL_INTERVAL_MICROSECONDS = 10000;
    private const PROCESS_TIMEOUT_SECONDS = 30;
    private const REFINEMENT_STEP = 2;
    private const REFINEMENT_RADIUS = 4;

    public function recommend(string $sourceKey, string $contentType): array
    {
        $originalBlob = Storage::disk('s3')->get($sourceKey);

        if ($originalBlob === false || $originalBlob === '') {
            throw new RuntimeException('Failed to read source image.');
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'cmp_rec_');
        if ($tempFilePath === false) {
            throw new RuntimeException('Failed to allocate temporary file for recommendation.');
        }

        file_put_contents($tempFilePath, $originalBlob);

        try {
            $bestCandidate = $this->findBestCandidate($tempFilePath, $contentType);
        } finally {
            @unlink($tempFilePath);
        }

        if ($bestCandidate === null) {
            return [
                'format' => 'webp',
                'quality' => 80,
                'estimated_size' => strlen($originalBlob),
                'saved_bytes' => 0,
                'saved_percent' => 0,
            ];
        }

        $originalSize = strlen($originalBlob);
        $savedBytes = max(0, $originalSize - (int) $bestCandidate['compressed_size']);
        $savedPercent = $originalSize > 0
            ? (int) round(($savedBytes / $originalSize) * 100)
            : 0;

        return [
            'format' => $bestCandidate['format'],
            'quality' => $bestCandidate['quality'],
            'estimated_size' => (int) $bestCandidate['compressed_size'],
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
        ];
    }

    public function estimateSpecific(
        string $sourceKey,
        string $contentType,
        string $format,
        int $quality
    ): array {
        unset($contentType);
        $normalizedQuality = max(0, min(100, $quality));
        $originalBlob = Storage::disk('s3')->get($sourceKey);

        if ($originalBlob === false || $originalBlob === '') {
            throw new RuntimeException('Failed to read source image.');
        }

        $scorer = new CompressionCandidateScorer();
        $result = $scorer->score($originalBlob, $format, $normalizedQuality, self::VISUAL_WEIGHT, self::SIZE_WEIGHT);

        if ($result === null) {
            throw new RuntimeException('Failed to estimate compression result.');
        }

        $originalSize = strlen($originalBlob);
        $savedBytes = max(0, $originalSize - (int) $result['compressed_size']);
        $savedPercent = $originalSize > 0
            ? (int) round(($savedBytes / $originalSize) * 100)
            : 0;

        return [
            'format' => $format,
            'quality' => $normalizedQuality,
            'estimated_size' => (int) $result['compressed_size'],
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
        ];
    }

    private function findBestCandidate(string $tempFilePath, string $contentType): ?array
    {
        $initialBestCandidate = $this->runCandidateBatch($tempFilePath, $this->buildCandidates($contentType));

        if ($initialBestCandidate === null) {
            return null;
        }

        $refinedCandidates = $this->buildRefinementCandidates(
            (string) $initialBestCandidate['format'],
            (int) $initialBestCandidate['quality']
        );

        if ($refinedCandidates === []) {
            return $initialBestCandidate;
        }

        $refinedBestCandidate = $this->runCandidateBatch($tempFilePath, $refinedCandidates);

        if ($refinedBestCandidate === null) {
            return $initialBestCandidate;
        }

        return $refinedBestCandidate['score'] > $initialBestCandidate['score']
            ? $refinedBestCandidate
            : $initialBestCandidate;
    }

    private function runCandidateBatch(string $tempFilePath, array $candidates): ?array
    {
        $queue = array_values($candidates);
        $running = [];
        $bestCandidate = null;

        while ($queue !== [] || $running !== []) {
            while ($queue !== [] && count($running) < self::MAX_PARALLEL_PROCESSES) {
                $candidate = array_shift($queue);
                $process = $this->createCandidateProcess($tempFilePath, $candidate);
                $process->start();

                $running[] = [
                    'process' => $process,
                    'candidate' => $candidate,
                ];
            }

            foreach ($running as $index => $job) {
                /** @var Process $process */
                $process = $job['process'];

                if ($process->isRunning()) {
                    continue;
                }

                if ($process->isSuccessful()) {
                    $result = json_decode($process->getOutput(), true);

                    if (is_array($result) && isset($result['score'])) {
                        if ($bestCandidate === null || $result['score'] > $bestCandidate['score']) {
                            $bestCandidate = $result;
                        }
                    }
                }

                unset($running[$index]);
            }

            if ($running !== []) {
                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }

            $running = array_values($running);
        }

        return $bestCandidate;
    }

    private function createCandidateProcess(string $tempFilePath, array $candidate): Process
    {
        $process = new Process([
            PHP_BINARY,
            base_path('bin/score_candidate.php'),
            $tempFilePath,
            $candidate['format'],
            (string) $candidate['quality'],
            (string) self::VISUAL_WEIGHT,
            (string) self::SIZE_WEIGHT,
        ]);

        $process->setTimeout(self::PROCESS_TIMEOUT_SECONDS);

        return $process;
    }

    private function buildCandidates(string $contentType): array
    {
        $candidates = [];

        foreach ($this->formatsForContentType($contentType) as $format) {
            foreach ($this->candidateQualitiesForFormat($format, $contentType) as $quality) {
                $candidates[] = [
                    'format' => $format,
                    'quality' => $quality,
                ];
            }
        }

        return $candidates;
    }

    private function buildRefinementCandidates(string $format, int $quality): array
    {
        if (strtolower($format) === 'png') {
            return [];
        }

        $candidates = [];

        for (
            $candidateQuality = max(0, $quality - self::REFINEMENT_RADIUS);
            $candidateQuality <= min(100, $quality + self::REFINEMENT_RADIUS);
            $candidateQuality += self::REFINEMENT_STEP
        ) {
            $candidates[] = [
                'format' => $format,
                'quality' => $candidateQuality,
            ];
        }

        if (!in_array($quality, array_column($candidates, 'quality'), true)) {
            $candidates[] = [
                'format' => $format,
                'quality' => $quality,
            ];
        }

        usort(
            $candidates,
            static fn (array $left, array $right): int => $left['quality'] <=> $right['quality']
        );

        return array_values(array_unique($candidates, SORT_REGULAR));
    }

    private function formatsForContentType(string $contentType): array
    {
        return match ($contentType) {
            'photo' => ['webp', 'avif'],
            'text_graphics' => ['png', 'webp'],
            'ui_screenshot' => ['png', 'webp'],
            'illustration' => ['webp', 'avif', 'png'],
            default => ['webp', 'jpeg'],
        };
    }

    private function candidateQualitiesForFormat(string $format, string $contentType): array
    {
        return match ($contentType) {
            'photo' => match ($format) {
                'webp' => [72, 78, 84, 90, 96],
                'avif' => [68, 74, 80, 86, 92],
                default => [85],
            },
            'text_graphics', 'ui_screenshot' => match ($format) {
                'png' => [100],
                'webp' => [90, 93, 96, 100],
                default => [100],
            },
            'illustration' => match ($format) {
                'png' => [100],
                'webp' => [78, 84, 90, 96],
                'avif' => [74, 80, 86, 92],
                default => [86],
            },
            default => match ($format) {
                'jpeg' => [68, 76, 84, 92],
                'webp' => [68, 76, 84, 92],
                default => [82],
            },
        };
    }

}
