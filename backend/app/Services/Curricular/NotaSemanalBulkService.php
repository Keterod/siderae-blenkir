<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotaSemanalBulkService
{
    public const ADVERTENCIA_ELIMINAR_NOTA = 'Para eliminar una nota registrada se requiere una acción específica.';

    public function __construct(
        private readonly CeCalculatorService $ceCalculator = new CeCalculatorService,
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
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
                'notas' => ['Debe registrar al menos una nota (cuaderno, libro o tarea).'],
            ]);
        }

        $resultado = [];

        DB::transaction(function () use ($filasConNota, $asignacion, $tema, $docente, $contexto, &$resultado): void {
            foreach ($filasConNota as $indice => $fila) {
                $resultado[] = $this->persistirFila(
                    $docente,
                    $asignacion,
                    $tema,
                    $fila,
                    $contexto['pesos'],
                    "notas.{$indice}",
                );
            }
        });

        return ['notas' => $resultado, 'advertencias' => []];
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

        if ($asignacion->nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['No se registran notas semanales para nivel Inicial.'],
            ]);
        }

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);
        $this->pesoResolver->validarSuma100($pesos);

        $registrosConNota = $this->filtrarFilasConAlMenosUnaNota($registros);
        $advertencias = [];
        $resultado = [];

        if ($registrosConNota === [] && $registros !== []) {
            throw ValidationException::withMessages([
                'registros' => ['Debe registrar al menos una nota (cuaderno, libro o tarea) en algún criterio.'],
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

        DB::transaction(function () use ($registrosConNota, $asignacion, $estudiante, $docente, $pesos, $temas, &$resultado): void {
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
                    $pesos,
                    "registros.{$indice}",
                );
            }
        });

        return [
            'notas' => $resultado,
            'advertencias' => array_values(array_unique($advertencias)),
        ];
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

        if ($asignacion->nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['No se registran notas semanales para nivel Inicial.'],
            ]);
        }

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);
        $this->pesoResolver->validarSuma100($pesos);

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

                $filasParaPersistir[] = [
                    'estudiante_id' => $estudiante->id,
                    'tema_semanal_id' => $registro['tema_semanal_id'],
                    'nota_cuaderno' => $registro['nota_cuaderno'] ?? null,
                    'nota_libro' => $registro['nota_libro'] ?? null,
                    'nota_tarea' => $registro['nota_tarea'] ?? null,
                    'error_key' => "registros_por_estudiante.{$indiceBloque}.registros.{$indiceRegistro}",
                ];
            }
        }

        if ($filasParaPersistir === []) {
            throw ValidationException::withMessages([
                'registros_por_estudiante' => ['Debe registrar al menos una nota (cuaderno, libro o tarea) en algún criterio.'],
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

        DB::transaction(function () use ($filasParaPersistir, $asignacion, $docente, $pesos, $temas, &$resultado): void {
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
                    $pesos,
                    "{$fila['error_key']}.{$indice}",
                );
            }
        });

        return [
            'notas' => $resultado,
            'advertencias' => array_values(array_unique($advertencias)),
        ];
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

        if ($asignacion->nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['No se registran notas semanales para nivel Inicial.'],
            ]);
        }

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);
        $this->pesoResolver->validarSuma100($pesos);

        return ['mallaCurso' => $mallaCurso, 'pesos' => $pesos];
    }

    /**
     * @param  array{estudiante_id: int, nota_cuaderno?: mixed, nota_libro?: mixed, nota_tarea?: mixed}  $fila
     */
    private function persistirFila(
        User $docente,
        DocenteCursoAula $asignacion,
        TemaSemanal $tema,
        array $fila,
        array $pesos,
        string $errorKey,
    ): NotaSemanal {
        $estudiante = Estudiante::query()->findOrFail($fila['estudiante_id']);

        if (! $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion)) {
            throw ValidationException::withMessages([
                "{$errorKey}.estudiante_id" => ['El estudiante no pertenece a la asignación docente indicada.'],
            ]);
        }

        $cuaderno = array_key_exists('nota_cuaderno', $fila) ? $this->nullableFloat($fila['nota_cuaderno']) : null;
        $libro = array_key_exists('nota_libro', $fila) ? $this->nullableFloat($fila['nota_libro']) : null;
        $tarea = array_key_exists('nota_tarea', $fila) ? $this->nullableFloat($fila['nota_tarea']) : null;

        try {
            $ce = $this->ceCalculator->calcular($cuaderno, $libro, $tarea, $pesos);
        } catch (NotasCurricularesVaciasException) {
            throw ValidationException::withMessages([
                $errorKey => ['Debe registrar al menos una nota (cuaderno, libro o tarea).'],
            ]);
        } catch (NotaCurricularFueraDeRangoException) {
            throw ValidationException::withMessages([
                $errorKey => ['Las notas deben estar entre 0 y 20.'],
            ]);
        }

        return NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $docente->id,
                'nota_cuaderno' => $cuaderno,
                'nota_libro' => $libro,
                'nota_tarea' => $tarea,
                'ce_calculado' => $ce,
                'pesos_usados_json' => $pesos,
                'fecha_registro' => now()->toDateString(),
            ]
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

  /**
     * @param  array<string, mixed>  $fila
     */
    private function filaSinNotas(array $fila): bool
    {
        $cuaderno = array_key_exists('nota_cuaderno', $fila) ? $this->nullableFloat($fila['nota_cuaderno']) : null;
        $libro = array_key_exists('nota_libro', $fila) ? $this->nullableFloat($fila['nota_libro']) : null;
        $tarea = array_key_exists('nota_tarea', $fila) ? $this->nullableFloat($fila['nota_tarea']) : null;

        return $cuaderno === null && $libro === null && $tarea === null;
    }

    private function nullableFloat(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return (float) $valor;
    }
}
