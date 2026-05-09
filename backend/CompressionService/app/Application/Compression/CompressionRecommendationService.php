<?php

namespace App\Application\Compression;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class CompressionRecommendationService
{
    /**
     * Коэффициенты многокритериальной оценки.
     *
     * α = 0.9 — вклад визуального качества.
     * β = 0.1 — вклад уменьшения размера.
     */
    private const VISUAL_WEIGHT = 0.9;
    private const SIZE_WEIGHT = 0.1;

    /**
     * Минимально допустимое perceptual quality.
     *
     * SSIM >= 0.95 считается visually acceptable.
     */
    private const MIN_VISUAL_SCORE = 0.95;

    /**
     * Параллельная обработка кандидатов.
     */
    private const MAX_PARALLEL_PROCESSES = 4;

    /**
     * Интервал polling процессов.
     */
    private const POLL_INTERVAL_MICROSECONDS = 10000;

    /**
     * Таймаут оценки кандидата.
     */
    private const PROCESS_TIMEOUT_SECONDS = 30;

    /**
     * Шаг первичного перебора quality.
     */
    private const INITIAL_QUALITY_STEP = 5;

    /**
     * Шаг refinement-поиска.
     */
    private const REFINEMENT_STEP = 2;

    /**
     * Радиус refinement-поиска.
     */
    private const REFINEMENT_RADIUS = 4;

    /**
     * Порог относительного прироста visual quality.
     */
    private const VISUAL_GAIN_PERCENT_THRESHOLD = 0.01;

    /**
     * Порог относительного роста размера файла.
     */
    private const SIZE_GROWTH_PERCENT_THRESHOLD = 0.15;

    public function recommend(
        string $sourceKey,
        string $contentType
    ): array {

        $originalBlob = Storage::disk('s3')->get($sourceKey);

        if ($originalBlob === false || $originalBlob === '') {
            throw new RuntimeException(
                'Failed to read source image.'
            );
        }

        $tempFilePath = tempnam(
            sys_get_temp_dir(),
            'cmp_rec_'
        );

        if ($tempFilePath === false) {
            throw new RuntimeException(
                'Failed to allocate temporary file.'
            );
        }

        file_put_contents(
            $tempFilePath,
            $originalBlob
        );

        try {

            $bestCandidate = $this->findBestCandidate(
                $tempFilePath,
                $contentType
            );

        } finally {

            @unlink($tempFilePath);
        }

        if ($bestCandidate === null) {

            return [
                'format' => 'webp',
                'quality' => 85,
                'estimated_size' => strlen($originalBlob),
                'saved_bytes' => 0,
                'saved_percent' => 0,
            ];
        }

        $originalSize = strlen($originalBlob);

        $savedBytes = max(
            0,
            $originalSize
            - (int) $bestCandidate['compressed_size']
        );

        $savedPercent = $originalSize > 0
            ? (int) round(
                ($savedBytes / $originalSize) * 100
            )
            : 0;

        return [
            'format' => $bestCandidate['format'],
            'quality' => $bestCandidate['quality'],
            'estimated_size' => (int) $bestCandidate['compressed_size'],
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'visual_score' => $bestCandidate['visual_score'],
        ];
    }

    public function estimateSpecific(
        string $sourceKey,
        string $contentType,
        string $format,
        int $quality
    ): array {

        unset($contentType);

        $normalizedQuality = max(
            0,
            min(100, $quality)
        );

        $originalBlob = Storage::disk('s3')->get($sourceKey);

        if ($originalBlob === false || $originalBlob === '') {
            throw new RuntimeException(
                'Failed to read source image.'
            );
        }

        $scorer = new CompressionCandidateScorer();

        $result = $scorer->score(
            $originalBlob,
            $format,
            $normalizedQuality,
            self::VISUAL_WEIGHT,
            self::SIZE_WEIGHT
        );

        if ($result === null) {
            throw new RuntimeException(
                'Failed to estimate compression result.'
            );
        }

        $originalSize = strlen($originalBlob);

        $savedBytes = max(
            0,
            $originalSize
            - (int) $result['compressed_size']
        );

        $savedPercent = $originalSize > 0
            ? (int) round(
                ($savedBytes / $originalSize) * 100
            )
            : 0;

        return [
            'format' => $format,
            'quality' => $normalizedQuality,
            'estimated_size' => (int) $result['compressed_size'],
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'visual_score' => $result['visual_score'] ?? null,
        ];
    }

    private function findBestCandidate(
        string $tempFilePath,
        string $contentType
    ): ?array {

        $format = $this->recommendedFormatForContentType(
            $contentType
        );

        $candidates = [];

        foreach (
            $this->candidateQualitiesForFormat(
                $format
            )
            as $quality
        ) {

            $candidates[] = [
                'format' => $format,
                'quality' => $quality,
            ];
        }

        $bestCandidate = $this->runCandidateBatch(
            $tempFilePath,
            $candidates
        );

        if ($bestCandidate === null) {
            return null;
        }

        /**
         * Refinement.
         */
        $refinedCandidates =
            $this->buildRefinementCandidates(
                $format,
                (int) $bestCandidate['quality']
            );

        if ($refinedCandidates === []) {
            return $bestCandidate;
        }

        $refinedBest =
            $this->runCandidateBatch(
                $tempFilePath,
                $refinedCandidates
            );

        return $refinedBest
            ?? $bestCandidate;
    }

