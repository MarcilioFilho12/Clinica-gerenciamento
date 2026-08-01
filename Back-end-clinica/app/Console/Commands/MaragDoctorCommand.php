<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Detecta resíduos da estrutura antiga (pastas aninhadas / junction quebrada)
 * e repara o link público de storage usado por logos white-label.
 */
class MaragDoctorCommand extends Command
{
    protected $signature = 'marag:doctor
        {--fix : Recria public/storage se estiver ausente ou apontando para path inválido}';

    protected $description = 'Verifica paths, storage link e saúde básica do ambiente Marag';

    public function handle(): int
    {
        $ok = true;

        $this->info('Marag doctor — '.base_path());
        $this->newLine();

        if (str_contains(base_path(), 'paulinho-marcilio')) {
            $this->error('CRÍTICO: base_path ainda parece estar na pasta aninhada antiga.');
            $ok = false;
        } else {
            $this->line('✓ base_path fora de paulinho-marcilio-*');
        }

        $legacyHints = [
            base_path('paulinho-marcilio-back-main'),
            dirname(base_path()).DIRECTORY_SEPARATOR.'paulinho-marcilio-back-main',
        ];
        foreach ($legacyHints as $legacy) {
            if (is_dir($legacy)) {
                $this->warn("Resíduo encontrado (pode ignorar se vazio): {$legacy}");
            }
        }

        $target = storage_path('app/public');
        $link = public_path('storage');

        if (! is_dir($target)) {
            File::ensureDirectoryExists($target);
            $this->line("✓ Criado diretório {$target}");
        }

        $linkHealthy = $this->storageLinkIsHealthy($link, $target);

        if ($linkHealthy) {
            $this->line('✓ public/storage → storage/app/public');
        } else {
            $this->error('ALTO: public/storage ausente ou quebrado (causa 404 em /storage/clinic-logos/...)');
            $ok = false;

            if ($this->option('fix') || $this->confirm('Recriar storage:link agora?', true)) {
                $this->repairStorageLink($link);
                if ($this->storageLinkIsHealthy(public_path('storage'), $target)) {
                    $this->info('✓ storage:link reparado');
                    $ok = true;
                } else {
                    $this->error('Falha ao reparar storage:link — rode manualmente: php artisan storage:link --force');
                    $ok = false;
                }
            }
        }

        $appUrl = (string) config('app.url');
        if ($appUrl === '' || str_contains($appUrl, 'example.com')) {
            $this->warn('APP_URL parece inválido: configure no .env (ex.: http://localhost:8000)');
        } else {
            $this->line("✓ APP_URL={$appUrl}");
        }

        $this->newLine();
        $this->line($ok ? 'Ambiente OK para logos e paths flat.' : 'Há problemas — corrija antes de testar upload de logo.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function storageLinkIsHealthy(string $link, string $target): bool
    {
        if (! file_exists($link) && ! is_link($link)) {
            return false;
        }

        $resolvedLink = realpath($link);
        $resolvedTarget = realpath($target);

        if ($resolvedLink && $resolvedTarget) {
            return $resolvedLink === $resolvedTarget;
        }

        // Windows junction: realpath do link às vezes falha se target antigo sumiu
        $read = @readlink($link);
        if (is_string($read) && $read !== '') {
            $normalizedRead = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $read);
            $normalizedTarget = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target);

            return str_ends_with($normalizedRead, 'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public')
                && is_dir($normalizedRead)
                && realpath($normalizedRead) === $resolvedTarget;
        }

        return false;
    }

    private function repairStorageLink(string $link): void
    {
        if (is_link($link) || file_exists($link)) {
            if (PHP_OS_FAMILY === 'Windows') {
                // Junction/directory link no Windows
                @rmdir($link);
                if (file_exists($link) || is_link($link)) {
                    File::deleteDirectory($link);
                }
            } else {
                @unlink($link);
            }
        }

        Artisan::call('storage:link', ['--force' => true]);
        $this->line(trim(Artisan::output()));
    }
}
