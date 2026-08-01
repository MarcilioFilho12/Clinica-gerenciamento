<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ClinicProvisioner
{
    /**
     * Cria registro central + banco da clínica + migrations + admin (D13/D15).
     */
    public function provision(
        string $slug,
        string $nome,
        string $adminEmail,
        string $adminPassword,
        string $adminName = 'Administrador',
        ?string $corPrimaria = null,
        ?string $corSecundaria = null,
    ): Clinic {
        $slug = strtolower(Str::slug($slug));
        if ($slug === '' || strlen($slug) < 2) {
            throw new InvalidArgumentException('Slug inválido.');
        }

        if (Clinic::query()->where('slug', $slug)->exists()) {
            throw new InvalidArgumentException("Já existe clínica com slug '{$slug}'.");
        }

        $databaseName = 'marag_clinic_'.str_replace('-', '_', $slug);

        if (Clinic::query()->where('database_name', $databaseName)->exists()) {
            throw new InvalidArgumentException("Database '{$databaseName}' já registrado.");
        }

        $this->createDatabase($databaseName);

        $clinic = Clinic::query()->create([
            'nome' => $nome,
            'slug' => $slug,
            'database_name' => $databaseName,
            'logo_url' => null,
            'cor_primaria' => $corPrimaria ?: '#0676a6',
            'cor_secundaria' => $corSecundaria ?: '#D4AF37',
            'ativo' => true,
        ]);

        TenantContext::set($clinic);

        $exit = Artisan::call('migrate', [
            '--database' => 'mysql',
            '--force' => true,
            '--path' => 'database/migrations',
        ]);

        if ($exit !== 0) {
            throw new RuntimeException('Falha ao rodar migrations da clínica: '.Artisan::output());
        }

        User::query()->create([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => $adminPassword,
            'profile_id' => 1,
            'situacao_id' => 1,
        ]);

        return $clinic;
    }

    private function createDatabase(string $databaseName): void
    {
        $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $databaseName);
        if ($safe !== $databaseName || $safe === '') {
            throw new InvalidArgumentException('Nome de database inválido.');
        }

        DB::connection('central')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }
}
