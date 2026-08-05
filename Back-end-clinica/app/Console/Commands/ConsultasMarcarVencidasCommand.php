<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\ConsultaStatusService;
use App\Support\TenantContext;
use Illuminate\Console\Command;
use Throwable;

/**
 * Marca automaticamente como VENCIDA toda consulta PENDENTE cuja data/hora já passou
 * e que não teve atendimento iniciado. Roda a cada 5 minutos via Scheduler (Kernel::schedule).
 *
 * Multi-tenant: percorre todas as clínicas ativas (banco central) e troca a conexão
 * `mysql` para o banco de cada uma antes de processar — cada clínica tem seu próprio
 * MySQL (D11), então este comando precisa iterar explicitamente.
 */
class ConsultasMarcarVencidasCommand extends Command
{
    protected $signature = 'consultas:marcar-vencidas';

    protected $description = 'Marca consultas PENDENTE vencidas (data/hora passada, sem atendimento) em todas as clínicas ativas';

    public function handle(ConsultaStatusService $service): int
    {
        $clinicas = Clinic::query()->where('ativo', true)->get();

        if ($clinicas->isEmpty()) {
            $this->warn('Nenhuma clínica ativa encontrada no banco central.');

            return self::SUCCESS;
        }

        $totalGeral = 0;

        foreach ($clinicas as $clinica) {
            try {
                TenantContext::set($clinica);
                $marcadas = $service->marcarVencidasAutomaticamente();
                $totalGeral += $marcadas;

                if ($marcadas > 0) {
                    $this->info("[{$clinica->slug}] {$marcadas} consulta(s) marcada(s) como VENCIDA.");
                }
            } catch (Throwable $e) {
                report($e);
                $this->error("[{$clinica->slug}] Falha ao processar: {$e->getMessage()}");
            } finally {
                TenantContext::clear();
            }
        }

        $this->info("Concluído. Total marcado: {$totalGeral}.");

        return self::SUCCESS;
    }
}
