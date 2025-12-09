<?php
namespace App\Domain\Photo\ValueObjects;

class FileName{
    public string $name;

    public function __construct(string $name)
    {
        if (empty($name) || strlen($name)>100)
            throw new \Exception("Invalid filename");
        $this->name=$name;
    }

    public function value():string 
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}