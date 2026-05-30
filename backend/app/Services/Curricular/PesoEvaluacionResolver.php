<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\PesosEvaluacionInvalidosException;
use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\MallaCurricular;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PesoEvaluacionResolver
{
    public const PESO_CUADERNO_DEFAULT = 33.33;

    public const PESO_LIBRO_DEFAULT = 33.33;

    public const PESO_TAREA_DEFAULT = 33.34;

    public const SCOPE_CURSO = 'curso';

    public const SCOPE_AREA = 'area';

    public const SCOPE_NIVEL_GRADO = 'nivel_grado';

    public const SCOPE_GLOBAL = 'global';

    public const SCOPE_POR_DEFECTO = 'por_defecto';

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
        return $this->resolverDetalleParaCurso($mallaCurso, $malla)['pesos'];
    }

    /**
     * @return array{
     *     pesos: array{cuaderno: float, libro: float, tarea: float},
     *     configuracion: ConfiguracionPesoEvaluacion|null,
     *     scope_aplicado: string,
     *     es_por_defecto: bool
     * }
     */
    public function resolverDetalleParaCurso(MallaCurso $mallaCurso, MallaCurricular $malla): array
    {
        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->where('curso_catalogo_id', $mallaCurso->curso_catalogo_id)
            ->where('area_id', $mallaCurso->area_id)
            ->first();

        if ($config !== null) {
            return $this->buildDetalle($config, self::SCOPE_CURSO);
        }

        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->where('area_id', $mallaCurso->area_id)
            ->whereNull('curso_catalogo_id')
            ->first();

        if ($config !== null) {
            return $this->buildDetalle($config, self::SCOPE_AREA);
        }

        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->where('nivel', $malla->nivel)
            ->where('grado', $malla->grado)
            ->whereNull('area_id')
            ->whereNull('curso_catalogo_id')
            ->first();

        if ($config !== null) {
            return $this->buildDetalle($config, self::SCOPE_NIVEL_GRADO);
        }

        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->whereNull('nivel')
            ->whereNull('grado')
            ->whereNull('area_id')
            ->whereNull('curso_catalogo_id')
            ->first();

        if ($config !== null) {
            return $this->buildDetalle($config, self::SCOPE_GLOBAL);
        }

        return [
            'pesos' => $this->pesosPorDefecto(),
            'configuracion' => null,
            'scope_aplicado' => self::SCOPE_POR_DEFECTO,
            'es_por_defecto' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{nivel: ?string, grado: ?string, area_id: ?int, curso_catalogo_id: ?int}
     */
    public function normalizarScope(array $data): array
    {
        return [
            'nivel' => $data['nivel'] ?? null,
            'grado' => $data['grado'] ?? null,
            'area_id' => isset($data['area_id']) ? (int) $data['area_id'] : null,
            'curso_catalogo_id' => isset($data['curso_catalogo_id']) ? (int) $data['curso_catalogo_id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function validarCombinacionScope(array $data): string
    {
        $scope = $this->normalizarScope($data);
        $nivel = $scope['nivel'];
        $grado = $scope['grado'];
        $areaId = $scope['area_id'];
        $cursoId = $scope['curso_catalogo_id'];

        if ($cursoId !== null) {
            if ($areaId === null) {
                throw ValidationException::withMessages([
                    'area_id' => ['Debe indicar el área cuando configura pesos por curso.'],
                ]);
            }

            return self::SCOPE_CURSO;
        }

        if ($areaId !== null) {
            if ($nivel !== null || $grado !== null) {
                throw ValidationException::withMessages([
                    'area_id' => ['La configuración por área no debe incluir nivel ni grado.'],
                ]);
            }

            return self::SCOPE_AREA;
        }

        if ($nivel !== null || $grado !== null) {
            if ($nivel === null || $grado === null) {
                throw ValidationException::withMessages([
                    'grado' => ['Debe indicar nivel y grado para configurar pesos por grado.'],
                ]);
            }

            return self::SCOPE_NIVEL_GRADO;
        }

        return self::SCOPE_GLOBAL;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertScopeActivoUnico(array $data, ?int $exceptId = null): void
    {
        $this->validarCombinacionScope($data);
        $scope = $this->normalizarScope($data);

        $query = $this->queryPorScope($scope)->where('activo', true);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'scope' => ['Ya existe una configuración activa para este alcance. Desactívela antes de crear otra.'],
            ]);
        }
    }

    /**
     * @param  array{nivel: ?string, grado: ?string, area_id: ?int, curso_catalogo_id: ?int}  $scope
     */
    private function queryPorScope(array $scope): Builder
    {
        $query = ConfiguracionPesoEvaluacion::query();

        foreach (['nivel', 'grado', 'area_id', 'curso_catalogo_id'] as $field) {
            $value = $scope[$field] ?? null;
            if ($value === null) {
                $query->whereNull($field);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * @return array{
     *     pesos: array{cuaderno: float, libro: float, tarea: float},
     *     configuracion: ConfiguracionPesoEvaluacion,
     *     scope_aplicado: string,
     *     es_por_defecto: bool
     * }
     */
    private function buildDetalle(ConfiguracionPesoEvaluacion $config, string $scope): array
    {
        return [
            'pesos' => [
                'cuaderno' => (float) $config->peso_cuaderno,
                'libro' => (float) $config->peso_libro,
                'tarea' => (float) $config->peso_tarea,
            ],
            'configuracion' => $config,
            'scope_aplicado' => $scope,
            'es_por_defecto' => false,
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
