<?php

namespace App\Traits;

use App\Utils\Enums\ApiStatusEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait RequestResponse
{
    public function response(Validator $validator): JsonResponse
    {
        $response = response()->json(
            [
                'status' => ApiStatusEnum::ERROR,
                'message' => __('responses.error.verifyData'),
                'data' => $validator->errors()->all(),
            ],
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            ['Content-Type' => 'application/json']
        );

        throw new ValidationException($validator, $response);
    }
}
