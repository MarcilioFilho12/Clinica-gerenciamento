<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\ConsultaStatusService;
use App\Support\TenantContext;
use Illuminate\Console\Command;
use Throwable;

/**
 * Marca automaticamente como NO_SHOW toda consulta CONFIRMADA sem chegada registrada
 * após a tolerância configurada (config('consultas.no_show_tolerancia_minutos')).
 * Roda a cada 5 minutos via Scheduler, iterando todas as clínicas ativas (1 MySQL por clínica).
 */
class ConsultasMarcarNoShowCommand extends Command
{
    protected $signature = 'consultas:marcar-no-show {--tolerancia= : Minutos de tolerância (default: config consultas.no_show_tolerancia_minutos)}';

    protected $description = 'Marca consultas CONFIRMADA sem chegada como NO_SHOW após a tolerância, em todas as clínicas ativas';

    public function handle(ConsultaStatusService $service): int
    {
        $tolerancia = (int) ($this->option('tolerancia') ?? config('consultas.no_show_tolerancia_minutos', 30));

        $clinicas = Clinic::query()->where('ativo', true)->get();

        if ($clinicas->isEmpty()) {
            $this->warn('Nenhuma clínica ativa encontrada no banco central.');

            return self::SUCCESS;
        }

        $totalGeral = 0;

        foreach ($clinicas as $clinica) {
            try {
                TenantContext::set($clinica);
                $marcadas = $service->marcarNoShowAutomaticamente($tolerancia);
                $totalGeral += $marcadas;

                if ($marcadas > 0) {
                    $this->info("[{$clinica->slug}] {$marcadas} consulta(s) marcada(s) como NO_SHOW.");
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
