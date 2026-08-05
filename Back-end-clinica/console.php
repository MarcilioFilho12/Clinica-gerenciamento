<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * Este é o arquivo de "commands" carregado por bootstrap/app.php
 * (Application::configure()->withRouting(commands: __DIR__.'/../console.php')).
 *
 * Achado durante a implementação do módulo de Consultas Vencidas: este arquivo
 * nunca existiu, então `App\Console\Kernel::schedule()` (Laravel <11) e
 * `routes/console.php` nunca eram carregados — nenhuma tarefa agendada rodava,
 * mesmo definida. Correção mínima e aditiva: registrar aqui (padrão Laravel 11+).
 */

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Módulo de Consultas Vencidas / gestão de status — roda a cada 5 minutos,
// conforme especificado (não depende de alguém abrir a tela para funcionar).
Schedule::command('consultas:marcar-vencidas')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('consultas:marcar-no-show')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
