<?php

namespace App\Services\Curricular;

use App\Models\Curricular\AnioEscolar;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioAcademicoService
{
    public const BIMESTRES = ['1', '2', '3', '4'];

    public const SEMANAS_POR_BIMESTRE_DEFAULT = 4;

    /**
     * @return array<string, mixed>|null
     */
    public function obtenerAnioActivoConVigente(): ?array
    {
        $anio = AnioEscolar::query()
            ->where('es_activo', true)
            ->first();

        if ($anio === null) {
            return null;
        }

        $periodoVigente = PeriodoAcademico::query()
            ->where('anio_escolar_id', $anio->id)
            ->where('es_vigente', true)
            ->first();

        return [
            'anio_escolar' => $this->serializarAnio($anio),
            'periodo_vigente' => $periodoVigente ? $this->serializarPeriodo($periodoVigente) : null,
        ];
    }

    public function obtenerAnioActivo(): ?AnioEscolar
    {
        return AnioEscolar::query()->where('es_activo', true)->first();
    }

    /**
     * @param  array{
     *   anio: string,
     *   nombre: string,
     *   fecha_inicio?: string|null,
     *   fecha_fin?: string|null,
     *   generar_bimestres?: bool,
     * }  $datos
     */
    public function crearAnioEscolar(array $datos): AnioEscolar
    {
        $this->validarRangoFechas($datos['fecha_inicio'] ?? null, $datos['fecha_fin'] ?? null, 'fecha_inicio', 'fecha_fin');

        return DB::transaction(function () use ($datos) {
            $anio = AnioEscolar::query()->create([
                'anio' => trim($datos['anio']),
                'nombre' => trim($datos['nombre']),
                'fecha_inicio' => $datos['fecha_inicio'] ?? null,
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'estado' => 'inactivo',
                'es_activo' => false,
            ]);

            if ($datos['generar_bimestres'] ?? false) {
                $this->generarBimestres($anio);
            }

            return $anio->fresh(['periodosAcademicos.semanasAcademicas']);
        });
    }

    /**
     * @param  array{
     *   nombre?: string,
     *   fecha_inicio?: string|null,
     *   fecha_fin?: string|null,
     * }  $datos
     */
    public function actualizarAnioEscolar(AnioEscolar $anio, array $datos): AnioEscolar
    {
        if ($anio->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se puede editar un año escolar cerrado.'],
            ]);
        }

        $fechaInicio = array_key_exists('fecha_inicio', $datos) ? $datos['fecha_inicio'] : $anio->fecha_inicio?->format('Y-m-d');
        $fechaFin = array_key_exists('fecha_fin', $datos) ? $datos['fecha_fin'] : $anio->fecha_fin?->format('Y-m-d');
        $this->validarRangoFechas($fechaInicio, $fechaFin, 'fecha_inicio', 'fecha_fin');

        $anio->fill(array_filter([
            'nombre' => isset($datos['nombre']) ? trim($datos['nombre']) : null,
            'fecha_inicio' => $datos['fecha_inicio'] ?? null,
            'fecha_fin' => $datos['fecha_fin'] ?? null,
        ], static fn ($v) => $v !== null));

        $anio->save();

        return $anio->fresh(['periodosAcademicos.semanasAcademicas']);
    }

    public function activarAnioEscolar(AnioEscolar $anio): AnioEscolar
    {
        if ($anio->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se puede activar un año escolar cerrado.'],
            ]);
        }

        return DB::transaction(function () use ($anio) {
            AnioEscolar::query()
                ->where('id', '!=', $anio->id)
                ->where('es_activo', true)
                ->update(['es_activo' => false, 'estado' => 'inactivo']);

            $anio->update([
                'es_activo' => true,
                'estado' => 'activo',
            ]);

            return $anio->fresh(['periodosAcademicos.semanasAcademicas']);
        });
    }

    public function cerrarAnioEscolar(AnioEscolar $anio): AnioEscolar
    {
        return DB::transaction(function () use ($anio) {
            PeriodoAcademico::query()
                ->where('anio_escolar_id', $anio->id)
                ->where('estado', '!=', 'cerrado')
                ->update([
                    'estado' => 'cerrado',
                    'activo' => false,
                    'es_vigente' => false,
                ]);

            $anio->update([
                'estado' => 'cerrado',
                'es_activo' => false,
            ]);

            return $anio->fresh(['periodosAcademicos.semanasAcademicas']);
        });
    }

    /**
     * @return Collection<int, PeriodoAcademico>
     */
    public function generarBimestres(AnioEscolar $anio, int $semanasPlanificadas = self::SEMANAS_POR_BIMESTRE_DEFAULT): Collection
    {
        if ($anio->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se pueden generar bimestres en un año cerrado.'],
            ]);
        }

        return DB::transaction(function () use ($anio, $semanasPlanificadas) {
            $periodos = collect();

            foreach (self::BIMESTRES as $bimestre) {
                $periodo = PeriodoAcademico::query()->updateOrCreate(
                    [
                        'anio_escolar' => $anio->anio,
                        'bimestre' => $bimestre,
                    ],
                    [
                        'anio_escolar_id' => $anio->id,
                        'semanas_planificadas' => $semanasPlanificadas,
                        'activo' => true,
                        'estado' => 'activo',
                        'es_vigente' => $bimestre === '1' && ! PeriodoAcademico::query()
                            ->where('anio_escolar_id', $anio->id)
                            ->where('es_vigente', true)
                            ->exists(),
                    ]
                );

                $periodos->push($periodo);
            }

            return $periodos;
        });
    }

    /**
     * @param  array{
     *   fecha_inicio?: string|null,
     *   fecha_fin?: string|null,
     *   semanas_planificadas?: int,
     * }  $datos
     */
    public function actualizarPeriodo(PeriodoAcademico $periodo, array $datos): PeriodoAcademico
    {
        if ($periodo->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se puede editar un bimestre cerrado.'],
            ]);
        }

        $fechaInicio = array_key_exists('fecha_inicio', $datos)
            ? $datos['fecha_inicio']
            : $periodo->fecha_inicio?->format('Y-m-d');
        $fechaFin = array_key_exists('fecha_fin', $datos)
            ? $datos['fecha_fin']
            : $periodo->fecha_fin?->format('Y-m-d');

        $this->validarRangoFechas($fechaInicio, $fechaFin, 'fecha_inicio', 'fecha_fin');

        if ($fechaInicio !== null && $fechaFin !== null) {
            $this->validarSolapamientoBimestres($periodo, $fechaInicio, $fechaFin);
        }

        if (isset($datos['semanas_planificadas'])) {
            $periodo->semanas_planificadas = (int) $datos['semanas_planificadas'];
        }
        if (array_key_exists('fecha_inicio', $datos)) {
            $periodo->fecha_inicio = $datos['fecha_inicio'];
        }
        if (array_key_exists('fecha_fin', $datos)) {
            $periodo->fecha_fin = $datos['fecha_fin'];
        }

        $periodo->save();

        return $periodo->fresh(['semanasAcademicas', 'anioEscolar']);
    }

    public function marcarPeriodoVigente(PeriodoAcademico $periodo): PeriodoAcademico
    {
        if ($periodo->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se puede marcar vigente un bimestre cerrado.'],
            ]);
        }

        return DB::transaction(function () use ($periodo) {
            PeriodoAcademico::query()
                ->where('anio_escolar', $periodo->anio_escolar)
                ->where('id', '!=', $periodo->id)
                ->update(['es_vigente' => false]);

            $periodo->update([
                'es_vigente' => true,
                'estado' => 'activo',
                'activo' => true,
            ]);

            return $periodo->fresh(['semanasAcademicas', 'anioEscolar']);
        });
    }

    public function cerrarPeriodo(PeriodoAcademico $periodo): PeriodoAcademico
    {
        return DB::transaction(function () use ($periodo) {
            $periodo->update([
                'estado' => 'cerrado',
                'activo' => false,
                'es_vigente' => false,
            ]);

            return $periodo->fresh(['semanasAcademicas', 'anioEscolar']);
        });
    }

    /**
     * @return Collection<int, SemanaAcademica>
     */
    public function generarSemanas(PeriodoAcademico $periodo): Collection
    {
        if ($periodo->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'estado' => ['No se pueden generar semanas en un bimestre cerrado.'],
            ]);
        }

        $total = max(1, (int) $periodo->semanas_planificadas);

        return DB::transaction(function () use ($periodo, $total) {
            $semanas = collect();

            for ($numero = 1; $numero <= $total; $numero++) {
                $semanas->push(
                    SemanaAcademica::query()->updateOrCreate(
                        [
                            'periodo_academico_id' => $periodo->id,
                            'numero_semana' => $numero,
                        ],
                        ['activo' => true]
                    )
                );
            }

            return $semanas;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function serializarAnio(AnioEscolar $anio): array
    {
        $anio->loadMissing(['periodosAcademicos.semanasAcademicas']);

        return [
            'id' => $anio->id,
            'anio' => $anio->anio,
            'nombre' => $anio->nombre,
            'fecha_inicio' => $anio->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $anio->fecha_fin?->format('Y-m-d'),
            'estado' => $anio->estado,
            'es_activo' => $anio->es_activo,
            'periodos' => $anio->periodosAcademicos
                ->sortBy('bimestre')
                ->values()
                ->map(fn (PeriodoAcademico $p) => $this->serializarPeriodo($p))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializarPeriodo(PeriodoAcademico $periodo): array
    {
        $periodo->loadMissing('semanasAcademicas');

        return [
            'id' => $periodo->id,
            'anio_escolar_id' => $periodo->anio_escolar_id,
            'anio_escolar' => $periodo->anio_escolar,
            'bimestre' => $periodo->bimestre,
            'fecha_inicio' => $periodo->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $periodo->fecha_fin?->format('Y-m-d'),
            'semanas_planificadas' => $periodo->semanas_planificadas,
            'activo' => $periodo->activo,
            'estado' => $periodo->estado,
            'es_vigente' => $periodo->es_vigente,
            'semanas' => $periodo->semanasAcademicas
                ->sortBy('numero_semana')
                ->values()
                ->map(fn (SemanaAcademica $s) => [
                    'id' => $s->id,
                    'numero_semana' => $s->numero_semana,
                    'fecha_inicio' => $s->fecha_inicio?->format('Y-m-d'),
                    'fecha_fin' => $s->fecha_fin?->format('Y-m-d'),
                    'activo' => $s->activo,
                ])
                ->all(),
        ];
    }

    private function validarRangoFechas(?string $inicio, ?string $fin, string $campoInicio, string $campoFin): void
    {
        if ($inicio === null || $fin === null || $inicio === '' || $fin === '') {
            return;
        }

        if ($fin < $inicio) {
            throw ValidationException::withMessages([
                $campoFin => ['La fecha de fin debe ser posterior o igual a la fecha de inicio.'],
            ]);
        }
    }

    private function validarSolapamientoBimestres(PeriodoAcademico $periodo, string $fechaInicio, string $fechaFin): void
    {
        $solapado = PeriodoAcademico::query()
            ->where('anio_escolar', $periodo->anio_escolar)
            ->where('id', '!=', $periodo->id)
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_fin')
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $fechaInicio)
            ->exists();

        if ($solapado) {
            throw ValidationException::withMessages([
                'fecha_inicio' => ['Las fechas del bimestre se solapan con otro bimestre del mismo año.'],
            ]);
        }
    }
}
