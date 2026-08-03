<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Support\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    /**
     * Branding público (D14) — lê só o banco central.
     */
    public function branding(Request $request): JsonResponse
    {
        $slug = strtolower(trim((string) ($request->query('slug') ?: $request->header('X-Clinic-Slug') ?: '')));

        if ($slug === '') {
            // PRG 1.5: DEFAULT_CLINIC_SLUG apenas fora de production
            if (! app()->environment('production')) {
                $slug = strtolower(trim((string) env('DEFAULT_CLINIC_SLUG', '')));
            }
        }

        if ($slug === '') {
            return response()->json([
                'success' => false,
                'message' => 'Informe o slug da clínica.',
            ], 422);
        }

        try {
            $clinic = Clinic::query()
                ->where('slug', $slug)
                ->where('ativo', true)
                ->first();
        } catch (QueryException $e) {
            Log::warning('clinic.branding.db_unavailable', [
                'slug' => $slug,
                'sqlstate' => $e->errorInfo[0] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Serviço temporariamente indisponível. Tente novamente em instantes.',
            ], 503);
        }

        if (! $clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($clinic->branding(), [
                'vendor' => [
                    'nome' => 'Marag',
                    'copyright' => '© Marag Tecnologia',
                ],
            ]),
        ]);
    }

    /**
     * Atualiza white-label da clínica autenticada (admin).
     */
    public function updateBranding(Request $request): JsonResponse
    {
        try {
            $dados = $request->validate([
                'nome' => 'sometimes|required|string|max:255',
            'logo_url' => 'nullable|string|max:3000000',
                'cor_primaria' => ['sometimes', 'required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
                'cor_secundaria' => ['sometimes', 'required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        }

        $clinic = TenantContext::clinic();
        if (! $clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica não resolvida.',
            ], 400);
        }

        $clinic->fill($dados);
        $clinic->save();

        return response()->json([
            'success' => true,
            'message' => 'Branding atualizado',
            'data' => $clinic->branding(),
        ]);
    }

    /**
     * Upload do logo white-label (admin).
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo inválido',
                'errors' => $e->errors(),
            ], 422);
        }

        $clinic = TenantContext::clinic();
        if (! $clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica não resolvida.',
            ], 400);
        }

        $this->deleteStoredLogo($clinic);

        $file = $request->file('logo');
        $binary = file_get_contents($file->getRealPath());
        if ($binary === false || $binary === '') {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível ler o arquivo enviado.',
            ], 422);
        }

        // Persistência no banco (data-URI): sobrevive a redeploy no Railway (disco efêmero).
        $mime = $file->getMimeType() ?: 'image/png';
        $url = 'data:'.$mime.';base64,'.base64_encode($binary);

        $clinic->logo_url = $url;
        $clinic->save();

        return response()->json([
            'success' => true,
            'message' => 'Logo enviado com sucesso',
            'url' => $url,
            'data' => $clinic->branding(),
        ]);
    }

    /**
     * Remove o logo white-label (admin).
     */
    public function removeLogo(): JsonResponse
    {
        $clinic = TenantContext::clinic();
        if (! $clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica não resolvida.',
            ], 400);
        }

        $this->deleteStoredLogo($clinic);
        $clinic->logo_url = null;
        $clinic->save();

        return response()->json([
            'success' => true,
            'message' => 'Logo removido',
            'data' => $clinic->branding(),
        ]);
    }

    private function deleteStoredLogo(Clinic $clinic): void
    {
        $url = (string) ($clinic->logo_url ?? '');
        if ($url === '' || str_starts_with($url, 'data:')) {
            return;
        }

        $marker = '/storage/';
        $pos = strpos($url, $marker);
        if ($pos === false) {
            return;
        }

        $relative = substr($url, $pos + strlen($marker));
        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
