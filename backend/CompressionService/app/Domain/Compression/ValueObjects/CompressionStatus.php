<?php 
namespace App\Domain\Compression\ValueObject;

class CompressionStatus {
    public const PROCESSING='processing';
    public const DONE='done';
    public const FAILED='failed';
    private string $status;

    public function __construct(string $status){
        $this->status=$status;
    }

     public function failed():self {
        return new self(self::FAILED);
    }

    public function processing():self {
        return new self(self::PROCESSING);
    }

    public function done():self {
        return new self(self::DONE);
    }

    public function value(): string {
        return $this->status;
    }

}