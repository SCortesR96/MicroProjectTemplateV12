<?php

namespace App\Models;

use App\Utils\Enums\ApiStatusEnum;

class ResponseModel
{
    protected ApiStatusEnum $status;

    protected string $message;

    protected mixed $data;

    public function __construct(ApiStatusEnum $status, string $message, mixed $data = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
