<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\PesosEvaluacionInvalidosException;
use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\MallaCurricular;

class PesoEvaluacionResolver
{
    public const PESO_CUADERNO_DEFAULT = 33.33;

    public const PESO_LIBRO_DEFAULT = 33.33;

    public const PESO_TAREA_DEFAULT = 33.34;

    private const TOLERANCIA_SUMA = 0.01;

    /**
     * @return array{cuaderno: float, libro: float, tarea: float}
     */
    public function pesosPorDefecto(): array
    {
        return [
            'cuaderno' => self::PESO_CUADERNO_DEFAULT,
            'libro' => self::PESO_LIBRO_DEFAULT,
            'tarea' => self::PESO_TAREA_DEFAULT,
        ];
    }

    /**
     * @param  array{cuaderno?: float|int|string, libro?: float|int|string, tarea?: float|int|string}  $pesos
     */
    public function validarSuma100(array $pesos): void
    {
        $cuaderno = (float) ($pesos['cuaderno'] ?? 0);
        $libro = (float) ($pesos['libro'] ?? 0);
        $tarea = (float) ($pesos['tarea'] ?? 0);

        if ($cuaderno < 0 || $libro < 0 || $tarea < 0) {
            throw new PesosEvaluacionInvalidosException('Cada peso debe ser mayor o igual a 0.');
        }

        $suma = round($cuaderno + $libro + $tarea, 2);
        if (abs($suma - 100.0) > self::TOLERANCIA_SUMA) {
            throw new PesosEvaluacionInvalidosException(
                sprintf('La suma de los pesos debe ser 100 (actual: %s).', $suma)
            );
        }
    }

    /**
     * @return array{cuaderno: float, libro: float, tarea: float}
     */
    public function resolver(): array
    {
        return $this->pesosPorDefecto();
    }

    /**
     * @return array{cuaderno: float, libro: float, tarea: float}
     */
    public function resolverParaCurso(MallaCurso $mallaCurso, MallaCurricular $malla): array
    {
        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->where('curso_catalogo_id', $mallaCurso->curso_catalogo_id)
            ->where('area_id', $mallaCurso->area_id)
            ->first();

        if ($config === null) {
            $config = ConfiguracionPesoEvaluacion::query()
                ->where('activo', true)
                ->where('area_id', $mallaCurso->area_id)
                ->whereNull('curso_catalogo_id')
                ->first();
        }

        if ($config === null) {
            $config = ConfiguracionPesoEvaluacion::query()
                ->where('activo', true)
                ->where('nivel', $malla->nivel)
                ->where('grado', $malla->grado)
                ->whereNull('area_id')
                ->whereNull('curso_catalogo_id')
                ->first();
        }

        if ($config === null) {
            $config = ConfiguracionPesoEvaluacion::query()
                ->where('activo', true)
                ->whereNull('nivel')
                ->whereNull('grado')
                ->whereNull('area_id')
                ->whereNull('curso_catalogo_id')
                ->first();
        }

        if ($config === null) {
            return $this->pesosPorDefecto();
        }

        return [
            'cuaderno' => (float) $config->peso_cuaderno,
            'libro' => (float) $config->peso_libro,
            'tarea' => (float) $config->peso_tarea,
        ];
    }

    /**
     * @param  array{cuaderno: float, libro: float, tarea: float}  $pesos
     */
    public function sonPesosPorDefecto(array $pesos): bool
    {
        return abs((float) $pesos['cuaderno'] - self::PESO_CUADERNO_DEFAULT) < self::TOLERANCIA_SUMA
            && abs((float) $pesos['libro'] - self::PESO_LIBRO_DEFAULT) < self::TOLERANCIA_SUMA
            && abs((float) $pesos['tarea'] - self::PESO_TAREA_DEFAULT) < self::TOLERANCIA_SUMA;
    }
}
