<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos aditivos para o ciclo de vida completo da consulta (status, transferência,
 * reagendamento, no-show). Não remove nem renomeia nenhuma coluna existente —
 * `situacao_id` continua funcionando exatamente como antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            // Fonte de verdade nova do ciclo de vida (situacao_id é sincronizado a partir daqui).
            $table->string('status', 20)->default('PENDENTE')->after('situacao_id');

            // Transferência: consulta nova referencia a origem; nunca duplica, encadeia.
            $table->foreignId('consulta_origem_id')->nullable()->after('status')
                ->constrained('consultas')->nullOnDelete();
            $table->foreignId('profissional_anterior_id')->nullable()->after('consulta_origem_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('profissional_novo_id')->nullable()->after('profissional_anterior_id')
                ->constrained('users')->nullOnDelete();
            $table->text('motivo_transferencia')->nullable()->after('profissional_novo_id');

            // Reagendamento: snapshot do horário anterior (mesma linha, sem duplicar).
            $table->date('data_anterior')->nullable()->after('motivo_transferencia');
            $table->time('horario_anterior_inicio')->nullable()->after('data_anterior');
            $table->time('horario_anterior_fim')->nullable()->after('horario_anterior_inicio');
            $table->text('motivo_reagendamento')->nullable()->after('horario_anterior_fim');

            // Cancelamento: quem/quando (o motivo já existia em `motivo_cancelamento`).
            $table->foreignId('cancelado_por_id')->nullable()->after('motivo_reagendamento')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('cancelado_em')->nullable()->after('cancelado_por_id');

            // No-show.
            $table->timestamp('no_show_em')->nullable()->after('cancelado_em');

            $table->index(['status', 'data'], 'consultas_status_data_idx');
        });

        // CHECK constraint defensiva (MySQL 8.0.16+). Só se aplica em MySQL — em
        // testes (SQLite in-memory) esta sintaxe de ALTER simplesmente não existe.
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            try {
                \Illuminate\Support\Facades\DB::statement(
                    "ALTER TABLE consultas ADD CONSTRAINT chk_consultas_status CHECK (status IN ('PENDENTE','CONFIRMADA','CHEGOU','EM_ATENDIMENTO','REALIZADA','CANCELADA','TRANSFERIDA','REAGENDADA','NO_SHOW','VENCIDA'))"
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE consultas DROP CONSTRAINT chk_consultas_status');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex('consultas_status_data_idx');
            $table->dropConstrainedForeignId('cancelado_por_id');
            $table->dropConstrainedForeignId('profissional_novo_id');
            $table->dropConstrainedForeignId('profissional_anterior_id');
            $table->dropConstrainedForeignId('consulta_origem_id');
            $table->dropColumn([
                'status', 'motivo_transferencia',
                'data_anterior', 'horario_anterior_inicio', 'horario_anterior_fim', 'motivo_reagendamento',
                'cancelado_em', 'no_show_em',
            ]);
        });
    }
};
