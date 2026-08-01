<?php

namespace App\Console\Commands;

use App\Services\ClinicProvisioner;
use Illuminate\Console\Command;
use Throwable;

class ProvisionClinicCommand extends Command
{
    protected $signature = 'clinic:provision
        {slug : Slug único da clínica (ex: demo)}
        {nome : Nome de exibição}
        {--admin-email=admin@clinica.local : E-mail do admin}
        {--admin-password=password : Senha do admin}
        {--admin-name=Administrador : Nome do admin}
        {--cor-primaria=#0676a6 : Cor primária}
        {--cor-secundaria=#D4AF37 : Cor secundária}';

    protected $description = 'Provisiona uma clínica (banco próprio + admin). D11/D13/D15.';

    public function handle(ClinicProvisioner $provisioner): int
    {
        $adminPassword = (string) $this->option('admin-password');
        if (strlen($adminPassword) < 8 || $adminPassword === 'password') {
            $this->error('Use --admin-password com no mínimo 8 caracteres e diferente de "password".');

            return self::FAILURE;
        }

        try {
            $clinic = $provisioner->provision(
                (string) $this->argument('slug'),
                (string) $this->argument('nome'),
                (string) $this->option('admin-email'),
                $adminPassword,
                (string) $this->option('admin-name'),
                (string) $this->option('cor-primaria'),
                (string) $this->option('cor-secundaria'),
            );
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Clínica provisionada com sucesso.');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $clinic->id],
                ['Nome', $clinic->nome],
                ['Slug', $clinic->slug],
                ['Database', $clinic->database_name],
                ['Admin', $this->option('admin-email')],
            ]
        );
        $this->line('Front: defina VITE_CLINIC_SLUG='.$clinic->slug.' (ou digite no login).');

        return self::SUCCESS;
    }
}
