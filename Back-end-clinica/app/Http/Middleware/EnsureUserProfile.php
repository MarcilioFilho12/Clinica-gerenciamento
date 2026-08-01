<?php

namespace App\Http\Middleware;

use App\Support\Profiles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserProfile
{
    /**
     * @param  string  ...$profiles  IDs ou nomes (admin|recepcao|profissional|1|2|3)
     */
    public function handle(Request $request, Closure $next, string ...$profiles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        $allowedIds = $this->resolveProfileIds($profiles);

        if ($allowedIds === [] || ! in_array((int) $user->profile_id, $allowedIds, true)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar este recurso.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * @param  list<string>  $profiles
     * @return list<int>
     */
    private function resolveProfileIds(array $profiles): array
    {
        $map = [
            'admin' => Profiles::ADMIN,
            'administrador' => Profiles::ADMIN,
            'recepcao' => Profiles::RECEPCAO,
            'recepção' => Profiles::RECEPCAO,
            'profissional' => Profiles::PROFISSIONAL,
            'medico' => Profiles::PROFISSIONAL,
            'médico' => Profiles::PROFISSIONAL,
            (string) Profiles::ADMIN => Profiles::ADMIN,
            (string) Profiles::RECEPCAO => Profiles::RECEPCAO,
            (string) Profiles::PROFISSIONAL => Profiles::PROFISSIONAL,
        ];

        $ids = [];

        foreach ($profiles as $profile) {
            $key = mb_strtolower(trim($profile));
            if (isset($map[$key])) {
                $ids[] = $map[$key];
            }
        }

        return array_values(array_unique($ids));
    }
}
