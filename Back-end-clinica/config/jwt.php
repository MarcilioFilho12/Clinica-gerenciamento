<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT TTL (PRG 1.6)
    |--------------------------------------------------------------------------
    | Tempo de vida do token em segundos. Default 2 horas (piloto). Mínimo aplicado: 300.
    */
    'ttl_seconds' => (int) env('JWT_TTL_SECONDS', 60 * 60 * 2),
];
