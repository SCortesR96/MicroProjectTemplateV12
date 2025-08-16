<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $token = $this->extractToken($request);
        if (! $token) {
            return $this->unauthorized('Token not provided');
        }

        $payload = $this->getCachedOrDecodedPayload($token);
        if (! $payload) {
            return $this->unauthorized('Invalid token');
        }

        if (! $this->isTokenTimeValid($payload)) {
            return $this->unauthorized('Token not yet valid or expired');
        }

        if (! $this->isRoleAllowed($roles, $payload)) {
            return response()->json(
                ['error' => 'Forbidden'],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $token = $request->header('Authorization');

        return $token ? str_replace('Bearer ', '', $token) : null;
    }

    private function getCachedOrDecodedPayload(string $token): ?object
    {
        $cacheKey = 'jwt_validated:'.md5($token);
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            try {
                $payload = JWT::decode($token, new Key(
                    config('environment.jwt.secret'),
                    config('environment.jwt.algorithm')
                ));

                $ttl = isset($payload->exp) ? max(0, $payload->exp - time()) : 60;
                Cache::put($cacheKey, $payload, min($ttl, 60));
            } catch (Exception $e) {
                return null;
            }
        }

        return $payload;
    }

    private function isTokenTimeValid(object $payload): bool
    {
        if (isset($payload->exp) && $payload->exp < time()) {
            return false;
        }
        if (isset($payload->nbf) && $payload->nbf > time()) {
            return false;
        }

        return true;
    }

    private function isRoleAllowed(array $roles, object $payload): bool
    {
        if (count($roles) > 0 && isset($payload->role)) {
            return in_array($payload->role, $roles);
        }

        return true;
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(
            ['error' => $message],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }
}
