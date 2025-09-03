<?php

namespace App\Utils\Entities\Responses;

use Spatie\LaravelData\Data;

class ResponseEntity extends Data
{
    public function __construct(
        public string $response,
        public bool $isSuccess,
        public ?int $code = 200,
        public mixed $data = null,
    ) {}

    public static function success(
        string $message,
        mixed $data = null,
        int $code = 200
    ): self {
        return new self($message, true, $code, $data);
    }

    public static function error(
        string $message,
        int $code = 500,
        mixed $data = null
    ): self {
        return new self($message, false, $code, $data);
    }
}
