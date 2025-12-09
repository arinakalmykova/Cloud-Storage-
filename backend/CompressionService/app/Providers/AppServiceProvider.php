<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Compression\Events\Publisher\CompressionEventPublisherInterface; 
use App\Infrastructure\Compression\Events\CompressEventPublisher; 
use App\Domain\Compression\Repositories\CompressionTaskRepositoryInterface; 
use App\Infrastructure\Compression\Repositories\EloquentCompressionTaskRepository; 
use App\Domain\Compression\Services\CompressorServiceInterface; 
use App\Infrastructure\Compression\Services\ImageMagickCompressor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompressionEventPublisherInterface::class,CompressEventPublisher::class); 
        $this->app->bind(CompressionTaskRepositoryInterface::class, EloquentCompressionTaskRepository::class); 
        $this->app->bind(CompressorServiceInterface::class, ImageMagickCompressor::class); 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
