<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Situacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Listar todos os usuários
     */
     public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['profile', 'situacao']);

            // Filtrar por profile_id se fornecido
            if ($request->has('profile_id')) {
                $query->where('profile_id', $request->profile_id);
            }

            $users = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Usuários listados com sucesso',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cadastrar um novo usuário
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|string|min:6',
                'profile_id' => 'nullable|exists:auth_profiles,id',
                'situacao_id' => 'nullable|exists:situacoes,id',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'situacao_id' => $request->situacao_id,
            ]);

            // Carregar os relacionamentos
            $user->load(['profile', 'situacao']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso',
                'data' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuário por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with(['profile', 'situacao'])->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário encontrado com sucesso',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar usuário existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Busca o usuário
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Valida os dados
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id . '|max:255',
                'password' => 'nullable|string|min:6',
                'profile_id' => 'nullable|exists:auth_profiles,id',
                'situacao_id' => 'nullable|exists:situacoes,id',
            ]);

            DB::beginTransaction();

            // Prepara os dados para atualização
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'profile_id' => $request->profile_id,
                'situacao_id' => $request->situacao_id,
            ];

            // Atualiza senha apenas se fornecida
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Atualiza os dados do usuário
            $user->update($updateData);

            // Carregar os relacionamentos
            $user->load(['profile', 'situacao']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir usuário (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Busca o usuário
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            // Verifica se já foi excluído (soft delete)
            if ($user->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário já foi excluído'
                ], 400);
            }

            // Executa soft delete
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
