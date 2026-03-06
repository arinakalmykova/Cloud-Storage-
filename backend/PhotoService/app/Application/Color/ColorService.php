<?php
namespace App\Application\Color;
use App\Domain\Color\Entities\Color;
use App\Domain\Color\Repositories\ColorRepositoryInterface;
use Illuminate\Support\Str;

class ColorService
{
    public function __construct(
        private ColorRepositoryInterface $color
    ) {}

    public function createColor(string $color): Color
    {
        $color = new Color(Str::uuid()->toString(), $color);
        $this->color->save($color);
        return $color;
    }

    public function getColors(): array
    {
        return $this->color->getColors();
    }

    public function findByHex(string $hex): ?Color
    {
        return $this->color->findByHex($hex);
    }
}
