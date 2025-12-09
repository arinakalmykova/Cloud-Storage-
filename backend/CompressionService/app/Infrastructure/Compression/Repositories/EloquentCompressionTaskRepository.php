<?php 
namespace App\Infrastructure\Compression\Repositories;

use App\Domain\Compression\Repositories\CompressionTaskRepositoryInterface;
use App\Domain\Compression\Entities\PhotoCompressionTask;
use App\Domain\Compression\ValueObjects\CompressionStatus;
use app\tasks\CompressionTask as CompressionTaskModel;

class EloquentCompressionTaskRepository implements CompressionTaskRepositoryInterface
{
    public function save(PhotoCompressionTask $task):void
    {
        CompressionTaskModel::updateOrCreate(['id' => $task->id],
        [
            'photo_id' => $task->photoId,
            'status' => $task->status->value(),
            'sourse_url' => $task->SourseUrl,
            'destination_path' => $task->destinationPath
        ]);
    }

    public function findPending(): array
    {
        $task = CompressionTaskModel::where('status','pending')->get();
        return $task->map(fn($t) => new CompressionTask($t->task_id, $t->photo_id, $t->source_url))->toArray();
    }
}