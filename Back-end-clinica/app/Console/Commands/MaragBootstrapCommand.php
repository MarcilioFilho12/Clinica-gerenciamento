<?php

namespace App\Console\Commands;

use App\Services\ClinicProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Prepara MySQL no host (Railway): cria marag_central, migrate central,
 * e opcionalmente provisiona a clínica piloto.
 */
class MaragBootstrapCommand extends Command
{
    protected $signature = 'marag:bootstrap
        {--central-db= : Nome do banco central (default: CENTRAL_DB_DATABASE)}
        {--provision : Após migrate, provisiona clínica piloto}
        {--slug=piloto : Slug da clínica (com --provision)}
        {--nome=Clinica Piloto : Nome (com --provision)}
        {--admin-email=admin@piloto.local : E-mail admin (com --provision)}
        {--admin-password=TroqueEstaSenha9 : Senha admin (com --provision)}
        {--admin-name=Admin : Nome admin (com --provision)}';

    protected $description = 'Cria marag_central se faltar, roda migrations central e opcionalmente provisiona clínica';

    public function handle(ClinicProvisioner $provisioner): int
    {
        $centralDb = (string) ($this->option('central-db') ?: config('database.connections.central.database') ?: 'marag_central');
        $centralDb = preg_replace('/[^a-zA-Z0-9_]/', '', $centralDb) ?: 'marag_central';

        $this->info("Marag bootstrap — criando database `{$centralDb}` se necessário…");

        try {
            $this->ensureDatabaseExists($centralDb);
        } catch (Throwable $e) {
            $this->error('Falha ao criar/conectar no MySQL: '.$e->getMessage());
            $this->line('Confira CENTRAL_DB_HOST / DB_HOST, user e senha no Railway.');

            return self::FAILURE;
        }

        $this->info('Rodando migrations do banco central…');
        $exitCentral = Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
        $this->line(Artisan::output());

        if ($exitCentral !== 0) {
            $this->error('Falha nas migrations central.');

            return self::FAILURE;
        }

        if (! $this->option('provision')) {
            $this->info('OK. Próximo: php artisan marag:bootstrap --provision');
            $this->line('Ou: php artisan clinic:provision …');

            return self::SUCCESS;
        }

        $password = (string) $this->option('admin-password');
        if (strlen($password) < 8 || $password === 'password') {
            $this->error('Use --admin-password com no mínimo 8 caracteres e diferente de "password".');

            return self::FAILURE;
        }

        $slug = (string) $this->option('slug');
        $this->info("Provisionando clínica `{$slug}`…");

        try {
            $clinic = $provisioner->provision(
                $slug,
                (string) $this->option('nome'),
                (string) $this->option('admin-email'),
                $password,
                (string) $this->option('admin-name'),
            );
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Já existe clínica')) {
                $this->warn($msg);
                $this->info('Clínica já provisionada — branding/login devem funcionar.');

                return self::SUCCESS;
            }
            $this->error($msg);

            return self::FAILURE;
        }

        $this->info('Clínica provisionada.');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Slug', $clinic->slug],
                ['Database', $clinic->database_name],
                ['Admin', $this->option('admin-email')],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Conecta no servidor MySQL sem exigir o database da app e cria o schema.
     */
    private function ensureDatabaseExists(string $databaseName): void
    {
        $base = config('database.connections.central');
        $server = array_merge($base, [
            // Schema do sistema — não depende de marag_central existir
            'database' => 'mysql',
        ]);

        Config::set('database.connections.marag_server', $server);
        DB::purge('marag_server');

        DB::connection('marag_server')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        DB::purge('central');
        DB::reconnect('central');

        $this->line("✓ Database `{$databaseName}` disponível");
    }
}