    private function runCandidateBatch(
        string $tempFilePath,
        array $candidates
    ): ?array {

        $queue = array_values($candidates);

        $running = [];

        $results = [];

        while ($queue !== [] || $running !== []) {

            while (
                $queue !== []
                && count($running)
                < self::MAX_PARALLEL_PROCESSES
            ) {

                $candidate = array_shift($queue);

                $process =
                    $this->createCandidateProcess(
                        $tempFilePath,
                        $candidate
                    );

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

                    $result = json_decode(
                        $process->getOutput(),
                        true
                    );

                    if (
                        is_array($result)
                        && isset(
                            $result['visual_score'],
                            $result['compressed_size'],
                            $result['quality']
                        )
                    ) {

                        /**
                         * Отбрасываем perceptually
                         * unacceptable варианты.
                         */
                        if (
                            $result['visual_score']
                            >= self::MIN_VISUAL_SCORE
                        ) {

                            $results[] = $result;
                        }
                    }
                }

                unset($running[$index]);
            }

            if ($running !== []) {

                usleep(
                    self::POLL_INTERVAL_MICROSECONDS
                );
            }

            $running = array_values($running);
        }

        return $this->selectSweetSpot($results);
    }

    /**
     * Поиск точки насыщения качества.
     *
     * Алгоритм ищет момент,
     * когда perceptual quality
     * почти перестаёт расти,
     * но размер файла
     * продолжает увеличиваться.
     */
    private function selectSweetSpot(
        array $results
    ): ?array {

        if ($results === []) {
            return null;
        }

        usort(
            $results,
            static fn(array $a, array $b): int =>
                $a['quality']
                <=> $b['quality']
        );

        $best = $results[0];

        for (
            $i = 1;
            $i < count($results);
            $i++
        ) {

            $previous = $results[$i - 1];
            $current = $results[$i];

            $visualGainPercent =
                (
                    $current['visual_score']
                    - $previous['visual_score']
                )
                / max(
                    $previous['visual_score'],
                    0.0001
                );

            $sizeGrowthPercent =
                (
                    $current['compressed_size']
                    - $previous['compressed_size']
                )
                / max(
                    $previous['compressed_size'],
                    1
                );

            /**
             * Sweet spot найден.
             */
            if (
                $visualGainPercent
                < self::VISUAL_GAIN_PERCENT_THRESHOLD
                &&
                $sizeGrowthPercent
                > self::SIZE_GROWTH_PERCENT_THRESHOLD
            ) {

                return $previous;
            }

            $best = $current;
        }

        return $best;
    }

    private function createCandidateProcess(
        string $tempFilePath,
        array $candidate
    ): Process {

        $process = new Process([
            PHP_BINARY,
            base_path('bin/score_candidate.php'),
            $tempFilePath,
            $candidate['format'],
            (string) $candidate['quality'],
            (string) self::VISUAL_WEIGHT,
            (string) self::SIZE_WEIGHT,
        ]);

        $process->setTimeout(
            self::PROCESS_TIMEOUT_SECONDS
        );

        return $process;
    }

    /**
     * Выбор рекомендуемого формата
     * на основе типа изображения.
     *
     * Основано на исследованиях:
     * - AVIF показывает лучшую
     *   compression efficiency
     *   для photographic content.
     * - PNG остаётся предпочтительным
     *   для text graphics и UI.
     * - WebP эффективен
     *   для illustration content.
     */
    private function recommendedFormatForContentType(
        string $contentType
    ): string {

        return match ($contentType) {

            'photo' => 'avif',

            'text_graphics',
            'ui_screenshot' => 'png',

            'illustration' => 'webp',

            default => 'webp',
        };
    }

    private function candidateQualitiesForFormat(
        string $format
    ): array {

        return match ($format) {

            'avif' =>
                $this->qualityRange(60, 90),

            'webp' =>
                $this->qualityRange(70, 95),

            'jpeg' =>
                $this->qualityRange(75, 90),

            'png' => [100],

            default =>
                $this->qualityRange(75, 90),
        };
    }

    private function buildRefinementCandidates(
        string $format,
        int $quality
    ): array {

        if (strtolower($format) === 'png') {
            return [];
        }

        $candidates = [];

        for (
            $candidateQuality = max(
                0,
                $quality - self::REFINEMENT_RADIUS
            );

            $candidateQuality <= min(
                100,
                $quality + self::REFINEMENT_RADIUS
            );

            $candidateQuality += self::REFINEMENT_STEP
        ) {

            $candidates[] = [
                'format' => $format,
                'quality' => $candidateQuality,
            ];
        }

        return array_values(
            array_unique($candidates, SORT_REGULAR)
        );
    }

    private function qualityRange(
        int $start,
        int $end
    ): array {

        $lowerBound = max(
            0,
            min(100, $start)
        );

        $upperBound = max(
            $lowerBound,
            min(100, $end)
        );

        $qualities = [];

        for (
            $quality = $lowerBound;
            $quality <= $upperBound;
            $quality += self::INITIAL_QUALITY_STEP
        ) {

            $qualities[] = $quality;
        }

        if (
            !in_array(
                $upperBound,
                $qualities,
                true
            )
        ) {

            $qualities[] = $upperBound;
        }

        return array_values(
            array_unique($qualities)
        );
    }
}