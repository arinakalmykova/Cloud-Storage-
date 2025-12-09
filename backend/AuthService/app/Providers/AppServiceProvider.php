<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Auth\Repositories\UserRepositoryInterface; 
use App\Infrastructure\Auth\Repositories\EloquentUserRepository; 
use App\Application\Auth\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class); 
        $this->app->bind(AuthService::class, function($app) {
        return new AuthService(
             $app->make(UserRepositoryInterface::class),
             env('JWT_SECRET')      
           
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
