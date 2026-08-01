<?php

namespace App\Support;

final class Profiles
{
    public const ADMIN = 1;

    public const RECEPCAO = 2;

    public const PROFISSIONAL = 3;

    public const NAMES = [
        self::ADMIN => 'Administrador',
        self::RECEPCAO => 'Recepção',
        self::PROFISSIONAL => 'Profissional',
    ];

    /** @return list<int> */
    public static function all(): array
    {
        return [self::ADMIN, self::RECEPCAO, self::PROFISSIONAL];
    }

    /** @return list<int> */
    public static function staff(): array
    {
        return [self::ADMIN, self::RECEPCAO];
    }

    /** @return list<int> */
    public static function clinical(): array
    {
        return [self::ADMIN, self::RECEPCAO, self::PROFISSIONAL];
    }
}
