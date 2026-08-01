<?php

namespace App\Http\Controllers;

use App\Custom\Jwt;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        if (! TenantContext::clinic()) {
            return response()->json([
                'message' => 'Informe a clínica (X-Clinic-Slug) para autenticar.',
            ], 400);
        }

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['senha'], $user->password)) {
            return response()->json([
                'message' => 'E-mail ou senha incorretos.',
            ], 401);
        }

        // situacao_id 1 = ativo (mesma tabela situacoes)
        if ((int) $user->situacao_id !== 1) {
            return response()->json([
                'message' => 'Usuário inativo. Contate o administrador da clínica.',
            ], 403);
        }

        $token = Jwt::create($user);

        return response()->json([
            'token' => $token,
            'user' => Jwt::claimsFromUser($user),
            'clinic' => TenantContext::clinic()?->branding(),
        ]);
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'valid' => true,
            'user' => Jwt::claimsFromUser($user),
            'clinic' => TenantContext::clinic()?->branding(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $dados = $request->validate([
            'senha_atual' => ['required', 'string'],
            'senha_nova' => ['required', 'string', 'min:8'],
        ]);

        $user = $request->user();
        if (! $user || ! Hash::check($dados['senha_atual'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta.',
            ], 401);
        }

        $user->password = Hash::make($dados['senha_nova']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso',
        ]);
    }
}
