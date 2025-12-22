<?php
namespace App\Application\Tag;
use App\Domain\Tag\Entities\Tag;
use App\Domain\Tag\Repositories\TagRepositoryInterface;
use Illuminate\Support\Str;

class TagService
{
    public function __construct(
        private TagRepositoryInterface $tags
    ) {}

    public function getOrCreate(string $name): Tag
    {
        $name = mb_strtolower(trim($name));

        $tag = $this->tags->findByName($name);
        if ($tag) {
            return $tag;
        }

        $tag = new Tag(
            id: Str::uuid()->toString(),
            name: $name
        );

        $this->tags->save($tag);

        return $tag;
    }
}
