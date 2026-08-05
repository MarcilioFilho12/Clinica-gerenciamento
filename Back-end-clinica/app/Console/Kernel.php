<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * ATENÇÃO: esta classe NÃO está vinculada no bootstrap (Laravel 11+ não faz o bind
 * automático de Illuminate\Contracts\Console\Kernel::class -> App\Console\Kernel::class
 * como em versões anteriores). `bootstrap/app.php` carrega `console.php` (raiz do
 * projeto) via `withRouting(commands: ...)` — é ALI que o Scheduler é registrado
 * (Schedule::command(...)), não aqui. Mantido apenas por compatibilidade/histórico;
 * não adicione `schedule()` aqui, pois nunca será executado.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
