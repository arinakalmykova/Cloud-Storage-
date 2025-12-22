<?php
namespace App\Infrastructure\Tag;
use App\Domain\Tag\Entities\Tag;
use App\Domain\Tag\Repositories\TagRepositoryInterface;
use App\Models\Tag as TagModel;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function findByName(string $name): ?Tag
    {
        $model = TagModel::where('name', $name)->first();
        if (!$model) return null;

        return new Tag($model->id, $model->name);
    }

    public function save(Tag $tag): void
    {
        TagModel::updateOrCreate(
            ['id' => $tag->getId()],
            ['name' => $tag->getName()]
        );
    }

    public function findById(array $id): array
    {
        $models = TagModel::whereIn('id', $ids)->get();
        $tags = [];
        foreach ($models as $model) {
            $tags[] = new Tag($model->id, $model->name);
        }
        return $tags;
    }
}
