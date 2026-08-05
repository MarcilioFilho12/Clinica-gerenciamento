<?php

return [
    /*
     * Minutos de tolerância após o horário de início para marcar automaticamente
     * uma consulta CONFIRMADA/CHEGOU sem atendimento iniciado como NO_SHOW.
     */
    'no_show_tolerancia_minutos' => (int) env('NO_SHOW_TOLERANCIA_MINUTOS', 30),
];
