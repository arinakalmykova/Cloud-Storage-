<?php

declare(strict_types=1);

use App\Application\Compression\CompressionCandidateScorer;

require dirname(__DIR__) . '/vendor/autoload.php';

[$script, $filePath, $format, $quality, $visualWeight, $sizeWeight] = $argv + [null, null, null, null, null, null];

if ($filePath === null || $format === null || $quality === null || $visualWeight === null || $sizeWeight === null) {
    fwrite(STDERR, "Missing arguments.\n");
    exit(1);
}

$originalBlob = @file_get_contents($filePath);
if ($originalBlob === false || $originalBlob === '') {
    fwrite(STDERR, "Failed to read source blob.\n");
    exit(1);
}

$scorer = new CompressionCandidateScorer();
$result = $scorer->score(
    $originalBlob,
    (string) $format,
    (int) $quality,
    (float) $visualWeight,
    (float) $sizeWeight
);

if ($result === null) {
    fwrite(STDERR, "Candidate encoding failed.\n");
    exit(2);
}

echo json_encode($result, JSON_THROW_ON_ERROR);
