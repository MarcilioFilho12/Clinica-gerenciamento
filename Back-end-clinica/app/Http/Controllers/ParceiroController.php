<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use App\Models\TipoParceiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParceiroController extends Controller
{
    /**
     * Cadastrar um novo parceiro
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'tipo_parceiro_id' => 'nullable|exists:tipos_parceiros,id',
                'situacao_id' => 'nullable|exists:situacoes,id',
                'cnpj' => 'nullable|string|max:18',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'site' => 'nullable|url|max:500',
                'responsavel' => 'nullable|string|max:255',
                'cep' => 'nullable|string|max:9',
                'logradouro' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'observacoes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $parceiro = Parceiro::create([
                'nome' => $request->nome,
                'tipo_parceiro_id' => $request->tipo_parceiro_id,
                'situacao_id' => $request->situacao_id,
                'cnpj' => $request->cnpj,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'site' => $request->site,
                'responsavel' => $request->responsavel,
                'cep' => $request->cep,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'observacoes' => $request->observacoes,
            ]);

            // Carregar o relacionamento com tipoParceiro
            $parceiro->load(['tipoParceiro', 'situacao']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parceiro cadastrado com sucesso',
                'data' => $parceiro
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            report($e);

            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Listar todos os parceiros
     */
    public function index(): JsonResponse
    {
        try {
            $parceiros = Parceiro::with(['tipoParceiro', 'situacao'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Parceiros listados com sucesso',
                'data' => $parceiros
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Buscar parceiro por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $parceiro = Parceiro::with(['tipoParceiro', 'situacao'])->find($id);

            if (!$parceiro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parceiro não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Parceiro encontrado com sucesso',
                'data' => $parceiro
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Atualizar parceiro existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Busca o parceiro
            $parceiro = Parceiro::find($id);

            if (!$parceiro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parceiro não encontrado'
                ], 404);
            }

            // Valida os dados
            $request->validate([
                'nome' => 'required|string|max:255',
                'tipo_parceiro_id' => 'nullable|exists:tipos_parceiros,id',
                'situacao_id' => 'nullable|exists:situacoes,id',
                'cnpj' => 'nullable|string|max:18',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'site' => 'nullable|url|max:500',
                'responsavel' => 'nullable|string|max:255',
                'cep' => 'nullable|string|max:9',
                'logradouro' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'observacoes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Atualiza os dados do parceiro
            $parceiro->update([
                'nome' => $request->nome,
                'tipo_parceiro_id' => $request->tipo_parceiro_id,
                'situacao_id' => $request->situacao_id,
                'cnpj' => $request->cnpj,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'site' => $request->site,
                'responsavel' => $request->responsavel,
                'cep' => $request->cep,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'observacoes' => $request->observacoes,
            ]);

            // Carregar o relacionamento com tipoParceiro
            $parceiro->load(['tipoParceiro', 'situacao']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parceiro atualizado com sucesso',
                'data' => $parceiro
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            report($e);

            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Excluir parceiro (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Busca o parceiro
            $parceiro = Parceiro::find($id);

            if (!$parceiro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parceiro não encontrado'
                ], 404);
            }

            // Verifica se já foi excluído (soft delete)
            if ($parceiro->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parceiro já foi excluído'
                ], 400);
            }

            // Executa soft delete
            $parceiro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parceiro excluído com sucesso'
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Listar tipos de parceiros
     */
    public function tipos(): JsonResponse
    {
        try {
            $tipos = TipoParceiro::all();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de parceiros listados com sucesso',
                'data' => $tipos
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }
}
