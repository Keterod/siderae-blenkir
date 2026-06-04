<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralFormularioService;
use Illuminate\Support\Collection;

class PlantillaRegistroAuxiliarService
{
    public function __construct(
        private readonly NotaSemanalFormularioService $formularioService = new NotaSemanalFormularioService,
        private readonly EvaluacionBimestralFormularioService $evalBimFormularioService = new EvaluacionBimestralFormularioService,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function construirDesdeAsignacion(DocenteCursoAula $asignacion, int $periodoAcademicoId, bool $incluirNotas): array
    {
        $asignacion->loadMissing(['user', 'mallaCurso.area', 'mallaCurso.cursoCatalogo']);

        $formulario = $this->formularioService->construir($asignacion, $periodoAcademicoId);
        $evalBim = $this->evalBimFormularioService->construirDocente($asignacion, $periodoAcademicoId);

        return $this->armarPayload($formulario, $evalBim, $asignacion, $incluirNotas);
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
     *     area_id?: string|int|null
     * }  $filtros
     * @return array<string, mixed>
     */
    public function construirConsultaGlobal(array $filtros, bool $incluirNotas): array
    {
        $formulario = $this->formularioService->construirConsultaGlobal($filtros);
        $evalBim = $this->evalBimFormularioService->construirConsulta($filtros);

        $asignacion = DocenteCursoAula::query()
            ->with(['user', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
            ->where('activo', true)
            ->where('malla_curso_id', $filtros['malla_curso_id'])
            ->where('anio_escolar', $filtros['anio_escolar'])
            ->where('nivel', $filtros['nivel'])
            ->where('grado', $filtros['grado'])
            ->where('seccion', $filtros['seccion'])
            ->where('sede', $filtros['sede'])
            ->first();

        return $this->armarPayload($formulario, $evalBim, $asignacion, $incluirNotas);
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
     * }  $filtros
     * @return array<string, mixed>
     */
    public function construirPorMallaCursoEnAula(array $filtros, MallaCurso $mallaCurso, bool $incluirNotas): array
    {
        $filtros['malla_curso_id'] = $mallaCurso->id;
        $formulario = $this->formularioService->construirPorAula($filtros, $mallaCurso->id);
        $evalBim = $this->evalBimFormularioService->construirConsulta($filtros);

        return $this->armarPayload($formulario, $evalBim, null, $incluirNotas);
    }

    /**
     * @param  array<string, mixed>  $formulario
     * @param  array<string, mixed>  $evalBim
     * @return array<string, mixed>
     */
    private function armarPayload(array $formulario, array $evalBim, ?DocenteCursoAula $asignacion, bool $incluirNotas): array
    {
        /** @var Collection<int, TemaSemanal> $criterios */
        $criterios = $formulario['criterios'];
        $layout = PlantillaRegistroAuxiliarLayout::resolverDesdeFormulario($formulario);
        $columnasCriterios = $this->columnasCriterios($criterios);
        $columnasBimestral = $this->columnasBimestrales($evalBim);
        $mapeoImportacion = PlantillaRegistroAuxiliarLayout::construirMapeoImportacion(
            $columnasCriterios,
            $layout['columnas_nota'],
            $columnasBimestral,
        );

        $estudiantes = collect($formulario['estudiantes'])->map(function ($est) use ($formulario, $evalBim, $incluirNotas, $columnasCriterios, $columnasBimestral, $layout) {
            $notasPorCriterio = $formulario['notas_por_estudiante_criterio'][$est->id] ?? [];
            $resultado = $evalBim['resultados_por_estudiante'][$est->id] ?? null;
            $scalars = $evalBim['notas_scalar_por_estudiante'][$est->id] ?? [];
            $etasNotas = $evalBim['notas_eta_por_estudiante'][$est->id] ?? [];

            $notasCriterio = [];
            foreach ($columnasCriterios as $col) {
                $nota = $notasPorCriterio[$col['criterio_id']] ?? null;
                $notasCriterio[] = [
                    'valores' => PlantillaRegistroAuxiliarLayout::valoresNotaParaPlantilla(
                        is_array($nota) ? $nota : null,
                        $layout['columnas_nota'],
                        $layout['modo'],
                        $incluirNotas,
                    ),
                ];
            }

            $bimestral = [];
            foreach ($columnasBimestral as $col) {
                $bimestral[] = $this->valorColumnaBimestral($col, $resultado, $scalars, $etasNotas, $incluirNotas);
            }

            return [
                'numero' => 0,
                'estudiante_id' => (int) $est->id,
                'nombre' => trim("{$est->apellidos} {$est->nombres}"),
                'notas_criterio' => $notasCriterio,
                'bimestral' => $bimestral,
            ];
        })->values()->all();

        foreach ($estudiantes as $i => &$fila) {
            $fila['numero'] = $i + 1;
        }
        unset($fila);

        $periodo = $formulario['periodo'];
        $curso = $formulario['curso'];
        $ctx = $evalBim['contexto'] ?? [];

        return [
            'incluir_notas' => $incluirNotas,
            'periodo_academico_id' => (int) $periodo->id,
            'asignacion_docente_id' => $asignacion?->id,
            'encabezado' => [
                'titulo' => 'REGISTRO AUXILIAR DE EVALUACIÓN DE LOS APRENDIZAJES',
                'bimestre' => $periodo->bimestre ?? '',
                'area' => $curso['area'] ?? '',
                'curso' => $curso['nombre'] ?? '',
                'docente' => $asignacion?->user?->name ?? '—',
                'anio_escolar' => $periodo->anio_escolar ?? ($asignacion?->anio_escolar ?? ($ctx['anio_escolar'] ?? '')),
                'nivel' => $asignacion?->nivel ?? ($ctx['nivel'] ?? ''),
                'grado' => $asignacion?->grado ?? ($ctx['grado'] ?? ''),
                'seccion' => $asignacion?->seccion ?? ($ctx['seccion'] ?? ''),
                'sede' => $asignacion?->sede ?? ($ctx['sede'] ?? ''),
            ],
            'pesos_clt' => $formulario['pesos'],
            'modo_calificacion_plantilla' => $layout['modo'],
            'componentes_calificacion' => $layout['componentes'],
            'columnas_nota' => $layout['columnas_nota'],
            'columnas_por_criterio' => $layout['columnas_por_criterio'],
            'mapeo_importacion' => $mapeoImportacion,
            'columnas_criterios' => $columnasCriterios,
            'columnas_bimestral' => $columnasBimestral,
            'pesos_nivel_componentes' => $this->pesosNivelComponentes($evalBim),
            'estudiantes' => $estudiantes,
            'nombre_archivo' => $this->nombreArchivo(
                (string) ($asignacion?->nivel ?? $ctx['nivel'] ?? 'nivel'),
                (string) ($asignacion?->grado ?? $ctx['grado'] ?? 'grado'),
                (string) ($asignacion?->seccion ?? $ctx['seccion'] ?? 'seccion'),
                (string) ($curso['nombre'] ?? 'curso'),
                (int) ($periodo->bimestre ?? 0),
                (string) ($periodo->anio_escolar ?? 'anio'),
            ),
        ];
    }

    /**
     * @param  Collection<int, TemaSemanal>  $criterios
     * @return list<array<string, mixed>>
     */
    private function columnasCriterios(Collection $criterios): array
    {
        $grupos = $this->agruparCriterios($criterios);
        $columnas = [];

        foreach ($grupos as $grupo) {
            foreach ($grupo['capacidades'] as $cap) {
                foreach ($cap['criterios'] as $criterio) {
                    $columnas[] = [
                        'criterio_id' => $criterio->id,
                        'competencia_id' => $grupo['competencia']['id'],
                        'competencia_nombre' => $grupo['competencia']['nombre'],
                        'capacidad_id' => $cap['capacidad']['id'],
                        'capacidad_nombre' => $cap['capacidad']['nombre'],
                        'criterio_titulo' => $criterio->titulo,
                    ];
                }
            }
        }

        return $columnas;
    }

    /**
     * @param  Collection<int, TemaSemanal>  $criterios
     * @return list<array{competencia: array{id: mixed, nombre: string}, capacidades: list<array{capacidad: array{id: mixed, nombre: string}, criterios: list<TemaSemanal>}>}>
     */
    private function agruparCriterios(Collection $criterios): array
    {
        $porCompetencia = [];

        foreach ($criterios as $tema) {
            if (! $tema->activo) {
                continue;
            }

            foreach ($tema->capacidades as $cap) {
                $compId = $cap->pivot->competencia_id ?? $cap->competencia_id;
                $comp = $tema->competencias->first(fn ($c) => (int) $c->id === (int) $compId)
                    ?? ['id' => $compId, 'nombre' => 'Competencia'];

                $compKey = (string) $compId;
                if (! isset($porCompetencia[$compKey])) {
                    $porCompetencia[$compKey] = [
                        'competencia' => ['id' => $comp->id ?? $compId, 'nombre' => $comp->nombre ?? 'Competencia'],
                        'capacidades' => [],
                    ];
                }

                $capKey = (string) $cap->id;
                if (! isset($porCompetencia[$compKey]['capacidades'][$capKey])) {
                    $porCompetencia[$compKey]['capacidades'][$capKey] = [
                        'capacidad' => ['id' => $cap->id, 'nombre' => $cap->nombre],
                        'criterios' => [],
                    ];
                }

                $lista = &$porCompetencia[$compKey]['capacidades'][$capKey]['criterios'];
                if (! collect($lista)->contains('id', $tema->id)) {
                    $lista[] = $tema;
                }
            }
        }

        $resultado = [];
        foreach ($porCompetencia as $grupo) {
            $caps = array_values($grupo['capacidades']);
            usort($caps, fn ($a, $b) => strcmp($a['capacidad']['nombre'], $b['capacidad']['nombre']));
            foreach ($caps as &$cap) {
                usort($cap['criterios'], fn ($a, $b) => ($a->id <=> $b->id));
            }
            unset($cap);
            $resultado[] = [
                'competencia' => $grupo['competencia'],
                'capacidades' => $caps,
            ];
        }

        usort($resultado, fn ($a, $b) => strcmp($a['competencia']['nombre'], $b['competencia']['nombre']));

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $evalBim
     * @return list<array<string, mixed>>
     */
    private function columnasBimestrales(array $evalBim): array
    {
        $columnas = [];
        $componentes = collect($evalBim['componentes'] ?? [])->where('activo', true)->sortBy('orden')->values();
        $etas = collect($evalBim['etas'] ?? [])->where('activo', true)->sortBy('orden')->values();

        $promCrit = $componentes->firstWhere('codigo', 'promedio_criterios');
        if ($promCrit) {
            $columnas[] = ['tipo' => 'promedio_criterios', 'etiqueta' => 'PROMEDIO DE CRITERIO', 'componente_id' => $promCrit['id']];
        }

        $oral = $componentes->firstWhere('codigo', 'oral');
        if ($oral) {
            $columnas[] = ['tipo' => 'oral', 'etiqueta' => 'ORAL', 'componente_id' => $oral['id']];
        }

        foreach ($etas as $eta) {
            $columnas[] = [
                'tipo' => 'eta',
                'etiqueta' => mb_strtoupper($eta['nombre']),
                'eta_id' => $eta['id'],
            ];
        }

        $promEta = $componentes->firstWhere('codigo', 'promedio_eta');
        if ($promEta) {
            $columnas[] = ['tipo' => 'promedio_eta', 'etiqueta' => 'PROMEDIO ETA', 'componente_id' => $promEta['id']];
        }

        $examen = $componentes->firstWhere('codigo', 'examen_bimestral');
        if ($examen) {
            $columnas[] = ['tipo' => 'examen_bimestral', 'etiqueta' => 'EXAMEN BIMESTRAL', 'componente_id' => $examen['id']];
        }

        $columnas[] = ['tipo' => 'nivel_numerico', 'etiqueta' => 'NIVEL NUMÉRICO'];
        $columnas[] = ['tipo' => 'nivel_literal', 'etiqueta' => 'NIVEL LITERAL'];
        $columnas[] = ['tipo' => 'conclusion', 'etiqueta' => 'CONCLUSIONES DESCRIPTIVAS'];

        return $columnas;
    }

    /**
     * Pesos de componentes activos para fórmulas Excel de nivel (solo plantilla vacía).
     *
     * @param  array<string, mixed>  $evalBim
     * @return list<array{codigo: string, peso: float}>
     */
    private function pesosNivelComponentes(array $evalBim): array
    {
        $orden = ['promedio_criterios', 'oral', 'promedio_eta', 'examen_bimestral'];

        return collect($evalBim['componentes'] ?? [])
            ->where('activo', true)
            ->filter(fn ($c) => in_array($c['codigo'] ?? '', $orden, true))
            ->sortBy(fn ($c) => array_search($c['codigo'], $orden, true))
            ->map(fn ($c) => [
                'codigo' => (string) $c['codigo'],
                'peso' => (float) $c['peso'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $resultado
     * @param  array<int, array{nota?: float|null}>  $scalars
     * @param  array<int, array{nota?: float|null}>  $etasNotas
     */
    private function valorColumnaBimestral(array $col, ?array $resultado, array $scalars, array $etasNotas, bool $incluirNotas): mixed
    {
        if (! $incluirNotas) {
            return null;
        }

        return match ($col['tipo']) {
            'promedio_criterios' => $this->valorCelda($resultado['promedio_criterios'] ?? null),
            'oral' => $this->valorCelda($resultado['oral'] ?? ($scalars[$col['componente_id']]['nota'] ?? null)),
            'eta' => $this->valorCelda($etasNotas[$col['eta_id']]['nota'] ?? null),
            'promedio_eta' => $this->valorCelda($resultado['promedio_eta'] ?? null),
            'examen_bimestral' => $this->valorCelda($resultado['examen_bimestral'] ?? ($scalars[$col['componente_id']]['nota'] ?? null)),
            'nivel_numerico' => $this->valorCelda($resultado['nivel_logro_numerico'] ?? null),
            'nivel_literal' => $resultado['nivel_logro_literal'] ?? null,
            'conclusion' => $resultado['conclusion_descriptiva'] ?? null,
            default => null,
        };
    }

    private function valorCelda(mixed $valor): mixed
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return is_numeric($valor) ? round((float) $valor, 2) : $valor;
    }

    private function nombreArchivo(string $nivel, string $grado, string $seccion, string $curso, int $bimestre, string $anio): string
    {
        $slug = fn (string $s) => preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim($s))) ?: 'x';

        return sprintf(
            'plantilla_registro_auxiliar_%s_%s_%s_%s_bimestre_%d_%s.xlsx',
            $slug($nivel),
            $slug($grado),
            $slug($seccion),
            $slug($curso),
            $bimestre,
            $slug($anio),
        );
    }
}
