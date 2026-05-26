<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimNotaEta;
use App\Models\Curricular\EvalBimNotaScalar;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Services\Curricular\EquivalenciaGradoService;
use App\Services\Curricular\EstudianteAsignacionDocenteValidator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EvaluacionBimestralFormularioService
{
    public function __construct(
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
        private readonly EscalaLogroService $escalaLogroService = new EscalaLogroService,
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
        private readonly EtaParticipacionPorAulaService $etaParticipacionService = new EtaParticipacionPorAulaService,
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function construirDocente(DocenteCursoAula $asignacion, int $periodoAcademicoId): array
    {
        $periodo = $this->validarPeriodoAsignacion($asignacion, $periodoAcademicoId);
        $mallaCurso = $this->validarMallaCurso($asignacion->malla_curso_id);
        $estudiantes = $this->estudiantesDeAsignacion($asignacion);
        $aula = $this->contextoDesdeAsignacion($asignacion, $periodoAcademicoId, $estudiantes);

        return $this->armarRespuesta(
            contexto: [
                'modo' => 'docente',
                'asignacion_docente_id' => $asignacion->id,
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'anio_escolar' => $asignacion->anio_escolar,
                'nivel' => $asignacion->nivel,
                'sede' => $asignacion->sede,
                'grado' => $asignacion->grado,
                'seccion' => $asignacion->seccion,
                'curso' => [
                    'id' => $mallaCurso->id,
                    'nombre' => $mallaCurso->cursoCatalogo?->nombre ?? '',
                ],
                'periodo' => $periodo,
            ],
            aula: $aula,
            estudiantes: $estudiantes,
            mallaCursoId: $mallaCurso->id,
            periodoAcademicoId: $periodo->id,
            readonly: false,
        );
    }

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     sede: string,
     *     grado: string,
     *     seccion: string,
     *     malla_curso_id: int,
     *     periodo_academico_id: int
     * } $filtros
     * @return array<string, mixed>
     */
    public function construirConsulta(array $filtros): array
    {
        $periodo = PeriodoAcademico::query()->findOrFail($filtros['periodo_academico_id']);

        if ($periodo->anio_escolar !== $filtros['anio_escolar']) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar indicado.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with(['cursoCatalogo', 'mallaCurricular'])
            ->findOrFail($filtros['malla_curso_id']);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $estudiantes = Estudiante::query()
            ->where('anio_escolar', $filtros['anio_escolar'])
            ->where('nivel', $filtros['nivel'])
            ->where('sede', $filtros['sede'])
            ->where('seccion', $filtros['seccion'])
            ->where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->filter(function (Estudiante $e) use ($filtros) {
                $equiv = $this->equivalenciaGradoService->aCurricular($e->nivel, $e->grado);

                return $equiv === $filtros['grado'];
            })
            ->values();

        $aula = new AulaEvaluacionContext(
            mallaCursoId: $mallaCurso->id,
            periodoAcademicoId: $periodo->id,
            sede: $filtros['sede'],
            grado: $filtros['grado'],
            seccion: $filtros['seccion'],
            estudianteIds: $estudiantes->pluck('id')->all(),
        );

        return $this->armarRespuesta(
            contexto: [
                'modo' => 'consulta',
                'consulta_global' => true,
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'anio_escolar' => $filtros['anio_escolar'],
                'nivel' => $filtros['nivel'],
                'sede' => $filtros['sede'],
                'grado' => $filtros['grado'],
                'seccion' => $filtros['seccion'],
                'curso' => [
                    'id' => $mallaCurso->id,
                    'nombre' => $mallaCurso->cursoCatalogo?->nombre ?? '',
                ],
                'periodo' => $periodo,
            ],
            aula: $aula,
            estudiantes: $estudiantes,
            mallaCursoId: $mallaCurso->id,
            periodoAcademicoId: $periodo->id,
            readonly: true,
        );
    }

    /**
     * @param  Collection<int, Estudiante>  $estudiantes
     * @return array<string, mixed>
     */
    private function armarRespuesta(
        array $contexto,
        AulaEvaluacionContext $aula,
        Collection $estudiantes,
        int $mallaCursoId,
        int $periodoAcademicoId,
        bool $readonly,
    ): array {
        $config = $this->componentesResolver->resolver($mallaCursoId, $periodoAcademicoId);
        $participacion = $this->etaParticipacionService->resolverParticipacion(
            $aula,
            $config['eta_items_activos'],
        );

        $estudianteIds = $estudiantes->pluck('id');
        $componenteIds = $config['componentes']->pluck('id');
        $etaIds = $config['eta_items']->pluck('id');

        $notasScalar = $estudianteIds->isEmpty() || $componenteIds->isEmpty()
            ? collect()
            : EvalBimNotaScalar::query()
                ->whereIn('estudiante_id', $estudianteIds)
                ->whereIn('eval_bim_componente_id', $componenteIds)
                ->get();

        $notasEta = $estudianteIds->isEmpty() || $etaIds->isEmpty()
            ? collect()
            : EvalBimNotaEta::query()
                ->whereIn('estudiante_id', $estudianteIds)
                ->whereIn('eval_bim_eta_item_id', $etaIds)
                ->get();

        $resultados = $estudianteIds->isEmpty()
            ? collect()
            : EvalBimResultado::query()
                ->whereIn('estudiante_id', $estudianteIds)
                ->where('malla_curso_id', $mallaCursoId)
                ->where('periodo_academico_id', $periodoAcademicoId)
                ->where('sede', $aula->sede)
                ->where('grado', $aula->grado)
                ->where('seccion', $aula->seccion)
                ->get();

        $notasScalarPorEstudiante = [];
        $notasEtaPorEstudiante = [];
        $resultadosPorEstudiante = [];

        foreach ($estudiantes as $estudiante) {
            $notasScalarPorEstudiante[$estudiante->id] = [];
            $notasEtaPorEstudiante[$estudiante->id] = [];

            foreach ($notasScalar->where('estudiante_id', $estudiante->id) as $nota) {
                $notasScalarPorEstudiante[$estudiante->id][$nota->eval_bim_componente_id] = [
                    'nota' => $nota->nota !== null ? (float) $nota->nota : null,
                ];
            }

            foreach ($notasEta->where('estudiante_id', $estudiante->id) as $nota) {
                $notasEtaPorEstudiante[$estudiante->id][$nota->eval_bim_eta_item_id] = [
                    'nota' => $nota->nota !== null ? (float) $nota->nota : null,
                ];
            }

            $res = $resultados->firstWhere('estudiante_id', $estudiante->id);
            $resultadosPorEstudiante[$estudiante->id] = $res !== null
                ? $this->serializarResultado($res)
                : null;
        }

        return [
            'contexto' => $contexto,
            'estudiantes' => $estudiantes->values(),
            'componentes' => $config['componentes']->map(fn ($c) => $this->serializarComponente($c))->values(),
            'etas' => $config['eta_items']->map(fn ($e) => $this->serializarEta($e))->values(),
            'eta_participantes_ids' => $participacion['participantes']->pluck('id')->values(),
            'eta_pesos_efectivos' => $participacion['pesos_efectivos'],
            'escala_logro' => $this->escalaLogroService->listarEscalaActiva(),
            'resultados_por_estudiante' => $resultadosPorEstudiante,
            'notas_scalar_por_estudiante' => $notasScalarPorEstudiante,
            'notas_eta_por_estudiante' => $notasEtaPorEstudiante,
            'readonly' => $readonly,
        ];
    }

    /**
     * @return Collection<int, Estudiante>
     */
    private function estudiantesDeAsignacion(DocenteCursoAula $asignacion): Collection
    {
        return Estudiante::query()
            ->where('anio_escolar', $asignacion->anio_escolar)
            ->where('nivel', $asignacion->nivel)
            ->where('sede', $asignacion->sede)
            ->where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->filter(fn (Estudiante $e) => $this->estudianteValidator->perteneceAAsignacion($e, $asignacion))
            ->values();
    }

    private function contextoDesdeAsignacion(
        DocenteCursoAula $asignacion,
        int $periodoAcademicoId,
        Collection $estudiantes,
    ): AulaEvaluacionContext {
        return new AulaEvaluacionContext(
            mallaCursoId: $asignacion->malla_curso_id,
            periodoAcademicoId: $periodoAcademicoId,
            sede: $asignacion->sede,
            grado: $asignacion->grado,
            seccion: $asignacion->seccion,
            estudianteIds: $estudiantes->pluck('id')->all(),
        );
    }

    private function validarPeriodoAsignacion(DocenteCursoAula $asignacion, int $periodoAcademicoId): PeriodoAcademico
    {
        $periodo = PeriodoAcademico::query()->findOrFail($periodoAcademicoId);

        if ($periodo->anio_escolar !== $asignacion->anio_escolar) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar de la asignación.'],
            ]);
        }

        return $periodo;
    }

    private function validarMallaCurso(int $mallaCursoId): MallaCurso
    {
        $mallaCurso = MallaCurso::query()
            ->with('cursoCatalogo')
            ->findOrFail($mallaCursoId);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        return $mallaCurso;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarComponente(\App\Models\Curricular\EvalBimComponente $c): array
    {
        return [
            'id' => $c->id,
            'tipo' => $c->tipo->value,
            'codigo' => $c->codigo,
            'nombre' => $c->nombre,
            'peso' => (float) $c->peso,
            'orden' => (int) $c->orden,
            'activo' => (bool) $c->activo,
            'editable_nombre' => $c->tipo === EvalBimComponenteTipo::Personalizado,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarEta(\App\Models\Curricular\EvalBimEtaItem $e): array
    {
        return [
            'id' => $e->id,
            'nombre' => $e->nombre,
            'peso_interno' => (float) $e->peso_interno,
            'orden' => (int) $e->orden,
            'activo' => (bool) $e->activo,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarResultado(EvalBimResultado $r): array
    {
        return [
            'estudiante_id' => $r->estudiante_id,
            'promedio_criterios' => $r->promedio_criterios !== null ? (float) $r->promedio_criterios : null,
            'oral' => $r->oral !== null ? (float) $r->oral : null,
            'promedio_eta' => $r->promedio_eta !== null ? (float) $r->promedio_eta : null,
            'examen_bimestral' => $r->examen_bimestral !== null ? (float) $r->examen_bimestral : null,
            'nivel_logro_numerico' => $r->nivel_logro_numerico !== null ? (float) $r->nivel_logro_numerico : null,
            'nivel_logro_literal' => $r->nivel_logro_literal,
            'conclusion_descriptiva' => $r->conclusion_descriptiva,
            'estado_calculo' => $r->estado_calculo->value,
            'detalle_json' => $r->detalle_json,
            'calculado_en' => $r->calculado_en?->toIso8601String(),
        ];
    }
}
