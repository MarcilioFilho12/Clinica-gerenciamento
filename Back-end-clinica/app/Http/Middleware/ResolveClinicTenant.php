<?php

namespace App\Http\Middleware;

use App\Custom\Jwt;
use App\Models\Clinic;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveClinicTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->resolveSlug($request);

        if (! $slug) {
            return response()->json([
                'message' => 'Informe a clínica (header X-Clinic-Slug ou parâmetro clinic).',
            ], 400);
        }

        $clinic = Clinic::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->first();

        if (! $clinic) {
            return response()->json([
                'message' => 'Clínica não encontrada ou inativa.',
            ], 404);
        }

        TenantContext::set($clinic);
        $request->attributes->set('clinic', $clinic);

        return $next($request);
    }

    private function resolveSlug(Request $request): ?string
    {
        $fromHeader = $request->header('X-Clinic-Slug');
        if (is_string($fromHeader) && $fromHeader !== '') {
            return strtolower(trim($fromHeader));
        }

        $fromQuery = $request->query('clinic');
        if (is_string($fromQuery) && $fromQuery !== '') {
            return strtolower(trim($fromQuery));
        }

        $token = Jwt::bearerToken($request);
        if ($token) {
            try {
                $decoded = Jwt::decode($token);
                $fromJwt = $decoded->data->clinic_slug ?? null;
                if (is_string($fromJwt) && $fromJwt !== '') {
                    return strtolower(trim($fromJwt));
                }
            } catch (Throwable) {
                // slug via JWT opcional aqui; JwtAuthenticate valida depois
            }
        }

        $fallback = env('DEFAULT_CLINIC_SLUG');
        if (is_string($fallback) && $fallback !== '') {
            return strtolower(trim($fallback));
        }

        return null;
    }
}
