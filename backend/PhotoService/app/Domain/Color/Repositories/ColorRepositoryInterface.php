<?php 
namespace App\Domain\Color\Repositories;

use App\Domain\Color\Entities\Color;

interface ColorRepositoryInterface
{
    public function save(Color $color):void;
    public function getColorsByUser(string $userId):array;
    public function findByHex(string $hex): ?Color;
}


