<?php 

namespace App\Domain\Photo\Entities;

class User
{
    private string $id;


    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string { return $this->id; }

}
