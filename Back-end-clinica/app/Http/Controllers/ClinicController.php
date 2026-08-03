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
     * Persiste como data-URI no banco (disco do Railway é efêmero).
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

        try {
            $this->ensureLogoColumnSupportsDataUri();
            $url = $this->encodeLogoAsDataUri($request->file('logo'));
            $clinic->logo_url = $url;
            $clinic->save();
        } catch (QueryException $e) {
            Log::warning('clinic.logo_save_failed', [
                'clinic_id' => $clinic->id,
                'sqlstate' => $e->errorInfo[0] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível salvar o logo no banco. No Shell da API rode: php artisan marag:doctor --fix',
            ], 503);
        } catch (\Throwable $e) {
            Log::warning('clinic.logo_encode_failed', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível processar a imagem. Use PNG/JPEG até 2 MB.',
            ], 422);
        }

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

    /**
     * Garante coluna capaz de guardar data-URI (self-heal se migrate não rodou).
     */
    private function ensureLogoColumnSupportsDataUri(): void
    {
        try {
            $col = \Illuminate\Support\Facades\DB::connection('central')
                ->selectOne("SHOW COLUMNS FROM clinics WHERE Field = 'logo_url'");
            $type = strtolower((string) ($col->Type ?? ''));
            if ($type === '' || str_contains($type, 'text')) {
                return;
            }

            \Illuminate\Support\Facades\DB::connection('central')->statement(
                'ALTER TABLE clinics MODIFY logo_url MEDIUMTEXT NULL'
            );
        } catch (\Throwable $e) {
            Log::warning('clinic.logo_column_widen_failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Redimensiona (se GD) e devolve data-URI estável no banco.
     */
    private function encodeLogoAsDataUri(\Illuminate\Http\UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            throw new \RuntimeException('Arquivo ilegível');
        }

        $mime = $file->getMimeType() ?: 'image/png';
        $binary = null;

        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $raw = file_get_contents($path);
            $img = @imagecreatefromstring($raw ?: '');
            if ($img !== false) {
                $w = imagesx($img);
                $h = imagesy($img);
                $max = 512;
                if ($w > $max || $h > $max) {
                    $scale = min($max / max($w, 1), $max / max($h, 1));
                    $nw = max(1, (int) round($w * $scale));
                    $nh = max(1, (int) round($h * $scale));
                    $dst = imagecreatetruecolor($nw, $nh);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
                    imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    imagedestroy($img);
                    $img = $dst;
                }

                ob_start();
                imagepng($img, null, 6);
                $binary = ob_get_clean();
                imagedestroy($img);
                $mime = 'image/png';
            }
        }

        if ($binary === null || $binary === false || $binary === '') {
            $binary = file_get_contents($path);
        }

        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Falha ao ler bytes da imagem');
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
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
