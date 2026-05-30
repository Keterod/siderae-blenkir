<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class NotaSemanalFormularioService
{
    public function __construct(
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
        private readonly NotaSemanalCalificacionAdapter $calificacionAdapter = new NotaSemanalCalificacionAdapter,
    ) {}

    /**
     * @return array{
     *     asignacion: DocenteCursoAula|null,
     *     curso: array{id: int, nombre: string, area: string|null, area_id: int|null},
     *     periodo: PeriodoAcademico,
     *     estudiantes: Collection<int, Estudiante>,
     *     pesos: array{cuaderno: float, libro: float, tarea: float},
     *     criterios: Collection<int, TemaSemanal>,
     *     notas_por_criterio: array<int, array<string, mixed>>,
     *     notas_por_estudiante_criterio: array<int, array<int, array<string, mixed>>>,
     *     readonly: bool
     * }
     */
    public function construir(DocenteCursoAula $asignacion, int $periodoAcademicoId, ?int $estudianteId = null, bool $readonlyForzado = false): array
    {
        $ctxKey = $asignacion->exists ? 'asignacion_docente_id' : 'malla_curso_id';

        $periodo = PeriodoAcademico::query()->findOrFail($periodoAcademicoId);

        if ($periodo->anio_escolar !== $asignacion->anio_escolar) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar de la asignación.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with(['area', 'cursoCatalogo', 'mallaCurricular'])
            ->findOrFail($asignacion->malla_curso_id);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                $ctxKey => ['El curso de malla está inactivo.'],
            ]);
        }

        $criterios = TemaSemanal::query()
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->where('activo', true)
            ->with([
                'periodoAcademico',
                'semanaAcademica',
                'competencias',
                'capacidades',
            ])
            ->orderByRaw('semana_academica_id IS NULL')
            ->orderBy('semana_academica_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $estudiantes = Estudiante::query()
            ->where('anio_escolar', $asignacion->anio_escolar)
            ->where('nivel', $asignacion->nivel)
            ->where('seccion', $asignacion->seccion)
            ->where('sede', $asignacion->sede)
            ->where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->filter(fn (Estudiante $e) => $this->estudianteValidator->perteneceAAsignacion($e, $asignacion))
            ->values();

        if ($estudianteId !== null) {
            $estudiante = $estudiantes->firstWhere('id', $estudianteId);
            if ($estudiante === null) {
                throw ValidationException::withMessages([
                    'estudiante_id' => ['El estudiante no pertenece a la asignación docente indicada.'],
                ]);
            }
        }

        $notasPorCriterio = [];
        $notasPorEstudianteCriterio = [];

        if ($criterios->isNotEmpty()) {
            if ($estudianteId !== null) {
                $notas = NotaSemanal::query()
                    ->with('notasComponentes')
                    ->where('estudiante_id', $estudianteId)
                    ->whereIn('tema_semanal_id', $criterios->pluck('id'))
                    ->get()
                    ->keyBy('tema_semanal_id');

                foreach ($criterios as $criterio) {
                    $nota = $notas->get($criterio->id);
                    if ($nota !== null) {
                        $notasPorCriterio[$criterio->id] = $this->serializarNota($nota);
                    }
                }
            } elseif ($estudiantes->isNotEmpty()) {
                $notas = NotaSemanal::query()
                    ->with('notasComponentes')
                    ->whereIn('estudiante_id', $estudiantes->pluck('id'))
                    ->whereIn('tema_semanal_id', $criterios->pluck('id'))
                    ->get();

                foreach ($notas as $nota) {
                    $notasPorEstudianteCriterio[$nota->estudiante_id][$nota->tema_semanal_id] = $this->serializarNota($nota);
                }
            }
        }

        $contextoCalificacion = $this->calificacionAdapter->contextoFormulario($mallaCurso, $asignacion);

        return [
            'asignacion' => $asignacion->exists ? $asignacion : null,
            'curso' => [
                'id' => $mallaCurso->id,
                'nombre' => $mallaCurso->cursoCatalogo?->nombre ?? '',
                'area' => $mallaCurso->area?->nombre,
                'area_id' => $mallaCurso->area_id,
            ],
            'periodo' => $periodo,
            'estudiantes' => $estudiantes,
            'pesos' => $contextoCalificacion['pesos'],
            'componentes_calificacion' => $contextoCalificacion['componentes_calificacion'],
            'calificacion_dinamica_disponible' => $contextoCalificacion['calificacion_dinamica_disponible'],
            'nivel' => $contextoCalificacion['nivel'],
            'anio_escolar' => $contextoCalificacion['anio_escolar'],
            'criterios' => $criterios,
            'notas_por_criterio' => $notasPorCriterio,
            'notas_por_estudiante_criterio' => $notasPorEstudianteCriterio,
            'readonly' => $readonlyForzado,
        ];
    }

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     sede: string,
     *     grado: string,
     *     seccion: string,
     *     malla_curso_id: int,
     *     periodo_academico_id: int,
     *     area_id?: string|int|null,
     *     estudiante_id?: int|null
     * } $filtros
     * @return array<string, mixed>
     */
    public function construirConsultaGlobal(array $filtros): array
    {
        $periodo = PeriodoAcademico::query()->findOrFail($filtros['periodo_academico_id']);

        if ($periodo->anio_escolar !== $filtros['anio_escolar']) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar indicado.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with(['area', 'cursoCatalogo', 'mallaCurricular'])
            ->findOrFail($filtros['malla_curso_id']);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $areaIdFiltrado = $filtros['area_id'] ?? null;
        if ($areaIdFiltrado !== null && $areaIdFiltrado !== '') {
            if ((int) $mallaCurso->area_id !== (int) $areaIdFiltrado) {
                throw ValidationException::withMessages([
                    'area_id' => ['El área no corresponde al curso seleccionado.'],
                ]);
            }
        }

        $existeAulaActiva = DocenteCursoAula::query()
            ->where('activo', true)
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('anio_escolar', $filtros['anio_escolar'])
            ->where('nivel', $filtros['nivel'])
            ->where('grado', $filtros['grado'])
            ->where('seccion', $filtros['seccion'])
            ->where('sede', $filtros['sede'])
            ->exists();

        if (! $existeAulaActiva) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['No hay una asignación docente activa para este curso y aula.'],
            ]);
        }

        $virtual = new DocenteCursoAula([
            'user_id' => 0,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => $filtros['anio_escolar'],
            'nivel' => $filtros['nivel'],
            'grado' => $filtros['grado'],
            'seccion' => $filtros['seccion'],
            'sede' => $filtros['sede'],
            'activo' => true,
        ]);

        $virtual->exists = false;
        $virtual->id = null;
        $virtual->setRelation('mallaCurso', $mallaCurso);

        return $this->construir(
            $virtual,
            (int) $filtros['periodo_academico_id'],
            isset($filtros['estudiante_id']) ? (int) $filtros['estudiante_id'] : null,
            true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarNota(NotaSemanal $nota): array
    {
        return $this->calificacionAdapter->serializarNota($nota);
    }
}
