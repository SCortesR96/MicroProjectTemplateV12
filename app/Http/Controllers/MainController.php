<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use App\Utils\Enums\ApiStatusEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    public function response(ApiStatusEnum $status, string $message, $data = [], int $code = Response::HTTP_OK): JsonResponse
    {
        $response = new ResponseModel($status, $message, $data);

        return response()->json(
            $response->toArray(),
            $code,
            ['Content-Type' => 'application/json']
        );
    }

    public function success(string $message, $data = [], int $code = Response::HTTP_OK): JsonResponse
    {
        return $this->response(ApiStatusEnum::SUCCESS, $message, $data, $code);
    }

    public function warning(string $message, $data = [], int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->response(ApiStatusEnum::WARNING, $message, $data, $code);
    }

    public function error(Exception $e, string $location, string $channel): JsonResponse
    {
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        if (is_int($e->getCode()) && $e->getCode() >= 300 && $e->getCode() < 500) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        } else {
            $msg = $this->getErrorMessage($e, $location, $channel);
        }

        return $this->response(ApiStatusEnum::ERROR, $msg, [], $code);
    }

    protected function handleResponse($data)
    {
        if ($data->isSuccess) {
            return $this->success(
                $data->response,
                $data->data ?? $data->isSuccess,
                JsonResponse::HTTP_OK
            );
        }

        return $this->warning(
            $data->response,
            $data->data ?? $data->isSuccess,
            $data->code
        );
    }

    protected function safeExecute(callable $fn, string $context, string $entity)
    {
        try {
            return $fn();
        } catch (Exception $e) {
            return $this->error($e, $context, $entity);
        }
    }

    // ERROR SECTION
    private function generateErrorCode(string $channel): string
    {
        return "{$channel}-".Carbon::now()->timestamp;
    }

    private function exceptionMessage(string $errorCode): string
    {
        return __('responses.error.catchError').' '.__('responses.error.getCode')." {$errorCode}";
    }

    private function logException(Exception $e, string $location, string $channel, string $errorCode)
    {
        $errorLogMessage = "{$channel}: ({$location}-{$errorCode}) :  \r\n".$e->getMessage().PHP_EOL;
        Log::channel('stack')->error($errorLogMessage);
    }

    private function getErrorMessage(Exception $e, string $location, string $channel): string
    {
        $errorCode = $this->generateErrorCode($channel);
        $this->logException($e, $location, $channel, $errorCode);

        return $this->exceptionMessage($errorCode);
    }
}
