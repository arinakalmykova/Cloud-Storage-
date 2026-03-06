<?php
namespace App\Infrastructure\Color\Repositories;
use App\Domain\Color\Repositories\ColorRepositoryInterface;
use App\Domain\Color\Entities\Color;
use App\Models\Color as ColorModel;


class EloquentColorRepository implements ColorRepositoryInterface
{
     public function save(Color $color): void
    {
        ColorModel::updateOrCreate(
            ['id' => $color->getId()],
            ['color' => $color->getColor()]
        );
    }

     public function getColors(): array
    {
        return ColorModel::all()->map(function (ColorModel $model) {
            return $model->color;
        })->toArray();
    }

     public function findByHex(string $hex): ?Color
    {
        $model = ColorModel::where('color', $hex)->first();
        if (!$model) return null;

        return new Color($model->id, $model->color);
    }
}
