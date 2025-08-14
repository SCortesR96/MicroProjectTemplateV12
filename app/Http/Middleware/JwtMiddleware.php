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
        $token = $request->header('Authorization');

        if (! $token) {
            return $this->unauthorized('Token not provided');
        }

        // Limpia el prefijo Bearer
        $token = str_replace('Bearer ', '', $token);

        // Revisa si ya está en caché
        $cacheKey = 'jwt_validated:'.md5($token);
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            try {
                $payload = JWT::decode($token, new Key(
                    config('environment.jwt.secret'),
                    config('environment.jwt.algorithm')
                ));

                // Validar exp (expiración)
                if (isset($payload->exp) && $payload->exp < time()) {
                    return $this->unauthorized('Token has expired');
                }

                // Validar nbf (not before)
                if (isset($payload->nbf) && $payload->nbf > time()) {
                    return $this->unauthorized('Token not yet valid');
                }

                // Guardar en caché por 1 minuto (o menos que la expiración)
                $ttl = isset($payload->exp) ? max(0, $payload->exp - time()) : 60;
                Cache::put($cacheKey, $payload, min($ttl, 60));
            } catch (Exception $e) {
                return $this->unauthorized('Invalid token: '.$e->getMessage());
            }
        }

        // Si el middleware recibe roles, validar que el usuario los tenga
        if (! empty($roles) && isset($payload->role)) {
            if (! in_array($payload->role, $roles)) {
                return response()->json(['error' => 'Forbidden'], JsonResponse::HTTP_FORBIDDEN);
            }
        }

        // Compartir el payload con el request para que el controlador lo use
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(['error' => $message], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
