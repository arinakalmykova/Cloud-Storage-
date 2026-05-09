<?php

namespace App\Infrastructure\Compression\Services;

use App\Domain\Compression\Services\CompressorServiceInterface;
use App\Domain\Compression\ValueObjects\CompressionQuality;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use RuntimeException;
use Symfony\Component\Process\Process;

class ImageMagickCompressor implements CompressorServiceInterface
{
    private ?string $lastCompressedBlob = null;
    private ImageManager $imageManager;
    
    public function __construct()
    {
        $this->imageManager = new ImageManager(new ImagickDriver());
    }
    
    public function compressAndUpload(
        string $sourceKey,
        CompressionQuality $quality,
        string $format
    ): string {
        $originalContent = Storage::disk('s3')->get($sourceKey);
        
        if (empty($originalContent)) {
            throw new RuntimeException('Source file is empty or not found: ' . $sourceKey);
        }
        
        try {
            if ($format === 'avif') {
                $blob = $this->compressToAvif($originalContent, $quality);
            } else {
                $image = $this->imageManager->read($originalContent);
                
                $encoder = match (strtolower($format)) {
                    'jpg', 'jpeg' => new JpegEncoder(quality: $quality->value(), progressive: true, strip: true),
                    'png' => new PngEncoder(),
                    'webp' => new WebpEncoder(quality: $quality->value(), strip: true),
                    default => throw new RuntimeException("Unsupported format: {$format}")
                };
                
                $encoded = $image->encode($encoder);
                $blob = (string) $encoded;
            }
            
            if (empty($blob)) {
                throw new RuntimeException("Failed to encode image to {$format}");
            }
            
            $this->lastCompressedBlob = $blob;
            
            $mimeType = match (strtolower($format)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                default => 'application/octet-stream',
            };
            
            Storage::disk('s3')->put($sourceKey, $blob, [
                'ContentType' => $mimeType,
                'Metadata' => [
                    'compressed' => 'true',
                    'quality' => (string) $quality->value(),
                    'format' => $format
                ]
            ]);
            
            return 'http://127.0.0.1:9000/photo/' . $sourceKey;
            
        } catch (\Exception $e) {
            Log::error('Compression failed', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException("Failed to compress image: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Сжатие в AVIF через avifenc
     */
    private function compressToAvif(string $content, CompressionQuality $quality): string
    {
        // Проверяем наличие avifenc в разных местах
        $avifencPath = $this->findAvifenc();
        
        if (!$avifencPath) {
            Log::warning('avifenc not found, falling back to WebP');
            // Fallback на WebP
            $image = $this->imageManager->read($content);
            $encoded = $image->encode(new WebpEncoder(quality: $quality->value(), strip: true));
            return (string) $encoded;
        }
        
        $tempInput = tempnam(sys_get_temp_dir(), 'avif_in_');
        $tempOutput = tempnam(sys_get_temp_dir(), 'avif_out_') . '.avif';
        
        file_put_contents($tempInput, $content);
        
        try {
            $process = new Process([
                $avifencPath,
                '--qcolor', (string) $quality->value(),
                '--qalpha', (string) $quality->value(),
                '--speed', '4',
                $tempInput,
                $tempOutput
            ]);
            
            $process->setTimeout(60);
            $process->run();
            
            if (!$process->isSuccessful()) {
                Log::error('avifenc failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'command' => $process->getCommandLine()
                ]);
                throw new RuntimeException('avifenc failed: ' . $process->getErrorOutput());
            }
            
            if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
                throw new RuntimeException('avifenc produced no output');
            }
            
            return file_get_contents($tempOutput);
            
        } finally {
            @unlink($tempInput);
            @unlink($tempOutput);
        }
    }
    
    /**
     * Ищет avifenc в системе
     */
    private function findAvifenc(): ?string
    {
        // Проверяем известные пути
        $possiblePaths = [
            '/usr/local/bin/avifenc',
            '/usr/bin/avifenc',
            '/opt/bin/avifenc',
            '/bin/avifenc',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        
        // Пробуем найти через which
        try {
            $process = new Process(['which', 'avifenc']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $path = trim($process->getOutput());
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        } catch (\Exception $e) {
            // Игнорируем
        }
        
        // Пробуем найти через find (может быть медленно)
        try {
            $process = new Process(['find', '/usr', '-name', 'avifenc', '-type', 'f', '2>/dev/null']);
            $process->setTimeout(5);
            $process->run();
            
            if ($process->isSuccessful()) {
                $path = trim($process->getOutput());
                if (!empty($path) && file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        } catch (\Exception $e) {
            // Игнорируем
        }
        
        return null;
    }

    public function getLastCompressedBlob(): ?string
    {
        return $this->lastCompressedBlob;
    }
}
