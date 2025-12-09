<?php 
namespace App\Providers; 
use Illuminate\Support\ServiceProvider; 
use App\Events\Publisher\PhotoEventPublisherInterface; 
use App\Infrastructure\Photo\Events\PhotoEventPublisher; // конкретная реализация 
use App\Domain\Photo\Repositories\PhotoRepositoryInterface; 
use App\Infrastructure\Photo\Repositories\EloquentPhotoRepository; // конкретная реализация 
use App\Domain\Photo\Services\PhotoManagementServiceInterface; 
use App\Infrastructure\Photo\Services\MinioPhotoManagement;

class AppServiceProvider extends ServiceProvider 
{ /** * Register any application services. */ 
    
    public function register(): void 
    { 
        $this->app->bind(PhotoEventPublisherInterface::class, PhotoEventPublisher::class); 
        $this->app->bind(PhotoRepositoryInterface::class, EloquentPhotoRepository::class); 
        $this->app->bind(PhotoManagementServiceInterface::class, MinioPhotoManagement::class); 
    } 
        
        
        public function boot(): void 
        { 
            $this->app['queue']->addConnector('amqp', function () 
            { 
                return new \App\Queue\Connectors\AmqpConnector(); 
            }); 
        } 
    }