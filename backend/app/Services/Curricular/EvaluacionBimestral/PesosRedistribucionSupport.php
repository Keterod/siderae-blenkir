<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Exceptions\Curricular\PesosEvaluacionBimestralInvalidosException;

trait PesosRedistribucionSupport
{
    private const TOLERANCIA_SUMA = 0.01;

    /**
     * @param  list<float|int|string>  $pesos
     * @return list<float>
     */
    public function redistribuirEquitativo(int $cantidad): array
    {
        if ($cantidad <= 0) {
            throw new PesosEvaluacionBimestralInvalidosException('Debe haber al menos un ítem activo para redistribuir pesos.');
        }

        $base = round(100 / $cantidad, 2);
        $pesos = array_fill(0, $cantidad, $base);
        $suma = round(array_sum($pesos), 2);
        $ajuste = round(100 - $suma, 2);
        $pesos[$cantidad - 1] = round($pesos[$cantidad - 1] + $ajuste, 2);

        return $pesos;
    }

    /**
     * @param  array<int, float|int|string>  $pesosPorId
     */
    public function validarSuma100(array $pesosPorId): void
    {
        $suma = round(array_sum(array_map('floatval', $pesosPorId)), 2);

        if ($suma < 0) {
            throw new PesosEvaluacionBimestralInvalidosException('Cada peso debe ser mayor o igual a 0.');
        }

        if (abs($suma - 100.0) > self::TOLERANCIA_SUMA) {
            throw new PesosEvaluacionBimestralInvalidosException(
                sprintf('La suma de los pesos debe ser 100 (actual: %s).', $suma)
            );
        }
    }

    /**
     * @param  array<int, float>  $pesos
     * @return array<int, float>
     */
    public function renormalizarPesos(array $pesos): array
    {
        $suma = array_sum($pesos);
        if ($suma <= 0) {
            return $pesos;
        }

        $resultado = [];
        $acumulado = 0.0;
        $ids = array_keys($pesos);
        $ultimo = end($ids);

        foreach ($pesos as $id => $peso) {
            if ($id === $ultimo) {
                $resultado[$id] = round(100 - $acumulado, 2);
            } else {
                $normalizado = round(($peso / $suma) * 100, 2);
                $resultado[$id] = $normalizado;
                $acumulado += $normalizado;
            }
        }

        return $resultado;
    }
}
