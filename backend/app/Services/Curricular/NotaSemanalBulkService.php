<?php

namespace App\Services\Curricular;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\EvaluacionBimestral\EvalBimResultadoPersistService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionComponentesResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotaSemanalBulkService
{
    public const ADVERTENCIA_ELIMINAR_NOTA = 'Para eliminar una nota registrada se requiere una acción específica.';

    public const ADVERTENCIA_EVAL_BIM_NO_ACTUALIZADA = 'No se pudo actualizar la evaluación bimestral tras guardar las notas.';

    public function __construct(
        private readonly NotaSemanalCalificacionAdapter $calificacionAdapter = new NotaSemanalCalificacionAdapter,
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
        private readonly EvalBimResultadoPersistService $resultadoPersistService = new EvalBimResultadoPersistService,
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
    ) {}

    /**
     * @param  list<array{estudiante_id: int, nota_cuaderno?: float|null, nota_libro?: float|null, nota_tarea?: float|null}>  $filas
     * @return array{notas: list<NotaSemanal>, advertencias: list<string>}
     */
    public function registrarPorTema(User $docente, DocenteCursoAula $asignacion, TemaSemanal $tema, array $filas): array
    {
        $contexto = $this->validarContexto($docente, $asignacion, $tema);
        $filasConNota = $this->filtrarFilasConAlMenosUnaNota($filas);

        if ($filasConNota === []) {
            throw ValidationException::withMessages([
                'notas' => ['Debe registrar al menos una nota (cuaderno, libro, tarea o componentes de calificación).'],
            ]);
        }

        $resultado = [];

        DB::transaction(function () use ($filasConNota, $asignacion, $tema, $docente, &$resultado): void {
            foreach ($filasConNota as $indice => $fila) {
                $resultado[] = $this->persistirFila(
                    $docente,
                    $asignacion,
                    $tema,
                    $fila,
                    "notas.{$indice}",
                );
            }
        });

        return $this->finalizarConRecalculoEvalBimestral($asignacion, $resultado, []);
    }

