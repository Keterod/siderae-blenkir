<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;
use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\NotaSemanalComponente;
use App\Models\Curricular\TemaSemanal;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class NotaSemanalCalificacionAdapter
{
    public const MODELO_LEGACY = 'legacy';

    public const MODELO_DINAMICO = 'dinamico';

    public const SNAPSHOT_MODELO_DINAMICO = 'dinamico_v1';

    public function __construct(
        private readonly CeCalculatorService $ceCalculator = new CeCalculatorService,
        private readonly CeComponentesDinamicosService $ceDinamico = new CeComponentesDinamicosService,
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
        private readonly ComponenteCalificacionNivelService $componenteService = new ComponenteCalificacionNivelService,
    ) {}

    /**
     * @return array{
     *     pesos: array{cuaderno: float, libro: float, tarea: float},
     *     componentes_calificacion: list<array<string, mixed>>,
     *     calificacion_dinamica_disponible: bool,
     *     nivel: string,
     *     anio_escolar: string
     * }
     */
    public function contextoFormulario(MallaCurso $mallaCurso, DocenteCursoAula $asignacion): array
    {
        $malla = $mallaCurso->mallaCurricular;
        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $malla);
        $componentesActivos = $this->componenteService->listar($asignacion->anio_escolar, $asignacion->nivel, true);
        $evaluacion = $this->componenteService->evaluarSumaActivos($asignacion->anio_escolar, $asignacion->nivel);

        return [
            'pesos' => $pesos,
            'componentes_calificacion' => $componentesActivos
                ->map(fn (ComponenteCalificacionNivel $c) => $this->serializarComponenteConfig($c))
                ->values()
                ->all(),
            'calificacion_dinamica_disponible' => $evaluacion['valido'] && $componentesActivos->isNotEmpty(),
            'nivel' => $asignacion->nivel,
            'anio_escolar' => $asignacion->anio_escolar,
        ];
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    public function detectarModoFila(array $fila, string $errorKey = 'notas'): string
    {
        $tieneDinamico = $this->filaTieneNotasComponentes($fila);
        $tieneLegacy = $this->filaTieneNotasLegacy($fila);

        if ($tieneDinamico && $tieneLegacy) {
            throw ValidationException::withMessages([
                $errorKey => ['No puede mezclar notas legacy (cuaderno/libro/tarea) con notas_componentes en el mismo registro.'],
            ]);
        }

        if ($tieneDinamico) {
            return self::MODELO_DINAMICO;
        }

        return self::MODELO_LEGACY;
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    public function filaSinNotas(array $fila): bool
    {
        return ! $this->filaTieneNotasLegacy($fila) && ! $this->filaTieneNotasComponentes($fila);
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    public function persistirFila(
        User $docente,
        DocenteCursoAula $asignacion,
        TemaSemanal $tema,
        array $fila,
        string $errorKey,
    ): NotaSemanal {
        $modo = $this->detectarModoFila($fila, $errorKey);

        if ($modo === self::MODELO_DINAMICO) {
            return $this->persistirFilaDinamica($docente, $asignacion, $tema, $fila, $errorKey);
        }

        return $this->persistirFilaLegacy($docente, $asignacion, $tema, $fila, $errorKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializarNota(NotaSemanal $nota): array
    {
        $nota->loadMissing('notasComponentes');

        $payload = [
            'id' => $nota->id,
            'modelo_calificacion' => $nota->modelo_calificacion ?? self::MODELO_LEGACY,
            'nota_cuaderno' => $nota->nota_cuaderno,
            'nota_libro' => $nota->nota_libro,
            'nota_tarea' => $nota->nota_tarea,
            'ce_calculado' => $nota->ce_calculado,
            'notas_componentes' => [],
        ];

        if (($nota->modelo_calificacion ?? self::MODELO_LEGACY) === self::MODELO_DINAMICO) {
            $payload['notas_componentes'] = $nota->notasComponentes
                ->sortBy('orden_snapshot')
                ->map(fn (NotaSemanalComponente $nc) => [
                    'componente_id' => $nc->componente_calificacion_nivel_id,
                    'codigo' => $nc->codigo_componente_snapshot,
                    'nombre' => $nc->nombre_componente_snapshot,
                    'nota' => $nc->nota,
                    'peso_usado' => $nc->peso_usado,
                    'orden' => $nc->orden_snapshot,
                ])
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    private function persistirFilaLegacy(
        User $docente,
        DocenteCursoAula $asignacion,
        TemaSemanal $tema,
        array $fila,
        string $errorKey,
    ): NotaSemanal {
        $mallaCurso = MallaCurso::query()
            ->with('mallaCurricular')
            ->findOrFail($asignacion->malla_curso_id);

        $pesos = $this->pesoResolver->resolverParaCurso($mallaCurso, $mallaCurso->mallaCurricular);
        $this->pesoResolver->validarSuma100($pesos);

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

        $nota = NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $fila['estudiante_id'],
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $docente->id,
                'modelo_calificacion' => self::MODELO_LEGACY,
                'nota_cuaderno' => $cuaderno,
                'nota_libro' => $libro,
                'nota_tarea' => $tarea,
                'ce_calculado' => $ce,
                'pesos_usados_json' => $pesos,
                'fecha_registro' => now()->toDateString(),
            ]
        );

        NotaSemanalComponente::query()->where('nota_semanal_id', $nota->id)->delete();

        return $nota->fresh(['notasComponentes']);
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    private function persistirFilaDinamica(
        User $docente,
        DocenteCursoAula $asignacion,
        TemaSemanal $tema,
        array $fila,
        string $errorKey,
    ): NotaSemanal {
        $componentesActivos = $this->componentesActivosValidos($asignacion->anio_escolar, $asignacion->nivel);

        if ($componentesActivos->isEmpty()) {
            throw ValidationException::withMessages([
                $errorKey => ['No hay configuración dinámica de componentes válida para este año y nivel.'],
            ]);
        }

        $this->componenteService->validarSumaActivos($asignacion->anio_escolar, $asignacion->nivel);

        $porId = $componentesActivos->keyBy('id');
        $entradaPorId = $this->normalizarNotasComponentesEntrada($fila['notas_componentes'] ?? [], $errorKey);

        if ($entradaPorId === []) {
            throw ValidationException::withMessages([
                $errorKey => ['Debe registrar al menos una nota en los componentes de calificación.'],
            ]);
        }

        $presentes = [];
        foreach ($entradaPorId as $componenteId => $valorNota) {
            /** @var ComponenteCalificacionNivel|null $config */
            $config = $porId->get($componenteId);
            if ($config === null) {
                throw ValidationException::withMessages([
                    "{$errorKey}.notas_componentes" => ['Uno o más componentes no pertenecen al año y nivel de la asignación, o están inactivos.'],
                ]);
            }

            $presentes[] = [
                'config' => $config,
                'nota' => $valorNota,
                'peso' => (float) $config->peso,
            ];
        }

        try {
            $ce = $this->ceDinamico->calcular(array_map(
                fn (array $item) => ['nota' => $item['nota'], 'peso' => $item['peso']],
                $presentes,
            ));
        } catch (NotasCurricularesVaciasException) {
            throw ValidationException::withMessages([
                $errorKey => ['Debe registrar al menos una nota en los componentes de calificación.'],
            ]);
        } catch (NotaCurricularFueraDeRangoException) {
            throw ValidationException::withMessages([
                $errorKey => ['Las notas deben estar entre 0 y 20.'],
            ]);
        }

        $snapshot = [
            'modelo' => self::SNAPSHOT_MODELO_DINAMICO,
            'componentes' => $componentesActivos->map(fn (ComponenteCalificacionNivel $c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'peso' => (float) $c->peso,
                'orden' => (int) $c->orden,
            ])->values()->all(),
        ];

        $nota = NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $fila['estudiante_id'],
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $docente->id,
                'modelo_calificacion' => self::MODELO_DINAMICO,
                'nota_cuaderno' => null,
                'nota_libro' => null,
                'nota_tarea' => null,
                'ce_calculado' => $ce,
                'pesos_usados_json' => $snapshot,
                'fecha_registro' => now()->toDateString(),
            ]
        );

        NotaSemanalComponente::query()->where('nota_semanal_id', $nota->id)->delete();

        foreach ($presentes as $item) {
            /** @var ComponenteCalificacionNivel $config */
            $config = $item['config'];
            NotaSemanalComponente::query()->create([
                'nota_semanal_id' => $nota->id,
                'componente_calificacion_nivel_id' => $config->id,
                'nota' => $item['nota'],
                'peso_usado' => (float) $config->peso,
                'nombre_componente_snapshot' => $config->nombre,
                'codigo_componente_snapshot' => $config->codigo,
                'orden_snapshot' => (int) $config->orden,
            ]);
        }

        return $nota->fresh(['notasComponentes']);
    }

    /**
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    private function componentesActivosValidos(string $anioEscolar, string $nivel): Collection
    {
        $componentes = $this->componenteService->listar($anioEscolar, $nivel, true);

        if ($componentes->isEmpty()) {
            return $componentes;
        }

        $evaluacion = $this->componenteService->evaluarSumaActivos($anioEscolar, $nivel);

        if (! $evaluacion['valido']) {
            return collect();
        }

        return $componentes;
    }

    /**
     * @param  list<array{componente_id?: int|string, nota?: mixed}>|array<int|string, mixed>  $entrada
     * @return array<int, float>
     */
    private function normalizarNotasComponentesEntrada(array $entrada, string $errorKey): array
    {
        $resultado = [];

        foreach ($entrada as $indice => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    "{$errorKey}.notas_componentes.{$indice}" => ['Formato de componente inválido.'],
                ]);
            }

            if (! isset($item['componente_id'])) {
                throw ValidationException::withMessages([
                    "{$errorKey}.notas_componentes.{$indice}.componente_id" => ['Debe indicar el componente.'],
                ]);
            }

            if (! array_key_exists('nota', $item) || $item['nota'] === null || $item['nota'] === '') {
                continue;
            }

            $resultado[(int) $item['componente_id']] = (float) $item['nota'];
        }

        return $resultado;
    }

    /**
     * @return array{id: int, codigo: string, nombre: string, peso: float, orden: int}
     */
    private function serializarComponenteConfig(ComponenteCalificacionNivel $componente): array
    {
        return [
            'id' => $componente->id,
            'codigo' => $componente->codigo,
            'nombre' => $componente->nombre,
            'peso' => (float) $componente->peso,
            'orden' => (int) $componente->orden,
        ];
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    private function filaTieneNotasLegacy(array $fila): bool
    {
        foreach (['nota_cuaderno', 'nota_libro', 'nota_tarea'] as $campo) {
            if (! array_key_exists($campo, $fila)) {
                continue;
            }
            if ($this->nullableFloat($fila[$campo]) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    private function filaTieneNotasComponentes(array $fila): bool
    {
        if (! isset($fila['notas_componentes']) || ! is_array($fila['notas_componentes'])) {
            return false;
        }

        foreach ($fila['notas_componentes'] as $item) {
            if (! is_array($item)) {
                continue;
            }
            if (array_key_exists('nota', $item) && $item['nota'] !== null && $item['nota'] !== '') {
                return true;
            }
        }

        return false;
    }

    private function nullableFloat(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return (float) $valor;
    }
}
