<?php

namespace App\Services\Curricular;

use App\Models\Curricular\NotaSemanal;
use App\Models\Estudiante;
use Illuminate\Support\Collection;

class ResumenAcademicoService
{
    /**
     * @return array{
     *   estudiante_id: int,
     *   anio_escolar: string,
     *   ce_por_tema: list<array<string, mixed>>,
     *   promedios_por_curso: list<array<string, mixed>>,
     *   promedios_por_area: list<array<string, mixed>>,
     *   promedios_bimestrales: list<array<string, mixed>>,
     * }
     */
    public function construir(Estudiante $estudiante, ?string $anioEscolar = null): array
    {
        $anio = $anioEscolar ?? $estudiante->anio_escolar;

        $notas = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereNotNull('ce_calculado')
            ->whereHas('temaSemanal', fn ($q) => $q->where('activo', true))
            ->whereHas('temaSemanal.mallaCurso', fn ($q) => $q->where('activo', true))
            ->whereHas('temaSemanal.mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', $anio)
                ->where('estado', 'activa'))
            ->with([
                'temaSemanal.periodoAcademico',
                'temaSemanal.semanaAcademica',
                'temaSemanal.mallaCurso.area',
                'temaSemanal.mallaCurso.cursoCatalogo',
                'temaSemanal.mallaCurso.mallaCurricular',
            ])
            ->get();

        $cePorTema = $notas->map(fn (NotaSemanal $n) => [
            'tema_semanal_id' => $n->tema_semanal_id,
            'titulo' => $n->temaSemanal->titulo,
            'bimestre' => $n->temaSemanal->periodoAcademico->bimestre,
            'numero_semana' => $n->temaSemanal->semanaAcademica?->numero_semana,
            'curso' => $n->temaSemanal->mallaCurso->cursoCatalogo->nombre,
            'area' => $n->temaSemanal->mallaCurso->area->nombre,
            'ce_calculado' => (float) $n->ce_calculado,
            'fecha_registro' => $n->fecha_registro?->format('Y-m-d'),
        ])->values()->all();

        $porCurso = $notas->groupBy(fn (NotaSemanal $n) => $n->temaSemanal->malla_curso_id);
        $promediosPorCurso = $porCurso->map(function (Collection $grupo, $mallaCursoId) {
            $primera = $grupo->first();
            $curso = $primera->temaSemanal->mallaCurso->cursoCatalogo->nombre;
            $area = $primera->temaSemanal->mallaCurso->area->nombre;

            return [
                'malla_curso_id' => (int) $mallaCursoId,
                'curso' => $curso,
                'area' => $area,
                'promedio_ce' => round($grupo->avg('ce_calculado'), 2),
                'cantidad_registros' => $grupo->count(),
            ];
        })->values()->all();

        $porArea = $notas->groupBy(fn (NotaSemanal $n) => $n->temaSemanal->mallaCurso->area_id);
        $promediosPorArea = $porArea->map(function (Collection $grupo, $areaId) {
            $area = $grupo->first()->temaSemanal->mallaCurso->area->nombre;

            return [
                'area_id' => (int) $areaId,
                'area' => $area,
                'promedio_ce' => round($grupo->avg('ce_calculado'), 2),
                'cantidad_registros' => $grupo->count(),
            ];
        })->values()->all();

        $porBimestre = $notas->groupBy(fn (NotaSemanal $n) => $n->temaSemanal->periodo_academico_id);
        $promediosBimestrales = $porBimestre->map(function (Collection $grupo, $periodoId) {
            $periodo = $grupo->first()->temaSemanal->periodoAcademico;

            return [
                'periodo_academico_id' => (int) $periodoId,
                'bimestre' => $periodo->bimestre,
                'anio_escolar' => $periodo->anio_escolar,
                'promedio_ce' => round($grupo->avg('ce_calculado'), 2),
                'cantidad_registros' => $grupo->count(),
            ];
        })->values()->all();

        return [
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => $anio,
            'ce_por_tema' => $cePorTema,
            'promedios_por_curso' => $promediosPorCurso,
            'promedios_por_area' => $promediosPorArea,
            'promedios_bimestrales' => $promediosBimestrales,
        ];
    }
}
