<?php

namespace App\Support;

/**
 * Sede de operación por defecto en consultas de listado (V1: Chilca).
 * No restringe altas ni requests que envíen explícitamente otra sede válida.
 */
final class SedeOperativa
{
    public const CHILCA = 'chilca';

    public static function defaultConsulta(?string $sede): string
    {
        $trim = is_string($sede) ? trim($sede) : '';

        return $trim !== '' ? $trim : self::CHILCA;
    }
}
