<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Compression\Services\CompressorServiceInterface; 
use App\Infrastructure\Compression\Services\ImageMagickCompressor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
