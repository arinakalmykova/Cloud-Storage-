<?php 
namespace App\Providers; 
use Illuminate\Support\ServiceProvider; 
use App\Events\Publisher\PhotoEventPublisherInterface; 
use App\Infrastructure\Photo\Events\PhotoEventPublisher;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Photo\Repositories\UserRepositoryInterface; 
use App\Infrastructure\Photo\Repositories\EloquentPhotoRepository;
use App\Infrastructure\Photo\Repositories\EloquentUserRepository; 
use App\Domain\Photo\Services\PhotoManagementServiceInterface; 
use App\Infrastructure\Photo\Services\MinioPhotoManagement;
use App\Infrastructure\Tag\Repositories\EloquentTagRepository;
use App\Domain\Tag\Repositories\TagRepositoryInterface;



class AppServiceProvider extends ServiceProvider 
{ 
    
    public function register(): void 
    { 
        $this->app->bind(PhotoEventPublisherInterface::class, PhotoEventPublisher::class); 
        $this->app->bind(PhotoRepositoryInterface::class, EloquentPhotoRepository::class); 
        $this->app->bind(PhotoManagementServiceInterface::class, MinioPhotoManagement::class); 
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class); 
        $this->app->bind(TagRepositoryInterface::class, EloquentTagRepository::class);
    } 
        
        
        public function boot(): void 
        { 
            $this->app['queue']->addConnector('amqp', function () 
            { 
                return new \App\Queue\Connectors\AmqpConnector(); 
            }); 
        } 
    }