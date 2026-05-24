<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;

class CeCalculatorService
{
    public function __construct(
        private readonly PesoEvaluacionResolver $pesoEvaluacionResolver = new PesoEvaluacionResolver,
    ) {}

    /**
     * @param  array{cuaderno: float, libro: float, tarea: float}  $pesos
     */
    public function calcular(?float $cuaderno, ?float $libro, ?float $tarea, ?array $pesos = null): float
    {
        $pesos = $pesos ?? $this->pesoEvaluacionResolver->pesosPorDefecto();
        $this->pesoEvaluacionResolver->validarSuma100($pesos);

        /** @var array<string, float> $presentes */
        $presentes = [];
        if ($cuaderno !== null) {
            $this->validarRango($cuaderno);
            $presentes['cuaderno'] = $cuaderno;
        }
        if ($libro !== null) {
            $this->validarRango($libro);
            $presentes['libro'] = $libro;
        }
        if ($tarea !== null) {
            $this->validarRango($tarea);
            $presentes['tarea'] = $tarea;
        }

        if ($presentes === []) {
            throw new NotasCurricularesVaciasException;
        }

        if ($this->pesoEvaluacionResolver->sonPesosPorDefecto($pesos)) {
            return round(array_sum($presentes) / count($presentes), 2);
        }

        $sumaPesos = 0.0;
        $sumaPonderada = 0.0;
        foreach ($presentes as $clave => $valor) {
            $peso = (float) $pesos[$clave];
            $sumaPesos += $peso;
            $sumaPonderada += $valor * $peso;
        }

        if ($sumaPesos <= 0) {
            throw new NotasCurricularesVaciasException;
        }

        return round($sumaPonderada / $sumaPesos, 2);
    }

    private function validarRango(float $nota): void
    {
        if ($nota < 0 || $nota > 20) {
            throw new NotaCurricularFueraDeRangoException;
        }
    }
}
