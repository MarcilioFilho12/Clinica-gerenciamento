<?php

namespace Tests\Feature;

use App\Models\Cadastro;
use App\Models\Consulta;
use App\Models\ConsultaHistorico;
use App\Models\User;
use App\Services\ConsultaStatusService;
use App\Support\ConsultaStatus;
use App\Support\Profiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Cobre a Regra Principal do módulo de Consultas Vencidas / gestão de status:
 * nunca duplicar a consulta (exceto transferência, que encadeia de propósito),
 * sempre gravar histórico, sempre sincronizar o `situacao_id` legado.
 *
 * Testado direto no Service (sem passar pelo middleware `clinic`), pois o
 * tenant-switch (TenantContext) troca a conexão para MySQL real e escaparia
 * do sqlite in-memory usado pelos testes — fora do escopo desta suíte.
 */
class ConsultaStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConsultaStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConsultaStatusService();
    }

    private function criarProfissional(): User
    {
        return User::create([
            'name' => 'Dr. Teste',
            'email' => 'dr.'.uniqid().'@example.test',
            'password' => 'senha-teste-12345',
            'profile_id' => Profiles::PROFISSIONAL,
            'situacao_id' => 1,
        ]);
    }

    private function criarPaciente(): Cadastro
    {
        return Cadastro::create([
            'nome' => 'Paciente Teste',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'F',
            'contato' => '11999999999',
            'cpf' => (string) random_int(10000000000, 99999999999),
        ]);
    }

    private function criarConsulta(User $prof, Cadastro $paciente, array $overrides = []): Consulta
    {
        return Consulta::create(array_merge([
            'user_id' => $prof->id,
            'paciente_id' => $paciente->id,
            'procedimento' => 'Consulta de rotina',
            'data' => now()->addDay()->format('Y-m-d'),
            'horario_inicio' => '10:00',
            'horario_fim' => '10:30',
            'prioridade' => 'normal',
            'situacao_id' => 1,
            'status' => ConsultaStatus::PENDENTE,
        ], $overrides));
    }

    public function test_fluxo_completo_confirma_chega_atende_finaliza(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();
        $consulta = $this->criarConsulta($prof, $paciente);

        $consulta = $this->service->transicionar($consulta, ConsultaStatus::CONFIRMADA, acao: 'confirmar');
        $this->assertSame(ConsultaStatus::CONFIRMADA, $consulta->status);
        $this->assertSame(1, $consulta->situacao_id); // legado inalterado (ainda "ativo")

        $consulta = $this->service->transicionar($consulta, ConsultaStatus::CHEGOU, acao: 'confirmar_chegada');
        $this->assertSame(ConsultaStatus::CHEGOU, $consulta->status);
        $this->assertSame(1, $consulta->situacao_id);

        $consulta = $this->service->transicionar($consulta, ConsultaStatus::EM_ATENDIMENTO, acao: 'chamar');
        $this->assertSame(ConsultaStatus::EM_ATENDIMENTO, $consulta->status);
        $this->assertSame(6, $consulta->situacao_id); // legado: em_atendimento

        $consulta = $this->service->transicionar($consulta, ConsultaStatus::REALIZADA, acao: 'finalizar');
        $this->assertSame(ConsultaStatus::REALIZADA, $consulta->status);
        $this->assertSame(4, $consulta->situacao_id); // legado: encerrado

        // 1 entrada de criação (ConsultaObserver) + 4 transições = 5 linhas de histórico
        // (nada é perdido/sobrescrito — auditoria completa do ciclo de vida).
        $this->assertSame(5, ConsultaHistorico::where('consulta_id', $consulta->id)->count());
        $this->assertSame(
            ['criar', 'confirmar', 'confirmar_chegada', 'chamar', 'finalizar'],
            ConsultaHistorico::where('consulta_id', $consulta->id)->orderBy('id')->pluck('acao')->all()
        );
    }

    public function test_transicao_invalida_e_bloqueada_e_nao_grava_historico_extra(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();
        $consulta = $this->criarConsulta($prof, $paciente, ['status' => ConsultaStatus::REALIZADA, 'situacao_id' => 4]);

        $totalAntes = ConsultaHistorico::count();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/TRANSICAO_INVALIDA/');

        try {
            $this->service->transicionar($consulta, ConsultaStatus::PENDENTE);
        } finally {
            $this->assertSame($totalAntes, ConsultaHistorico::count(), 'Transição inválida não deve gravar histórico.');
        }
    }

    public function test_cancelar_grava_motivo_e_nao_apaga_a_consulta(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();
        $consulta = $this->criarConsulta($prof, $paciente);

        $consulta = $this->service->transicionar(
            $consulta,
            ConsultaStatus::CANCELADA,
            acao: 'cancelar',
            motivo: 'Paciente desistiu',
            extra: ['motivo_cancelamento' => 'Paciente desistiu', 'cancelado_em' => now()],
        );

        $this->assertSame(ConsultaStatus::CANCELADA, $consulta->status);
        $this->assertSame(5, $consulta->situacao_id);
        $this->assertSame('Paciente desistiu', $consulta->motivo_cancelamento);
        $this->assertNotNull($consulta->cancelado_em);
        $this->assertDatabaseHas('consultas', ['id' => $consulta->id]); // nunca apaga
    }

    public function test_reagendar_nao_duplica_a_consulta(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();
        $consulta = $this->criarConsulta($prof, $paciente, [
            'data' => '2026-09-01',
            'horario_inicio' => '10:00',
            'horario_fim' => '10:30',
        ]);

        $totalAntes = Consulta::count();

        $reagendada = $this->service->reagendar($consulta, '2026-09-10', '14:00', '14:30', 'Pedido do paciente');

        $this->assertSame($totalAntes, Consulta::count(), 'Reagendar não pode criar nova linha.');
        $this->assertSame($consulta->id, $reagendada->id);
        $this->assertSame(ConsultaStatus::PENDENTE, $reagendada->status);
        $this->assertSame('2026-09-10', $reagendada->data->format('Y-m-d'));
        $this->assertSame('2026-09-01', $reagendada->data_anterior->format('Y-m-d'));
        $this->assertSame('Pedido do paciente', $reagendada->motivo_reagendamento);

        // 1 entrada de criação (PENDENTE) + REAGENDADA -> PENDENTE: rastro completo, nada perdido.
        $statusNovos = ConsultaHistorico::where('consulta_id', $consulta->id)->orderBy('id')->pluck('status_novo')->all();
        $this->assertSame([ConsultaStatus::PENDENTE, ConsultaStatus::REAGENDADA, ConsultaStatus::PENDENTE], $statusNovos);
    }

    public function test_transferir_cria_nova_consulta_encadeada_e_fecha_a_origem(): void
    {
        $prof1 = $this->criarProfissional();
        $prof2 = $this->criarProfissional();
        $paciente = $this->criarPaciente();
        $origem = $this->criarConsulta($prof1, $paciente);

        $totalAntes = Consulta::count();

        $nova = $this->service->transferir($origem, $prof2->id, '2026-09-15', '09:00', '09:30', 'Troca de profissional');

        $this->assertSame($totalAntes + 1, Consulta::count(), 'Transferência cria exatamente 1 nova linha.');
        $this->assertSame(ConsultaStatus::TRANSFERIDA, $origem->fresh()->status);
        $this->assertSame(5, $origem->fresh()->situacao_id);
        $this->assertSame(ConsultaStatus::PENDENTE, $nova->status);
        $this->assertSame($origem->id, $nova->consulta_origem_id);
        $this->assertSame($prof2->id, $nova->user_id);
        $this->assertSame($paciente->id, $nova->paciente_id);
    }

    public function test_marcar_vencidas_automaticamente_so_afeta_pendentes_com_data_passada(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();

        $vencida = $this->criarConsulta($prof, $paciente, [
            'data' => now()->subDay()->format('Y-m-d'),
            'horario_inicio' => '08:00',
            'horario_fim' => '08:30',
        ]);
        $futura = $this->criarConsulta($prof, $paciente, [
            'data' => now()->addDays(5)->format('Y-m-d'),
            'horario_inicio' => '08:00',
            'horario_fim' => '08:30',
        ]);
        $jaConfirmada = $this->criarConsulta($prof, $paciente, [
            'data' => now()->subDay()->format('Y-m-d'),
            'horario_inicio' => '08:00',
            'horario_fim' => '08:30',
            'status' => ConsultaStatus::CONFIRMADA,
        ]);

        $marcadas = $this->service->marcarVencidasAutomaticamente();

        $this->assertSame(1, $marcadas);
        $this->assertSame(ConsultaStatus::VENCIDA, $vencida->fresh()->status);
        $this->assertSame(ConsultaStatus::PENDENTE, $futura->fresh()->status);
        $this->assertSame(ConsultaStatus::CONFIRMADA, $jaConfirmada->fresh()->status);

        $historico = ConsultaHistorico::where('consulta_id', $vencida->id)->latest('id')->first();
        $this->assertSame('job_vencidas', $historico->acao);
    }

    public function test_marcar_no_show_respeita_tolerancia_e_ignora_quem_ja_chegou(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();

        $foraDoPrazo = $this->criarConsulta($prof, $paciente, [
            'data' => now()->format('Y-m-d'),
            'horario_inicio' => now()->subMinutes(45)->format('H:i'),
            'horario_fim' => now()->subMinutes(15)->format('H:i'),
            'status' => ConsultaStatus::CONFIRMADA,
        ]);
        $dentroDoPrazo = $this->criarConsulta($prof, $paciente, [
            'data' => now()->format('Y-m-d'),
            'horario_inicio' => now()->subMinutes(10)->format('H:i'),
            'horario_fim' => now()->addMinutes(20)->format('H:i'),
            'status' => ConsultaStatus::CONFIRMADA,
        ]);
        $jaChegou = $this->criarConsulta($prof, $paciente, [
            'data' => now()->format('Y-m-d'),
            'horario_inicio' => now()->subMinutes(45)->format('H:i'),
            'horario_fim' => now()->subMinutes(15)->format('H:i'),
            'status' => ConsultaStatus::CONFIRMADA,
            'chegada_em' => now()->subMinutes(40),
        ]);

        $marcadas = $this->service->marcarNoShowAutomaticamente(30);

        $this->assertSame(1, $marcadas);
        $this->assertSame(ConsultaStatus::NO_SHOW, $foraDoPrazo->fresh()->status);
        $this->assertSame(ConsultaStatus::CONFIRMADA, $dentroDoPrazo->fresh()->status);
        $this->assertSame(ConsultaStatus::CONFIRMADA, $jaChegou->fresh()->status);
    }

    public function test_scope_vencidas_retorna_apenas_pendente_ou_vencida_em_atraso(): void
    {
        $prof = $this->criarProfissional();
        $paciente = $this->criarPaciente();

        $pendenteAtrasada = $this->criarConsulta($prof, $paciente, ['data' => now()->subDays(2)->format('Y-m-d')]);
        $vencidaJaMarcada = $this->criarConsulta($prof, $paciente, [
            'data' => now()->subDays(5)->format('Y-m-d'),
            'status' => ConsultaStatus::VENCIDA,
        ]);
        $this->criarConsulta($prof, $paciente, ['data' => now()->addDays(3)->format('Y-m-d')]); // futura, não entra
        $this->criarConsulta($prof, $paciente, [
            'data' => now()->subDays(2)->format('Y-m-d'),
            'status' => ConsultaStatus::REALIZADA,
            'situacao_id' => 4,
        ]); // já atendida, não entra

        $ids = Consulta::vencidas()->pluck('id')->sort()->values()->all();

        $this->assertSame(
            collect([$pendenteAtrasada->id, $vencidaJaMarcada->id])->sort()->values()->all(),
            $ids
        );
    }
}
