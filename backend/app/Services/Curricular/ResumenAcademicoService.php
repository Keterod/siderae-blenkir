<?php

namespace App\Services\Curricular;

use App\Models\Curricular\EvalBimNotaEta;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\NotaSemanal;
use App\Models\Estudiante;
use Illuminate\Support\Collection;

class ResumenAcademicoService
{
    public function __construct(
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
    ) {}

    /**
     * @return array{
     *   estudiante_id: int,
     *   anio_escolar: string,
     *   nivel: string,
     *   grado: string,
     *   tiene_datos: bool,
     *   ce_por_tema: list<array<string, mixed>>,
     *   promedios_por_curso: list<array<string, mixed>>,
     *   promedios_por_area: list<array<string, mixed>>,
     *   promedios_bimestrales: list<array<string, mixed>>,
     *   evaluaciones_bimestrales: list<array<string, mixed>>,
     * }
     */
    public function construir(Estudiante $estudiante, ?string $anioEscolar = null): array
    {
        $anio = $anioEscolar ?? $estudiante->anio_escolar;
        $gradoCurricular = $this->equivalenciaGradoService->aCurricular(
            (string) $estudiante->nivel,
            (string) $estudiante->grado,
        );

        $notas = $this->notasSemanalesDelEstudiante($estudiante, $anio, $gradoCurricular);

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

            return [
                'malla_curso_id' => (int) $mallaCursoId,
                'curso' => $primera->temaSemanal->mallaCurso->cursoCatalogo->nombre,
                'area' => $primera->temaSemanal->mallaCurso->area->nombre,
                'promedio_ce' => round($grupo->avg('ce_calculado'), 2),
                'cantidad_registros' => $grupo->count(),
            ];
        })->values()->all();

        $porArea = $notas->groupBy(fn (NotaSemanal $n) => $n->temaSemanal->mallaCurso->area_id);
        $promediosPorArea = $porArea->map(function (Collection $grupo, $areaId) {
            return [
                'area_id' => (int) $areaId,
                'area' => $grupo->first()->temaSemanal->mallaCurso->area->nombre,
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

        $evaluacionesBimestrales = $this->evaluacionesBimestralesDelEstudiante(
            $estudiante,
            $anio,
            $gradoCurricular,
        );

        $tieneDatos = $cePorTema !== [] || $evaluacionesBimestrales !== [];

        return [
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => $anio,
            'nivel' => $estudiante->nivel,
            'grado' => $estudiante->grado,
            'tiene_datos' => $tieneDatos,
            'ce_por_tema' => $cePorTema,
            'promedios_por_curso' => $promediosPorCurso,
            'promedios_por_area' => $promediosPorArea,
            'promedios_bimestrales' => $promediosBimestrales,
            'evaluaciones_bimestrales' => $evaluacionesBimestrales,
        ];
    }

    /**
     * @return Collection<int, NotaSemanal>
     */
    private function notasSemanalesDelEstudiante(
        Estudiante $estudiante,
        string $anio,
        ?string $gradoCurricular,
    ): Collection {
        $query = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereNotNull('ce_calculado')
            ->whereHas('temaSemanal', fn ($q) => $q->where('activo', true))
            ->whereHas('temaSemanal.mallaCurso', fn ($q) => $q->where('activo', true))
            ->whereHas('temaSemanal.mallaCurso.mallaCurricular', function ($q) use ($anio, $estudiante, $gradoCurricular): void {
                $q->where('anio_escolar', $anio)
                    ->where('nivel', $estudiante->nivel)
                    ->where('estado', 'activa');
                if ($gradoCurricular !== null) {
                    $q->where('grado', $gradoCurricular);
                }
            })
            ->with([
                'temaSemanal.periodoAcademico',
                'temaSemanal.semanaAcademica',
                'temaSemanal.mallaCurso.area',
                'temaSemanal.mallaCurso.cursoCatalogo',
                'temaSemanal.mallaCurso.mallaCurricular',
            ]);

        return $query->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function evaluacionesBimestralesDelEstudiante(
        Estudiante $estudiante,
        string $anio,
        ?string $gradoCurricular,
    ): array {
        if ($gradoCurricular === null) {
            return [];
        }

        $resultados = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereHas('periodoAcademico', fn ($q) => $q->where('anio_escolar', $anio))
            ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('nivel', $estudiante->nivel)
                ->where('grado', $gradoCurricular))
            ->with(['mallaCurso.area', 'mallaCurso.cursoCatalogo', 'periodoAcademico'])
            ->orderBy('malla_curso_id')
            ->orderBy('periodo_academico_id')
            ->get();

        return $resultados->map(function (EvalBimResultado $resultado) use ($estudiante): array {
            $etas = EvalBimNotaEta::query()
                ->where('estudiante_id', $estudiante->id)
                ->whereHas('etaItem.componente', fn ($q) => $q
                    ->where('malla_curso_id', $resultado->malla_curso_id)
                    ->where('periodo_academico_id', $resultado->periodo_academico_id))
                ->with('etaItem')
                ->orderBy('eval_bim_eta_item_id')
                ->get()
                ->map(fn (EvalBimNotaEta $nota) => [
                    'nombre' => $nota->etaItem->nombre,
                    'nota' => $nota->nota !== null ? (float) $nota->nota : null,
                ])
                ->values()
                ->all();

            return [
                'malla_curso_id' => $resultado->malla_curso_id,
                'periodo_academico_id' => $resultado->periodo_academico_id,
                'curso' => $resultado->mallaCurso->cursoCatalogo?->nombre ?? '',
                'area' => $resultado->mallaCurso->area?->nombre ?? '',
                'bimestre' => $resultado->periodoAcademico->bimestre,
                'anio_escolar' => $resultado->periodoAcademico->anio_escolar,
                'promedio_criterios' => $resultado->promedio_criterios !== null
                    ? (float) $resultado->promedio_criterios
                    : null,
                'oral' => $resultado->oral !== null ? (float) $resultado->oral : null,
                'promedio_eta' => $resultado->promedio_eta !== null ? (float) $resultado->promedio_eta : null,
                'examen_bimestral' => $resultado->examen_bimestral !== null
                    ? (float) $resultado->examen_bimestral
                    : null,
                'nivel_logro_numerico' => $resultado->nivel_logro_numerico !== null
                    ? (float) $resultado->nivel_logro_numerico
                    : null,
                'nivel_logro_literal' => $resultado->nivel_logro_literal,
                'estado_calculo' => $resultado->estado_calculo?->value,
                'conclusion_descriptiva' => $resultado->conclusion_descriptiva,
                'calculado_en' => $resultado->calculado_en?->toIso8601String(),
                'etas' => $etas,
            ];
        })->values()->all();
    }
}
