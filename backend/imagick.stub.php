<?php

if (!class_exists('Imagick')) {
    class Imagick
    {
        public const INTERLACE_PLANE = 4;
        public const METRIC_MEANSQUAREERROR = 5;

        public function readImageBlob(string $image): bool
        {
            return true;
        }

        public function stripImage(): bool
        {
            return true;
        }

        public function setImageFormat(string $format): bool
        {
            return true;
        }

        public function setImageCompressionQuality(int $quality): bool
        {
            return true;
        }

        public function setInterlaceScheme(int $scheme): bool
        {
            return true;
        }

        public function setOption(string $key, string $value): bool
        {
            return true;
        }

        public function getImagesBlob(): string
        {
            return '';
        }

        public function compareImages(self $compare, int $metric): array
        {
            return [new self(), 0.0];
        }

        public function clear(): bool
        {
            return true;
        }

        public function destroy(): bool
        {
            return true;
        }
    }
}
