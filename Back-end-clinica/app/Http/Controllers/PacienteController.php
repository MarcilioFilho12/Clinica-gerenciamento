<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cadastro;
use App\Models\CadastroFichaClinica;
use App\Models\Consulta;
use App\Models\Anamnese;
use App\Models\AcuidadeVisual;
use App\Models\Refracao;
use App\Models\Biomicroscopia;
use App\Models\Prescricao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{

    public function cadastrar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|max:14',
                'data_nascimento' => 'nullable|date',
                'contato' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'sexo' => 'nullable|in:M,F,Outro',
                'ocupacao' => 'nullable|string|max:255',
                'rg' => 'nullable|string|max:20',
                'nome_responsavel' => 'nullable|string|max:255',
                'cpf_responsavel' => 'nullable|string|max:14',
                'observacoes' => 'nullable|string',
                'endereco' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $cadastro = Cadastro::create([
                'nome' => $request->nome,
                'data_nascimento' => $request->data_nascimento ?: null,
                'sexo' => $request->sexo,
                'contato' => $request->contato ?: null,
                'email' => $request->email,
                'ocupacao' => $request->ocupacao,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'nome_responsavel' => $request->nome_responsavel,
                'cpf_responsavel' => $request->cpf_responsavel,
                'observacoes' => $request->observacoes,
                'endereco' => $request->endereco,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente cadastrado com sucesso',
                'data' => $cadastro
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

    public function criarFichaClinica(Request $request, int $cadastroId): JsonResponse
    {
        try {
            // Verificar se o paciente existe
            $cadastro = Cadastro::find($cadastroId);
            if (!$cadastro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ], 404);
            }

            // Validar dados obrigatórios
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'data_consulta' => 'required|date',
                'observacoes' => 'nullable|string',
                'consulta_id' => 'nullable|exists:consultas,id',
                // Dados opcionais
                'anamnese' => 'nullable|array',
                'acuidades_visuais' => 'nullable|array',
                'refracoes' => 'nullable|array',
                'biomicroscopias' => 'nullable|array',
                'prescricao' => 'nullable|array'
            ]);

            DB::beginTransaction();

            // Verificar e processar consulta se consulta_id for fornecido
            $consultaEncerrada = false;
            $consulta = null;

            if ($request->consulta_id) {
                // Validação: Verificar se a consulta existe
                $consulta = Consulta::find($request->consulta_id);

                if (!$consulta) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Consulta não encontrada'
                    ], 404);
                }

                // Validação: Verificar se a consulta pertence ao paciente correto
                if ($consulta->paciente_id != $cadastroId) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'A consulta não pertence a este paciente'
                    ], 422);
                }

                // Validação: Verificar se a consulta não está cancelada
                if ($consulta->situacao_id == 5) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Não é possível criar ficha clínica para uma consulta cancelada'
                    ], 422);
                }

                // Validação: Verificar se a consulta está em atendimento (situacao_id = 6)
                // Se estiver, encerrar automaticamente (situacao_id = 4)
                if ($consulta->situacao_id == 6) {
                    $consulta->update(['situacao_id' => 4]);
                    $consultaEncerrada = true;
                }
                // Se já estiver encerrada (situacao_id = 4) ou em outro status, apenas criar a ficha sem alterar status
            }

            // Criar ficha clínica
            $fichaClinica = CadastroFichaClinica::create([
                'cadastro_id' => $cadastroId,
                'user_id' => $request->user_id,
                'consulta_id' => $request->consulta_id ?? null,
                'data_consulta' => $request->data_consulta,
                'observacoes' => $request->observacoes ?? null,
            ]);

            // Criar anamnese se fornecida
            if ($request->anamnese) {
                $fichaClinica->anamnese()->create([
                    'motivo_consulta' => $request->anamnese['motivo_consulta'] ?? null,
                    'ultimo_controle' => $request->anamnese['ultimo_controle'] ?? null,
                    'antecedentes_pessoais' => $request->anamnese['antecedentes_pessoais'] ?? null,
                    'antecedentes_familiares' => $request->anamnese['antecedentes_familiares'] ?? null,
                ]);
            }

            // Criar acuidades visuais se fornecidas
            if ($request->acuidades_visuais) {
                foreach ($request->acuidades_visuais as $acuidade) {
                    $fichaClinica->acuidadesVisuais()->create([
                        'olho' => $acuidade['olho'],
                        'vl' => $acuidade['vl'] ?? null,
                        'vp' => $acuidade['vp'] ?? null,
                        'ph' => $acuidade['ph'] ?? null,
                        'observacoes' => $acuidade['observacoes'] ?? null,
                    ]);
                }
            }

            // Criar refrações se fornecidas
            if ($request->refracoes) {
                foreach ($request->refracoes as $refracao) {
                    $fichaClinica->refracoes()->create([
                        'tipo' => $refracao['tipo'],
                        'olho' => $refracao['olho'],
                        'esf' => $refracao['esf'] ?? null,
                        'cil' => $refracao['cil'] ?? null,
                        'eixo' => $refracao['eixo'] ?? null,
                        'add' => $refracao['add'] ?? null,
                        'av' => $refracao['av'] ?? null,
                    ]);
                }
            }

            // Criar biomicroscopias se fornecidas
            if ($request->biomicroscopias) {
                foreach ($request->biomicroscopias as $biomicroscopia) {
                    $fichaClinica->biomicroscopias()->create([
                        'olho' => $biomicroscopia['olho'],
                        'cornea' => $biomicroscopia['cornea'] ?? null,
                        'iris' => $biomicroscopia['iris'] ?? null,
                        'conjuntiva' => $biomicroscopia['conjuntiva'] ?? null,
                        'cristalino' => $biomicroscopia['cristalino'] ?? null,
                        'pupilas' => $biomicroscopia['pupilas'] ?? null,
                    ]);
                }
            }

            // Criar prescrição se fornecida
            if ($request->prescricao) {
                $fichaClinica->prescricao()->create([
                    'material' => $request->prescricao['material'] ?? null,
                    'tipo_lente' => $request->prescricao['tipo_lente'] ?? null,
                    'filtro' => $request->prescricao['filtro'] ?? null,
                    'diagnostico' => $request->prescricao['diagnostico'] ?? null,
                    'conduta' => $request->prescricao['conduta'] ?? null,
                    'encaminhamento' => $request->prescricao['encaminhamento'] ?? null,
                    'proximo_controle' => $request->prescricao['proximo_controle'] ?? null,
                ]);
            }

            DB::commit();

            // Carregar relacionamentos
            $fichaClinica->load([
                'cadastro',
                'user',
                'consulta',
                'anamnese',
                'acuidadesVisuais',
                'refracoes',
                'biomicroscopias',
                'prescricao'
            ]);

            // Mensagem de sucesso
            $mensagem = 'Ficha clínica criada com sucesso';
            if ($consultaEncerrada) {
                $mensagem = 'Ficha clínica criada com sucesso e consulta encerrada automaticamente';
            }

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'data' => $fichaClinica,
                'consulta_encerrada' => $consultaEncerrada
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

    public function listarFichasClinicas(int $cadastroId): JsonResponse
    {
        try {
            // Verificar se o paciente existe
            $cadastro = Cadastro::find($cadastroId);
            if (!$cadastro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ], 404);
            }

            // Buscar todas as fichas clínicas do paciente com relacionamentos
            $fichasClinicas = CadastroFichaClinica::where('cadastro_id', $cadastroId)
                ->with([
                    'user:id,name,especialidade,crm',
                    'anamnese',
                    'acuidadesVisuais',
                    'refracoes',
                    'biomicroscopias',
                    'prescricao'
                ])
                ->orderBy('data_consulta', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Fichas clínicas listadas com sucesso',
                'data' => $fichasClinicas
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    public function obterFichaClinica(int $fichaClinicaId): JsonResponse
    {
        try {
            // Buscar ficha clínica com todos os relacionamentos
            $fichaClinica = CadastroFichaClinica::with([
                'cadastro',
                'user:id,name,especialidade,crm',
                'anamnese',
                'acuidadesVisuais',
                'refracoes',
                'biomicroscopias',
                'prescricao'
            ])->find($fichaClinicaId);

            if (!$fichaClinica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ficha clínica não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha clínica encontrada com sucesso',
                'data' => $fichaClinica
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    public function atualizarFichaClinica(Request $request, int $fichaClinicaId): JsonResponse
    {
        try {
            // Buscar ficha clínica
            $fichaClinica = CadastroFichaClinica::find($fichaClinicaId);
            if (!$fichaClinica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ficha clínica não encontrada'
                ], 404);
            }

            // Validar dados
            $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'data_consulta' => 'sometimes|required|date',
                'observacoes' => 'nullable|string',
                // Dados opcionais
                'anamnese' => 'nullable|array',
                'acuidades_visuais' => 'nullable|array',
                'refracoes' => 'nullable|array',
                'biomicroscopias' => 'nullable|array',
                'prescricao' => 'nullable|array'
            ]);

            DB::beginTransaction();

            // Atualizar ficha clínica
            $fichaClinica->update([
                'user_id' => $request->user_id ?? $fichaClinica->user_id,
                'data_consulta' => $request->data_consulta ?? $fichaClinica->data_consulta,
                'observacoes' => $request->has('observacoes') ? $request->observacoes : $fichaClinica->observacoes,
            ]);

            // Atualizar ou criar anamnese
            if ($request->anamnese) {
                $fichaClinica->anamnese()->updateOrCreate(
                    ['ficha_clinica_id' => $fichaClinica->id],
                    [
                        'motivo_consulta' => $request->anamnese['motivo_consulta'] ?? null,
                        'ultimo_controle' => $request->anamnese['ultimo_controle'] ?? null,
                        'antecedentes_pessoais' => $request->anamnese['antecedentes_pessoais'] ?? null,
                        'antecedentes_familiares' => $request->anamnese['antecedentes_familiares'] ?? null,
                    ]
                );
            }

            // Atualizar acuidades visuais - deletar as antigas e criar novas
            if ($request->acuidades_visuais) {
                $fichaClinica->acuidadesVisuais()->delete();
                foreach ($request->acuidades_visuais as $acuidade) {
                    $fichaClinica->acuidadesVisuais()->create([
                        'olho' => $acuidade['olho'],
                        'vl' => $acuidade['vl'] ?? null,
                        'vp' => $acuidade['vp'] ?? null,
                        'ph' => $acuidade['ph'] ?? null,
                        'observacoes' => $acuidade['observacoes'] ?? null,
                    ]);
                }
            }

            // Atualizar refrações - deletar as antigas e criar novas
            if ($request->refracoes) {
                $fichaClinica->refracoes()->delete();
                foreach ($request->refracoes as $refracao) {
                    $fichaClinica->refracoes()->create([
                        'tipo' => $refracao['tipo'],
                        'olho' => $refracao['olho'],
                        'esf' => $refracao['esf'] ?? null,
                        'cil' => $refracao['cil'] ?? null,
                        'eixo' => $refracao['eixo'] ?? null,
                        'add' => $refracao['add'] ?? null,
                        'av' => $refracao['av'] ?? null,
                    ]);
                }
            }

            // Atualizar biomicroscopias - deletar as antigas e criar novas
            if ($request->biomicroscopias) {
                $fichaClinica->biomicroscopias()->delete();
                foreach ($request->biomicroscopias as $biomicroscopia) {
                    $fichaClinica->biomicroscopias()->create([
                        'olho' => $biomicroscopia['olho'],
                        'cornea' => $biomicroscopia['cornea'] ?? null,
                        'iris' => $biomicroscopia['iris'] ?? null,
                        'conjuntiva' => $biomicroscopia['conjuntiva'] ?? null,
                        'cristalino' => $biomicroscopia['cristalino'] ?? null,
                        'pupilas' => $biomicroscopia['pupilas'] ?? null,
                    ]);
                }
            }

            // Atualizar ou criar prescrição
            if ($request->prescricao) {
                $fichaClinica->prescricao()->updateOrCreate(
                    ['ficha_clinica_id' => $fichaClinica->id],
                    [
                        'material' => $request->prescricao['material'] ?? null,
                        'tipo_lente' => $request->prescricao['tipo_lente'] ?? null,
                        'filtro' => $request->prescricao['filtro'] ?? null,
                        'diagnostico' => $request->prescricao['diagnostico'] ?? null,
                        'conduta' => $request->prescricao['conduta'] ?? null,
                        'encaminhamento' => $request->prescricao['encaminhamento'] ?? null,
                        'proximo_controle' => $request->prescricao['proximo_controle'] ?? null,
                    ]
                );
            }

            DB::commit();

            // Recarregar relacionamentos
            $fichaClinica->load([
                'cadastro',
                'user:id,name,especialidade,crm',
                'anamnese',
                'acuidadesVisuais',
                'refracoes',
                'biomicroscopias',
                'prescricao'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ficha clínica atualizada com sucesso',
                'data' => $fichaClinica
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

    public function listar(Request $request): JsonResponse
    {
        $query = Cadastro::query()->orderBy('nome');

        $searchTerm = trim((string) $request->input('search', ''));
        if ($searchTerm !== '') {
            $digits = preg_replace('/\D+/', '', $searchTerm) ?: '';

            $query->where(function ($q) use ($searchTerm, $digits) {
                $q->where('nome', 'LIKE', "%{$searchTerm}%");

                if (strlen($digits) >= 3) {
                    $q->orWhere('cpf', 'LIKE', "%{$digits}%")
                        ->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?",
                            ["%{$digits}%"]
                        );
                }

                if (strlen($searchTerm) >= 3) {
                    $q->orWhere('contato', 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        $limit = min(50, max(1, (int) $request->input('limit', 20)));
        $pacientes = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Pacientes listados com sucesso',
            'data' => $pacientes,
        ]);
    }

    public function buscar(int $id): JsonResponse
    {
        try {
            // Buscar apenas dados básicos do paciente (sem dados clínicos)
            $paciente = Cadastro::find($id);

            if (!$paciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Paciente encontrado com sucesso',
                'data' => $paciente
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    public function atualizar(Request $request, int $id): JsonResponse
    {
        try {
            $paciente = Cadastro::find($id);

            if (!$paciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ], 404);
            }

            $request->validate([
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|max:14',
                'data_nascimento' => 'nullable|date',
                'contato' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'sexo' => 'nullable|in:M,F,Outro',
                'ocupacao' => 'nullable|string|max:255',
                'rg' => 'nullable|string|max:20',
                'nome_responsavel' => 'nullable|string|max:255',
                'cpf_responsavel' => 'nullable|string|max:14',
                'observacoes' => 'nullable|string',
                'endereco' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            // Atualizar apenas dados básicos do cadastro (sem dados clínicos)
            $paciente->update([
                'nome' => $request->nome,
                'data_nascimento' => $request->data_nascimento ?: null,
                'sexo' => $request->sexo,
                'contato' => $request->contato ?: null,
                'email' => $request->email,
                'ocupacao' => $request->ocupacao,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'nome_responsavel' => $request->nome_responsavel,
                'cpf_responsavel' => $request->cpf_responsavel,
                'observacoes' => $request->observacoes,
                'endereco' => $request->endereco,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente atualizado com sucesso',
                'data' => $paciente
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
}
