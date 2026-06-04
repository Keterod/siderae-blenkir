<?php

namespace App\Support;

final class ExcelSheetNameSanitizer
{
    private const MAX_LENGTH = 31;

    /**
     * @param  list<string>  $nombresUsados
     */
    public function sanitizar(string $nombreBase, array $nombresUsados = []): string
    {
        $base = $this->limpiar($nombreBase);
        if ($base === '') {
            $base = 'Curso';
        }

        $candidato = mb_substr($base, 0, self::MAX_LENGTH);
        if (! in_array($candidato, $nombresUsados, true)) {
            return $candidato;
        }

        $n = 2;
        while (true) {
            $sufijo = " ({$n})";
            $maxBase = self::MAX_LENGTH - mb_strlen($sufijo);
            $truncado = mb_substr($base, 0, max(1, $maxBase)).$sufijo;
            if (! in_array($truncado, $nombresUsados, true)) {
                return $truncado;
            }
            $n++;
        }
    }

    private function limpiar(string $nombre): string
    {
        $nombre = trim($nombre);
        $nombre = str_replace(['[', ']', '*', '?', '/', '\\', ':'], '', $nombre);
        $nombre = preg_replace('/\s+/u', ' ', $nombre) ?? $nombre;

        return trim($nombre);
    }
}