    /**
     * @param  list<array{tema_semanal_id: int, nota_cuaderno?: float|null, nota_libro?: float|null, nota_tarea?: float|null}>  $registros
     * @return array{notas: list<NotaSemanal>, advertencias: list<string>}
     */
    public function registrarPorEstudiante(User $docente, DocenteCursoAula $asignacion, Estudiante $estudiante, array $registros): array
    {
        if ($asignacion->user_id !== $docente->id) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['La asignación no pertenece al docente autenticado.'],
            ]);
        }

        if (! $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion)) {
            throw ValidationException::withMessages([
                'estudiante_id' => ['El estudiante no pertenece a la asignación docente indicada.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with('mallaCurricular')
            ->findOrFail($asignacion->malla_curso_id);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $registrosConNota = $this->filtrarFilasConAlMenosUnaNota($registros);
        $advertencias = [];
        $resultado = [];

        if ($registrosConNota === [] && $registros !== []) {
            throw ValidationException::withMessages([
                'registros' => ['Debe registrar al menos una nota (cuaderno, libro, tarea o componentes de calificación) en algún criterio.'],
            ]);
        }

        if ($registrosConNota === []) {
            return ['notas' => [], 'advertencias' => []];
        }

        $temaIds = array_column($registrosConNota, 'tema_semanal_id');
        $temas = TemaSemanal::query()
            ->whereIn('id', $temaIds)
            ->get()
            ->keyBy('id');

        $notasExistentes = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereIn('tema_semanal_id', $temaIds)
            ->get()
            ->keyBy('tema_semanal_id');

        foreach ($registros as $registro) {
            if ($this->filaSinNotas($registro) && $notasExistentes->has($registro['tema_semanal_id'])) {
                $advertencias[] = self::ADVERTENCIA_ELIMINAR_NOTA;
            }
        }

        DB::transaction(function () use ($registrosConNota, $asignacion, $estudiante, $docente, $temas, &$resultado): void {
            foreach ($registrosConNota as $indice => $registro) {
                $tema = $temas->get($registro['tema_semanal_id']);
                if ($tema === null) {
                    throw ValidationException::withMessages([
                        "registros.{$indice}.tema_semanal_id" => ['Criterio no encontrado.'],
                    ]);
                }

                if ($tema->malla_curso_id !== $asignacion->malla_curso_id) {
                    throw ValidationException::withMessages([
                        "registros.{$indice}.tema_semanal_id" => ['El criterio no corresponde al curso de la asignación.'],
                    ]);
                }

                if (! $tema->activo) {
                    throw ValidationException::withMessages([
                        "registros.{$indice}.tema_semanal_id" => ['El criterio está inactivo.'],
                    ]);
                }

                $fila = array_merge($registro, ['estudiante_id' => $estudiante->id]);
                $resultado[] = $this->persistirFila(
                    $docente,
                    $asignacion,
                    $tema,
                    $fila,
                    "registros.{$indice}",
                );
            }
        });

        return $this->finalizarConRecalculoEvalBimestral($asignacion, $resultado, $advertencias);
    }

    /**
     * @param  list<array{estudiante_id: int, registros: list<array{tema_semanal_id: int, nota_cuaderno?: float|null, nota_libro?: float|null, nota_tarea?: float|null}>}>  $bloques
     * @return array{notas: list<NotaSemanal>, advertencias: list<string>}
     */
    public function registrarPorVariosEstudiantes(User $docente, DocenteCursoAula $asignacion, array $bloques): array
    {
        if ($asignacion->user_id !== $docente->id) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['La asignación no pertenece al docente autenticado.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with('mallaCurricular')
            ->findOrFail($asignacion->malla_curso_id);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $filasParaPersistir = [];
        $advertencias = [];
        $temaIds = [];
        $estudianteIds = [];

        foreach ($bloques as $indiceBloque => $bloque) {
            $estudiante = Estudiante::query()->findOrFail($bloque['estudiante_id']);

            if (! $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion)) {
                throw ValidationException::withMessages([
                    "registros_por_estudiante.{$indiceBloque}.estudiante_id" => ['El estudiante no pertenece a la asignación docente indicada.'],
                ]);
            }

            $estudianteIds[] = $estudiante->id;
            $registros = $bloque['registros'] ?? [];

            foreach ($registros as $indiceRegistro => $registro) {
                if (isset($registro['tema_semanal_id'])) {
                    $temaIds[] = $registro['tema_semanal_id'];
                }

                if ($this->filaSinNotas($registro)) {
                    continue;
                }

                $filasParaPersistir[] = array_merge(
                    [
                        'estudiante_id' => $estudiante->id,
                        'tema_semanal_id' => $registro['tema_semanal_id'],
                        'nota_cuaderno' => $registro['nota_cuaderno'] ?? null,
                        'nota_libro' => $registro['nota_libro'] ?? null,
                        'nota_tarea' => $registro['nota_tarea'] ?? null,
                        'error_key' => "registros_por_estudiante.{$indiceBloque}.registros.{$indiceRegistro}",
                    ],
                    isset($registro['notas_componentes']) ? ['notas_componentes' => $registro['notas_componentes']] : [],
                );
            }
        }

        if ($filasParaPersistir === []) {
            throw ValidationException::withMessages([
                'registros_por_estudiante' => ['Debe registrar al menos una nota (cuaderno, libro, tarea o componentes de calificación) en algún criterio.'],
            ]);
        }

        $temaIds = array_values(array_unique($temaIds));
        $estudianteIds = array_values(array_unique($estudianteIds));

        $temas = TemaSemanal::query()
            ->whereIn('id', $temaIds)
            ->get()
            ->keyBy('id');

        $notasExistentes = NotaSemanal::query()
            ->whereIn('estudiante_id', $estudianteIds)
            ->whereIn('tema_semanal_id', $temaIds)
            ->get()
            ->keyBy(fn (NotaSemanal $n) => "{$n->estudiante_id}:{$n->tema_semanal_id}");

        foreach ($bloques as $indiceBloque => $bloque) {
            foreach ($bloque['registros'] ?? [] as $registro) {
                if (! $this->filaSinNotas($registro)) {
                    continue;
                }
                $clave = "{$bloque['estudiante_id']}:{$registro['tema_semanal_id']}";
                if ($notasExistentes->has($clave)) {
                    $advertencias[] = self::ADVERTENCIA_ELIMINAR_NOTA;
                }
            }
        }

        $resultado = [];

        DB::transaction(function () use ($filasParaPersistir, $asignacion, $docente, $temas, &$resultado): void {
            foreach ($filasParaPersistir as $indice => $fila) {
                $tema = $temas->get($fila['tema_semanal_id']);
                if ($tema === null) {
                    throw ValidationException::withMessages([
                        "{$fila['error_key']}.{$indice}.tema_semanal_id" => ['Criterio no encontrado.'],
                    ]);
                }

                if ($tema->malla_curso_id !== $asignacion->malla_curso_id) {
                    throw ValidationException::withMessages([
                        "{$fila['error_key']}.{$indice}.tema_semanal_id" => ['El criterio no corresponde al curso de la asignación.'],
                    ]);
                }

                if (! $tema->activo) {
                    throw ValidationException::withMessages([
                        "{$fila['error_key']}.{$indice}.tema_semanal_id" => ['El criterio está inactivo.'],
                    ]);
                }

                $resultado[] = $this->persistirFila(
                    $docente,
                    $asignacion,
                    $tema,
                    $fila,
                    "{$fila['error_key']}.{$indice}",
                );
            }
        });

        return $this->finalizarConRecalculoEvalBimestral($asignacion, $resultado, $advertencias);
    }

    /**
     * @param  list<NotaSemanal>  $notas
     * @param  list<string>  $advertencias
     * @return array{notas: list<NotaSemanal>, advertencias: list<string>}
     */
    private function finalizarConRecalculoEvalBimestral(
        DocenteCursoAula $asignacion,
        array $notas,
        array $advertencias,
    ): array {
        $advertenciasEval = $this->recalcularEvaluacionBimestralTrasNotasSemanales($asignacion, $notas);

        return [
            'notas' => $notas,
            'advertencias' => array_values(array_unique(array_merge($advertencias, $advertenciasEval))),
        ];
    }

    /**
     * @param  list<NotaSemanal>  $notasGuardadas
     * @return list<string>
     */
    private function recalcularEvaluacionBimestralTrasNotasSemanales(
        DocenteCursoAula $asignacion,
        array $notasGuardadas,
    ): array {
        if ($notasGuardadas === []) {
            return [];
        }

        $notasConCe = array_values(array_filter(
            $notasGuardadas,
            fn (NotaSemanal $nota) => $nota->ce_calculado !== null,
        ));

        if ($notasConCe === []) {
            return [];
        }

        $temaIds = array_values(array_unique(array_map(
            fn (NotaSemanal $nota) => (int) $nota->tema_semanal_id,
            $notasConCe,
        )));

        $periodoIds = TemaSemanal::query()
            ->whereIn('id', $temaIds)
            ->pluck('periodo_academico_id')
            ->unique()
            ->filter();

        if ($periodoIds->isEmpty()) {
            return [self::ADVERTENCIA_EVAL_BIM_NO_ACTUALIZADA];
        }

        $advertencias = [];

        foreach ($periodoIds as $periodoAcademicoId) {
            try {
                $aula = $this->construirContextoAulaEvaluacion($asignacion, (int) $periodoAcademicoId);
                if ($aula === null) {
                    $advertencias[] = self::ADVERTENCIA_EVAL_BIM_NO_ACTUALIZADA;

                    continue;
                }

                $this->componentesResolver->resolver($aula->mallaCursoId, $aula->periodoAcademicoId);
                $this->resultadoPersistService->recalcularAula($aula);
            } catch (Throwable $exception) {
                Log::warning('No se pudo recalcular evaluación bimestral tras notas semanales.', [
                    'asignacion_docente_id' => $asignacion->id,
                    'periodo_academico_id' => $periodoAcademicoId,
                    'mensaje' => $exception->getMessage(),
                ]);
                $advertencias[] = self::ADVERTENCIA_EVAL_BIM_NO_ACTUALIZADA;
            }
        }

        return array_values(array_unique($advertencias));
    }

    private function construirContextoAulaEvaluacion(
        DocenteCursoAula $asignacion,
        int $periodoAcademicoId,
    ): ?AulaEvaluacionContext {
        $estudiantes = Estudiante::query()
            ->where('anio_escolar', $asignacion->anio_escolar)
            ->where('nivel', $asignacion->nivel)
            ->where('sede', $asignacion->sede)
            ->where('activo', true)
            ->get()
            ->filter(fn (Estudiante $estudiante) => $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion))
            ->values();

        if ($estudiantes->isEmpty()) {
            return null;
        }

        return new AulaEvaluacionContext(
            mallaCursoId: $asignacion->malla_curso_id,
            periodoAcademicoId: $periodoAcademicoId,
            sede: $asignacion->sede,
            grado: $asignacion->grado,
            seccion: $asignacion->seccion,
            estudianteIds: $estudiantes->pluck('id')->all(),
        );
    }

    /**
     * @return array{mallaCurso: MallaCurso, pesos: array{cuaderno: float, libro: float, tarea: float}}
     */
    private function validarContexto(User $docente, DocenteCursoAula $asignacion, TemaSemanal $tema): array
    {
        if ($asignacion->user_id !== $docente->id) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['La asignación no pertenece al docente autenticado.'],
            ]);
        }

        if ($asignacion->malla_curso_id !== $tema->malla_curso_id) {
            throw ValidationException::withMessages([
                'tema_semanal_id' => ['El tema no corresponde al curso de la asignación.'],
            ]);
        }

        if (! $tema->activo) {
            throw ValidationException::withMessages([
                'tema_semanal_id' => ['El tema semanal está inactivo.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()
            ->with('mallaCurricular')
            ->findOrFail($asignacion->malla_curso_id);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);
        $this->pesoResolver->validarSuma100($pesos);

        return ['mallaCurso' => $mallaCurso, 'pesos' => $pesos];
    }

    /**
     * @param  array{estudiante_id: int, nota_cuaderno?: mixed, nota_libro?: mixed, nota_tarea?: mixed, notas_componentes?: list<array<string, mixed>>}  $fila
     */
    private function persistirFila(
        User $docente,
        DocenteCursoAula $asignacion,
        TemaSemanal $tema,
        array $fila,
        string $errorKey,
    ): NotaSemanal {
        $estudiante = Estudiante::query()->findOrFail($fila['estudiante_id']);

        if (! $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion)) {
            throw ValidationException::withMessages([
                "{$errorKey}.estudiante_id" => ['El estudiante no pertenece a la asignación docente indicada.'],
            ]);
        }

        return $this->calificacionAdapter->persistirFila(
            $docente,
            $asignacion,
            $tema,
            $fila,
            $errorKey,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $filas
     * @return list<array<string, mixed>>
     */
    private function filtrarFilasConAlMenosUnaNota(array $filas): array
    {
        return array_values(array_filter($filas, fn (array $fila): bool => ! $this->filaSinNotas($fila)));
    }

    private function filaSinNotas(array $fila): bool
    {
        return $this->calificacionAdapter->filaSinNotas($fila);
    }
}
