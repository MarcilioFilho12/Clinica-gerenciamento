<?php

namespace App\Http\Middleware;

use App\Custom\Jwt;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = Jwt::bearerToken($request);

        if (! $token) {
            return response()->json([
                'message' => 'Não autenticado. Informe o token Bearer.',
            ], 401);
        }

        try {
            $decoded = Jwt::decode($token);
            $userId = (int) ($decoded->data->id ?? 0);

            if ($userId < 1) {
                return response()->json([
                    'message' => 'Token inválido.',
                ], 401);
            }

            $user = User::query()->find($userId);

            if (! $user) {
                return response()->json([
                    'message' => 'Usuário do token não encontrado.',
                ], 401);
            }

            $tokenSlug = isset($decoded->data->clinic_slug)
                ? strtolower((string) $decoded->data->clinic_slug)
                : null;
            $contextSlug = \App\Support\TenantContext::slug();
            if ($tokenSlug && $contextSlug && $tokenSlug !== $contextSlug) {
                return response()->json([
                    'message' => 'Token não pertence a esta clínica.',
                ], 401);
            }

            auth()->setUser($user);
            $request->setUserResolver(static fn () => $user);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Token inválido ou expirado.',
            ], 401);
        }

        return $next($request);
    }
}
