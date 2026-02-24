<?php 
namespace App\Providers; 
use Illuminate\Support\ServiceProvider; 
use App\Events\Publisher\PhotoEventPublisherInterface; 
use App\Infrastructure\Photo\Events\PhotoEventPublisher;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface; 
use App\Domain\Folder\Repositories\FolderRepositoryInterface; 
use App\Infrastructure\Photo\Repositories\EloquentPhotoRepository;
use App\Infrastructure\User\Repositories\EloquentUserRepository; 
use App\Infrastructure\Folder\Repositories\EloquentFolderRepository; 
use App\Domain\Photo\Services\PhotoManagementServiceInterface; 
use App\Infrastructure\Photo\Services\MinioPhotoManagement;
use App\Infrastructure\Tag\Repositories\EloquentTagRepository;
use App\Domain\Tag\Repositories\TagRepositoryInterface;
use Illuminate\Support\Facades\Queue;



class AppServiceProvider extends ServiceProvider 
{ 
    
    public function register(): void 
    { 
        $this->app->bind(PhotoEventPublisherInterface::class, PhotoEventPublisher::class); 
        $this->app->bind(PhotoRepositoryInterface::class, EloquentPhotoRepository::class); 
        $this->app->bind(PhotoManagementServiceInterface::class, MinioPhotoManagement::class); 
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class); 
        $this->app->bind(TagRepositoryInterface::class, EloquentTagRepository::class);
        $this->app->bind(FolderRepositoryInterface::class, EloquentFolderRepository::class);
    } 
        
        
        public function boot(): void 
        { 

        } 
    }