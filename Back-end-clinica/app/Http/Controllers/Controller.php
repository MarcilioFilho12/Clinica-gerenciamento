<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Throwable;

abstract class Controller
{
    /**
     * Resposta 500 sem vazar detalhe interno (PRG 1.2).
     */
    protected function safeServerError(
        Throwable $e,
        string $message = 'Erro interno do servidor',
        int $status = 500,
        array $extra = [],
    ): JsonResponse {
        report($e);

        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $status);
    }
}
