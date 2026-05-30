<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;

class CeComponentesDinamicosService
{
    private const TOLERANCIA_PESOS_IGUALES = 0.01;

    /**
     * @param  list<array{nota: float, peso: float}>  $componentesPresentes
     */
    public function calcular(array $componentesPresentes): float
    {
        if ($componentesPresentes === []) {
            throw new NotasCurricularesVaciasException;
        }

        foreach ($componentesPresentes as $item) {
            $this->validarRango((float) $item['nota']);
        }

        $pesos = array_map(fn (array $item) => (float) $item['peso'], $componentesPresentes);
        if ($this->sonPesosIguales($pesos)) {
            $notas = array_map(fn (array $item) => (float) $item['nota'], $componentesPresentes);

            return round(array_sum($notas) / count($notas), 2);
        }

        $sumaPesos = 0.0;
        $sumaPonderada = 0.0;
        foreach ($componentesPresentes as $item) {
            $peso = (float) $item['peso'];
            $nota = (float) $item['nota'];
            $sumaPesos += $peso;
            $sumaPonderada += $nota * $peso;
        }

        if ($sumaPesos <= 0) {
            throw new NotasCurricularesVaciasException;
        }

        return round($sumaPonderada / $sumaPesos, 2);
    }

    /**
     * @param  list<float>  $pesos
     */
    private function sonPesosIguales(array $pesos): bool
    {
        if ($pesos === []) {
            return false;
        }

        $referencia = (float) $pesos[0];

        foreach ($pesos as $peso) {
            if (abs((float) $peso - $referencia) > self::TOLERANCIA_PESOS_IGUALES) {
                return false;
            }
        }

        return true;
    }

    private function validarRango(float $nota): void
    {
        if ($nota < 0 || $nota > 20) {
            throw new NotaCurricularFueraDeRangoException;
        }
    }
}
