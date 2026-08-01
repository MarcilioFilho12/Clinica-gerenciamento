<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ConfiguracaoAgendamentoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// Público: branding white-label (banco central)
Route::get('clinic/branding', [ClinicController::class, 'branding']);

Route::middleware('clinic')->group(function () {
    Route::post('/auth', [AuthController::class, 'auth']);

    Route::middleware('jwt')->group(function () {
        Route::get('/auth/verify', [AuthController::class, 'verify']);
        Route::put('/auth/senha', [AuthController::class, 'changePassword']);

        // Clínico: admin, recepção e profissional
        Route::middleware('profile:admin,recepcao,profissional')->group(function () {
            Route::get('listar-pacientes', [PacienteController::class, 'listar']);
            Route::get('buscar-paciente/{id}', [PacienteController::class, 'buscar']);
            Route::post('cadastrar-paciente', [PacienteController::class, 'cadastrar']);
            Route::put('atualizar-paciente/{id}', [PacienteController::class, 'atualizar']);
            Route::get('pacientes/{id}/consultas', [ConsultaController::class, 'consultasPorPaciente']);

            Route::post('pacientes/{id}/fichas-clinicas', [PacienteController::class, 'criarFichaClinica']);
            Route::get('pacientes/{id}/fichas-clinicas', [PacienteController::class, 'listarFichasClinicas']);
            Route::get('fichas-clinicas/{id}', [PacienteController::class, 'obterFichaClinica']);
            Route::put('fichas-clinicas/{id}', [PacienteController::class, 'atualizarFichaClinica']);

            Route::get('consultas', [ConsultaController::class, 'index']);
            Route::get('consultas/profissionais', [ConsultaController::class, 'profissionais']);
            Route::get('consultas/agenda-periodo', [ConsultaController::class, 'agendaPeriodo']);
            Route::get('consultas/horarios-disponiveis', [ConsultaController::class, 'horariosDisponiveis']);
            Route::get('consultas/fila-espera', [ConsultaController::class, 'filaEspera']);
            Route::get('consultas/fila-espera/estatisticas', [ConsultaController::class, 'estatisticasFilaEspera']);
            Route::post('consultas/fila-espera/adicionar', [ConsultaController::class, 'adicionarFilaEspera']);
            Route::get('consultas/pacientes-em-atendimento', [ConsultaController::class, 'pacientesEmAtendimento']);
            Route::post('consultas/{id}/confirmar-chegada', [ConsultaController::class, 'confirmarChegada']);
            Route::get('consultas/{id}/detalhes', [ConsultaController::class, 'detalhesConsulta']);
            Route::get('consultas/{id}', [ConsultaController::class, 'show']);
            Route::post('consultas', [ConsultaController::class, 'store']);
            Route::put('consultas/{id}', [ConsultaController::class, 'update']);
            Route::delete('consultas/{id}', [ConsultaController::class, 'destroy']);
            Route::post('consultas/{id}/cancelar', [ConsultaController::class, 'cancelar']);
            Route::post('consultas/{id}/confirmar', [ConsultaController::class, 'confirmar']);
            Route::post('consultas/{id}/finalizar', [ConsultaController::class, 'finalizar']);
            Route::post('consultas/{id}/chamar', [ConsultaController::class, 'chamarPaciente']);
            Route::put('consultas/{id}/encerrar', [ConsultaController::class, 'encerrarConsulta']);

            Route::get('configuracoes-agendamento', [ConfiguracaoAgendamentoController::class, 'index']);
            Route::get('configuracoes-agendamento/{id}', [ConfiguracaoAgendamentoController::class, 'show']);
        });

        // Recepção + admin: parceiros, agenda e financeiro
        Route::middleware('profile:admin,recepcao')->group(function () {
            Route::get('parceiros', [ParceiroController::class, 'index']);
            Route::get('parceiros/{id}', [ParceiroController::class, 'show']);
            Route::post('parceiros', [ParceiroController::class, 'store']);
            Route::put('parceiros/{id}', [ParceiroController::class, 'update']);
            Route::delete('parceiros/{id}', [ParceiroController::class, 'destroy']);
            Route::get('parceiros-tipos', [ParceiroController::class, 'tipos']);

            Route::post('configuracoes-agendamento', [ConfiguracaoAgendamentoController::class, 'store']);
            Route::post('configuracoes-agendamento/confirmar', [ConfiguracaoAgendamentoController::class, 'confirmarCriacao']);
            Route::put('configuracoes-agendamento/{id}', [ConfiguracaoAgendamentoController::class, 'update']);
            Route::delete('configuracoes-agendamento/{id}', [ConfiguracaoAgendamentoController::class, 'destroy']);

            Route::get('financeiro/resumo', [FinanceiroController::class, 'resumo']);
            Route::get('financeiro/relatorio', [FinanceiroController::class, 'relatorio']);
            Route::get('financeiro/despesas', [FinanceiroController::class, 'listarDespesas']);
            Route::post('financeiro/despesas', [FinanceiroController::class, 'criarDespesa']);
            Route::put('financeiro/despesas/{id}', [FinanceiroController::class, 'atualizarDespesa']);
            Route::delete('financeiro/despesas/{id}', [FinanceiroController::class, 'excluirDespesa']);
        });

        // Somente administrador da clínica
        Route::middleware('profile:admin')->group(function () {
            Route::get('usuarios', [UsersController::class, 'index']);
            Route::get('usuarios/{id}', [UsersController::class, 'show']);
            Route::post('usuarios', [UsersController::class, 'store']);
            Route::put('usuarios/{id}', [UsersController::class, 'update']);
            Route::delete('usuarios/{id}', [UsersController::class, 'destroy']);

            Route::put('clinic/branding', [ClinicController::class, 'updateBranding']);
            Route::post('clinic/logo', [ClinicController::class, 'uploadLogo']);
            Route::delete('clinic/logo', [ClinicController::class, 'removeLogo']);
        });
    });
});
