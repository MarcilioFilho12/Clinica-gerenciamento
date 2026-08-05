<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Support\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Roda `migrate` no banco de CADA clínica ativa (1 MySQL por clínica — D11).
 *
 * Necessário porque uma migration nova só é aplicada ao banco que estiver
 * configurado em `DB_DATABASE` no momento do `php artisan migrate`. Sem este
 * comando, cada clínica precisaria ser migrada manualmente uma a uma.
 */
class MigrateAllClinicsCommand extends Command
{
    protected $signature = 'clinic:migrate-all {--pretend : Mostra o que seria executado, sem aplicar}';

    protected $description = 'Executa as migrations pendentes no banco de todas as clínicas ativas (central + tenants)';

    public function handle(): int
    {
        $clinicas = Clinic::query()->where('ativo', true)->get();

        if ($clinicas->isEmpty()) {
            $this->warn('Nenhuma clínica ativa encontrada no banco central.');

            return self::SUCCESS;
        }

        $falhas = 0;

        foreach ($clinicas as $clinica) {
            $this->line("==> [{$clinica->slug}] ({$clinica->database_name})");

            try {
                TenantContext::set($clinica);

                $exit = Artisan::call('migrate', [
                    '--database' => 'mysql',
                    '--path' => 'database/migrations',
                    '--force' => true,
                    '--pretend' => (bool) $this->option('pretend'),
                ]);

                $this->line(trim(Artisan::output()));

                if ($exit !== 0) {
                    $falhas++;
                    $this->error("[{$clinica->slug}] migrate retornou código {$exit}.");
                }
            } catch (Throwable $e) {
                report($e);
                $falhas++;
                $this->error("[{$clinica->slug}] Falha: {$e->getMessage()}");
            } finally {
                TenantContext::clear();
            }
        }

        if ($falhas > 0) {
            $this->error("Concluído com {$falhas} falha(s). Verifique os logs antes de considerar o deploy seguro.");

            return self::FAILURE;
        }

        $this->info('Todas as clínicas migradas com sucesso.');

        return self::SUCCESS;
    }
}
