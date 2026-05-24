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
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
    ) {}

    /**
     * @return array{
     *     asignacion: DocenteCursoAula,
     *     curso: array{id: int, nombre: string, area: string|null},
     *     periodo: PeriodoAcademico,
     *     estudiantes: Collection<int, Estudiante>,
     *     pesos: array{cuaderno: float, libro: float, tarea: float},
     *     criterios: Collection<int, TemaSemanal>,
     *     notas_por_criterio: array<int, array<string, mixed>>,
     *     notas_por_estudiante_criterio: array<int, array<int, array<string, mixed>>>
     * }
     */
    public function construir(DocenteCursoAula $asignacion, int $periodoAcademicoId, ?int $estudianteId = null): array
    {
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
                'asignacion_docente_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        if ($asignacion->nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['No se registran notas semanales para nivel Inicial.'],
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
                    ->whereIn('estudiante_id', $estudiantes->pluck('id'))
                    ->whereIn('tema_semanal_id', $criterios->pluck('id'))
                    ->get();

                foreach ($notas as $nota) {
                    $notasPorEstudianteCriterio[$nota->estudiante_id][$nota->tema_semanal_id] = $this->serializarNota($nota);
                }
            }
        }

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);

        return [
            'asignacion' => $asignacion,
            'curso' => [
                'id' => $mallaCurso->id,
                'nombre' => $mallaCurso->cursoCatalogo?->nombre ?? '',
                'area' => $mallaCurso->area?->nombre,
                'area_id' => $mallaCurso->area_id,
            ],
            'periodo' => $periodo,
            'estudiantes' => $estudiantes,
            'pesos' => $pesos,
            'criterios' => $criterios,
            'notas_por_criterio' => $notasPorCriterio,
            'notas_por_estudiante_criterio' => $notasPorEstudianteCriterio,
        ];
    }

    /**
     * @return array{id: int, nota_cuaderno: mixed, nota_libro: mixed, nota_tarea: mixed, ce_calculado: mixed}
     */
    private function serializarNota(NotaSemanal $nota): array
    {
        return [
            'id' => $nota->id,
            'nota_cuaderno' => $nota->nota_cuaderno,
            'nota_libro' => $nota->nota_libro,
            'nota_tarea' => $nota->nota_tarea,
            'ce_calculado' => $nota->ce_calculado,
        ];
    }
}
