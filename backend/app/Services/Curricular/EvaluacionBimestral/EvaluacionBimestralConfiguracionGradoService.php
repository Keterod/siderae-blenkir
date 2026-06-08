<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Exceptions\Curricular\PesosEvaluacionBimestralInvalidosException;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Services\Curricular\MallaCurricularService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluacionBimestralConfiguracionGradoService
{
    /** @var list<string> */
    private const CODIGOS_SISTEMA = [
        'promedio_criterios',
        'oral',
        'promedio_eta',
        'examen_bimestral',
    ];

    public function __construct(
        private readonly MallaCurricularService $mallaService = new MallaCurricularService,
        private readonly EvaluacionBimestralConfiguracionService $configuracionService = new EvaluacionBimestralConfiguracionService,
        private readonly PesosComponentesService $pesosComponentesService = new PesosComponentesService,
        private readonly PesosEtaInternosService $pesosEtaInternosService = new PesosEtaInternosService,
    ) {}

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     grado: string,
     *     periodo_academico_id: int,
     *     plantilla: array{
     *         componentes: list<array{codigo: string, nombre: string, peso: float|int|string, activo: bool, orden: int}>,
     *         etas: list<array{nombre: string, peso_interno: float|int|string, activo: bool, orden: int}>
     *     }
     * }  $datos
     * @return array{
     *     cursos_afectados: list<array{malla_curso_id: int, curso: string, area: string|null}>,
     *     total_afectados: int,
     *     cursos_omitidos: list<array{malla_curso_id: int, curso: string, motivo: string}>
     * }
     */
    public function aplicar(array $datos): array
    {
        $anioEscolar = trim($datos['anio_escolar']);
        $nivel = $datos['nivel'];
        $grado = $datos['grado'];
        $periodoId = (int) $datos['periodo_academico_id'];
        $plantilla = $datos['plantilla'];

        $this->validarPeriodoAcademico($anioEscolar, $periodoId);
        $this->validarPlantillaCoherente($plantilla);

        $malla = $this->mallaService->obtenerOProvisionar($anioEscolar, $nivel, $grado);

        $cursosActivos = $malla->mallaCursos
            ->filter(fn (MallaCurso $c) => $c->activo)
            ->sortBy('orden')
            ->values();

        if ($cursosActivos->isEmpty()) {
            throw ValidationException::withMessages([
                'grado' => ['No hay cursos activos en la malla del grado indicado.'],
            ]);
        }

        $inactivos = $malla->mallaCursos
            ->filter(fn (MallaCurso $c) => ! $c->activo)
            ->values();

        $cursosAfectados = [];

        DB::transaction(function () use ($cursosActivos, $periodoId, $plantilla, &$cursosAfectados): void {
            foreach ($cursosActivos as $mallaCurso) {
                try {
                    $this->aplicarPlantillaACurso($mallaCurso->id, $periodoId, $plantilla);
                } catch (PesosEvaluacionBimestralInvalidosException $e) {
                    throw ValidationException::withMessages([
                        'plantilla' => [
                            sprintf(
                                'Error al aplicar en el curso «%s»: %s',
                                $this->nombreCurso($mallaCurso),
                                $e->getMessage(),
                            ),
                        ],
                    ]);
                }

                $cursosAfectados[] = [
                    'malla_curso_id' => $mallaCurso->id,
                    'curso' => $this->nombreCurso($mallaCurso),
                    'area' => $mallaCurso->area?->nombre,
                ];
            }
        });

        return [
            'cursos_afectados' => $cursosAfectados,
            'total_afectados' => count($cursosAfectados),
            'cursos_omitidos' => $inactivos->map(fn (MallaCurso $c) => [
                'malla_curso_id' => $c->id,
                'curso' => $this->nombreCurso($c),
                'motivo' => 'Curso inactivo en la malla.',
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{
     *     componentes: list<array{codigo: string, nombre: string, peso: float|int|string, activo: bool, orden: int}>,
     *     etas: list<array{nombre: string, peso_interno: float|int|string, activo: bool, orden: int}>
     * }  $plantilla
     */
    public function aplicarPlantillaACurso(int $mallaCursoId, int $periodoId, array $plantilla): void
    {
        $this->configuracionService->asegurarConfiguracionPorDefecto($mallaCursoId, $periodoId);

        $componentesDb = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->get();

        foreach ($plantilla['componentes'] as $item) {
            $codigo = $item['codigo'];
            $activo = (bool) $item['activo'];
            $peso = (float) $item['peso'];
            $orden = (int) $item['orden'];
            $nombre = trim($item['nombre']);

            if ($this->esCodigoSistema($codigo)) {
                /** @var EvalBimComponente|null $componente */
                $componente = $componentesDb->firstWhere('codigo', $codigo);
                if ($componente === null) {
                    throw ValidationException::withMessages([
                        'plantilla' => [sprintf('Falta el componente de sistema «%s» en el curso.', $codigo)],
                    ]);
                }

                $componente->nombre = $nombre;
                $componente->peso = $activo ? $peso : 0;
                $componente->orden = $orden;
                $componente->activo = $activo;
                $componente->save();

                continue;
            }

            $componente = $this->resolverPersonalizado($componentesDb, $mallaCursoId, $periodoId, $nombre, $codigo);
            $componente->nombre = $nombre;
            $componente->peso = $activo ? $peso : 0;
            $componente->orden = $orden;
            $componente->activo = $activo;
            $componente->save();
        }

        $personalizadosDb = $componentesDb->filter(
            fn (EvalBimComponente $c) => $c->tipo === EvalBimComponenteTipo::Personalizado,
        );

        foreach ($personalizadosDb as $personalizado) {
            $norm = $this->normalizarNombre($personalizado->nombre);
            $enPlantilla = collect($plantilla['componentes'])->contains(
                fn (array $p) => ! $this->esCodigoSistema($p['codigo'])
                    && $this->normalizarNombre($p['nombre']) === $norm,
            );

            if (! $enPlantilla && $personalizado->activo) {
                $personalizado->activo = false;
                $personalizado->peso = 0;
                $personalizado->save();
            }
        }

        $componentesActivos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->where('activo', true)
            ->get();

        if ($componentesActivos->isEmpty()) {
            throw new PesosEvaluacionBimestralInvalidosException(
                'Debe haber al menos un componente activo en la plantilla.',
            );
        }

        $this->pesosComponentesService->validarPesosManuales(
            $componentesActivos->pluck('peso', 'id')->map(fn ($p) => (float) $p)->all(),
        );

        $componenteEta = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->where('codigo', 'promedio_eta')
            ->first();

        if ($componenteEta === null) {
            return;
        }

        $this->sincronizarEtas($componenteEta, $plantilla['etas']);

        if ($componenteEta->activo) {
            $etasActivas = EvalBimEtaItem::query()
                ->where('eval_bim_componente_id', $componenteEta->id)
                ->where('activo', true)
                ->get();

            if ($etasActivas->isNotEmpty()) {
                $this->pesosEtaInternosService->validarPesosManuales(
                    $etasActivas->pluck('peso_interno', 'id')->map(fn ($p) => (float) $p)->all(),
                );
            }
        }
    }

    /**
     * @param  array{
     *     componentes: list<array{codigo: string, nombre: string, peso: float|int|string, activo: bool, orden: int}>,
     *     etas: list<array{nombre: string, peso_interno: float|int|string, activo: bool, orden: int}>
     * }  $plantilla
     */
    private function sincronizarEtas(EvalBimComponente $componenteEta, array $etasPlantilla): void
    {
        $itemsDb = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $componenteEta->id)
            ->get();

        $nombresEnPlantilla = [];

        foreach ($etasPlantilla as $item) {
            $nombre = trim($item['nombre']);
            $norm = $this->normalizarNombre($nombre);
            $nombresEnPlantilla[] = $norm;

            $eta = $itemsDb->first(
                fn (EvalBimEtaItem $e) => $this->normalizarNombre($e->nombre) === $norm,
            );

            if ($eta === null) {
                $maxOrden = (int) EvalBimEtaItem::query()
                    ->where('eval_bim_componente_id', $componenteEta->id)
                    ->max('orden');

                $eta = EvalBimEtaItem::query()->create([
                    'eval_bim_componente_id' => $componenteEta->id,
                    'nombre' => $nombre,
                    'peso_interno' => (bool) $item['activo'] ? (float) $item['peso_interno'] : 0,
                    'orden' => (int) $item['orden'] ?: $maxOrden + 1,
                    'activo' => (bool) $item['activo'],
                ]);
            } else {
                $eta->nombre = $nombre;
                $eta->peso_interno = (bool) $item['activo'] ? (float) $item['peso_interno'] : 0;
                $eta->orden = (int) $item['orden'];
                $eta->activo = (bool) $item['activo'];
                $eta->save();
            }
        }

        foreach ($itemsDb as $item) {
            if (! in_array($this->normalizarNombre($item->nombre), $nombresEnPlantilla, true) && $item->activo) {
                $item->activo = false;
                $item->peso_interno = 0;
                $item->save();
            }
        }
    }

    /**
     * @param  Collection<int, EvalBimComponente>  $componentesDb
     */
    private function resolverPersonalizado(
        Collection $componentesDb,
        int $mallaCursoId,
        int $periodoId,
        string $nombre,
        string $codigoPlantilla,
    ): EvalBimComponente {
        $norm = $this->normalizarNombre($nombre);

        $existente = $componentesDb->first(
            fn (EvalBimComponente $c) => $c->tipo === EvalBimComponenteTipo::Personalizado
                && ($this->normalizarNombre($c->nombre) === $norm
                    || $c->codigo === $codigoPlantilla),
        );

        if ($existente !== null) {
            return $existente;
        }

        $maxOrden = (int) EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->max('orden');

        return EvalBimComponente::query()->create([
            'malla_curso_id' => $mallaCursoId,
            'periodo_academico_id' => $periodoId,
            'tipo' => EvalBimComponenteTipo::Personalizado,
            'codigo' => str_starts_with($codigoPlantilla, 'personalizado_')
                ? $codigoPlantilla
                : 'personalizado_'.uniqid(),
            'nombre' => $nombre,
            'peso' => 0,
            'orden' => $maxOrden + 1,
            'activo' => false,
        ]);
    }

    /**
     * @param  array{
     *     componentes: list<array{codigo: string, nombre: string, peso: float|int|string, activo: bool, orden: int}>,
     *     etas: list<array{nombre: string, peso_interno: float|int|string, activo: bool, orden: int}>
     * }  $plantilla
     */
    private function validarPlantillaCoherente(array $plantilla): void
    {
        $sumaComponentes = round(
            collect($plantilla['componentes'])
                ->filter(fn (array $c) => (bool) $c['activo'])
                ->sum(fn (array $c) => (float) $c['peso']),
            2,
        );

        if (abs($sumaComponentes - 100.0) > 0.01) {
            throw ValidationException::withMessages([
                'plantilla.componentes' => [
                    sprintf(
                        'La suma de pesos de componentes activos en la plantilla debe ser 100 (actual: %s).',
                        $sumaComponentes,
                    ),
                ],
            ]);
        }

        $promedioEta = collect($plantilla['componentes'])->firstWhere('codigo', 'promedio_eta');
        if ($promedioEta !== null && (bool) $promedioEta['activo']) {
            $sumaEtas = round(
                collect($plantilla['etas'])
                    ->filter(fn (array $e) => (bool) $e['activo'])
                    ->sum(fn (array $e) => (float) $e['peso_interno']),
                2,
            );

            if ($sumaEtas > 0 && abs($sumaEtas - 100.0) > 0.01) {
                throw ValidationException::withMessages([
                    'plantilla.etas' => [
                        sprintf(
                            'La suma de pesos internos de ETAs activas debe ser 100 (actual: %s).',
                            $sumaEtas,
                        ),
                    ],
                ]);
            }
        }
    }

    private function validarPeriodoAcademico(string $anioEscolar, int $periodoId): void
    {
        $periodo = PeriodoAcademico::query()->find($periodoId);

        if ($periodo === null || trim($periodo->anio_escolar) !== $anioEscolar) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El período académico no corresponde al año escolar indicado.'],
            ]);
        }
    }

    private function esCodigoSistema(string $codigo): bool
    {
        return in_array($codigo, self::CODIGOS_SISTEMA, true);
    }

    private function normalizarNombre(string $nombre): string
    {
        return mb_strtolower(trim($nombre));
    }

    private function nombreCurso(MallaCurso $mallaCurso): string
    {
        return $mallaCurso->cursoCatalogo?->nombre ?? 'Curso #'.$mallaCurso->id;
    }
}
