<?php 
namespace App\Domain\Compression\Repositories;

use App\Domain\Compression\Entities\PhotoCompressionTask;

interface CompressionTaskRepositoryInterface
{
    public function save(PhotoCompressionTask $task):void;
    public function findPending(): array;
}


