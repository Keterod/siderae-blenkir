<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\User;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralBulkService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionComponentesResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportPlantillaRegistroAuxiliarService
{
    public const MENSAJE_PLANTILLA_NO_COINCIDE =
        'La plantilla Excel no coincide con los componentes activos actuales. Descargue una nueva plantilla.';

    public const MENSAJE_PERIODO_DISTINTO =
        'La plantilla Excel pertenece a otro bimestre. Descargue una nueva plantilla.';

    public const MENSAJE_NOMBRES_DUPLICADOS =
        'Hay estudiantes con el mismo nombre en el aula. La plantilla debe incluir ID de estudiante. Descargue una nueva plantilla.';

    public const MENSAJE_CONTEXTO_DISTINTO =
        'La plantilla Excel no corresponde al aula o asignación seleccionada. Descargue una nueva plantilla.';

    public const MENSAJE_SIN_META =
        'La plantilla Excel no contiene metadatos de importación. Descargue una nueva plantilla.';

    public const MENSAJE_BIMESTRAL_NO_COINCIDE =
        'La plantilla no coincide con la evaluación bimestral actual. Descargue una nueva plantilla.';

    public function __construct(
        private readonly NotaSemanalFormularioService $formularioService = new NotaSemanalFormularioService,
        private readonly NotaSemanalBulkService $bulkService = new NotaSemanalBulkService,
        private readonly EvaluacionBimestralBulkService $evalBimBulkService = new EvaluacionBimestralBulkService,
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
    ) {}

    /**
     * @return array{
     *     notas: list<\App\Models\Curricular\NotaSemanal>,
     *     advertencias: list<string>,
     *     importados: int,
     *     importados_criterios: int,
     *     importados_bimestral: int,
     *     omitidos: int
     * }
     */
    public function importar(string $binary, User $docente, DocenteCursoAula $asignacion, int $periodoAcademicoId): array
    {
        $formulario = $this->formularioService->construir($asignacion, $periodoAcademicoId);
        $layoutActual = PlantillaRegistroAuxiliarLayout::resolverDesdeFormulario($formulario);

        $spreadsheet = $this->cargarSpreadsheet($binary);
        $meta = $this->leerMeta($spreadsheet);
        $this->validarContextoAulaPlantilla($meta, $asignacion, $formulario);
        $this->validarPeriodoAcademicoPlantilla($meta, $periodoAcademicoId);
        $this->validarCompatibilidadPlantilla($meta, $layoutActual, $formulario);

        $mapeo = json_decode($meta['mapeo_importacion_json'] ?? '[]', true);
        if (! is_array($mapeo) || ($mapeo['criterios'] ?? []) === []) {
            throw ValidationException::withMessages([
                'archivo' => ['La plantilla no contiene metadatos de columnas válidos. Descargue una nueva plantilla.'],
            ]);
        }

        $this->validarCriteriosMapeoPeriodo($mapeo, $periodoAcademicoId);
        $this->validarBimestralMapeoVsConfig($mapeo, $asignacion, $periodoAcademicoId);

        $sheet = $spreadsheet->getSheetByName('Registro auxiliar') ?? $spreadsheet->getActiveSheet();
        $estudiantesValidos = $this->indexarEstudiantesPorId($formulario['estudiantes'] ?? []);
        $estudiantesPorNombre = $this->indexarEstudiantesPorNombre($formulario['estudiantes'] ?? []);
        $hayNombresDuplicados = $this->hayNombresDuplicadosEnAula($formulario['estudiantes'] ?? []);
        $filaInicio = (int) ($meta['fila_inicio_datos'] ?? PlantillaRegistroAuxiliarLayout::FILA_INICIO_DATOS);

        $extraccionCriterios = $this->extraerRegistrosDesdeHoja(
            $sheet,
            $mapeo,
            $estudiantesValidos,
            $estudiantesPorNombre,
            $hayNombresDuplicados,
            $filaInicio,
            $meta,
            $layoutActual,
        );

        $extraccionBimestral = $this->extraerRegistrosBimestralesDesdeHoja(
            $sheet,
            $mapeo,
            $estudiantesValidos,
            $estudiantesPorNombre,
            $hayNombresDuplicados,
            $filaInicio,
            $meta,
        );

        $registrosPorEstudiante = $extraccionCriterios['registros'];
        $registrosBimestral = $extraccionBimestral['registros'];
        $omitidos = $extraccionCriterios['omitidos'];

        if ($registrosPorEstudiante === [] && $registrosBimestral === []) {
            throw ValidationException::withMessages([
                'archivo' => ['No se encontraron notas válidas para importar en la plantilla.'],
            ]);
        }

        return DB::transaction(function () use (
            $docente,
            $asignacion,
            $periodoAcademicoId,
            $registrosPorEstudiante,
            $registrosBimestral,
            $omitidos,
            $extraccionBimestral,
        ): array {
            $notas = [];
            $advertencias = [];
            $importadosCriterios = 0;

            if ($registrosPorEstudiante !== []) {
                $resultadoCriterios = $this->bulkService->registrarPorVariosEstudiantes(
                    $docente,
                    $asignacion,
                    $registrosPorEstudiante,
                );
                $notas = $resultadoCriterios['notas'];
                $advertencias = array_merge($advertencias, $resultadoCriterios['advertencias']);
                $importadosCriterios = count($notas);
            }

            $importadosBimestral = 0;
            if ($registrosBimestral !== []) {
                $resultadoBim = $this->evalBimBulkService->registrar(
                    $docente,
                    $asignacion,
                    $periodoAcademicoId,
                    $registrosBimestral,
                );
                $advertencias = array_merge($advertencias, $resultadoBim['advertencias']);
                $importadosBimestral = $extraccionBimestral['conteo'];
            }

            return [
                'notas' => $notas,
                'advertencias' => array_values(array_unique($advertencias)),
                'importados' => $importadosCriterios,
                'importados_criterios' => $importadosCriterios,
                'importados_bimestral' => $importadosBimestral,
                'omitidos' => $omitidos,
            ];
        });
    }

    private function cargarSpreadsheet(string $binary): Spreadsheet
    {
        $tmp = tempnam(sys_get_temp_dir(), 'import_notas_xlsx_');
        if ($tmp === false) {
            throw ValidationException::withMessages([
                'archivo' => ['No se pudo procesar el archivo Excel.'],
            ]);
        }

        file_put_contents($tmp, $binary);

        try {
            return IOFactory::load($tmp);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'archivo' => ['El archivo no es un Excel válido.'],
            ]);
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * @return array<string, string>
     */
    private function leerMeta(Spreadsheet $spreadsheet): array
    {
        $metaSheet = $spreadsheet->getSheetByName(PlantillaRegistroAuxiliarLayout::META_SHEET);
        if ($metaSheet === null) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_SIN_META],
            ]);
        }

        $meta = [];
        $row = 1;
        while (true) {
            $clave = trim((string) $metaSheet->getCell("A{$row}")->getValue());
            if ($clave === '') {
                break;
            }
            $meta[$clave] = (string) $metaSheet->getCell("B{$row}")->getValue();
            $row++;
        }

        if (($meta['plantilla_version'] ?? '') === '') {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_PLANTILLA_NO_COINCIDE],
            ]);
        }

        return $meta;
    }

    /**
     * @param  array<string, string>  $meta
     * @param  array<string, mixed>  $formulario
     */
    private function validarContextoAulaPlantilla(array $meta, DocenteCursoAula $asignacion, array $formulario): void
    {
        $asignacionPlantilla = (int) ($meta['asignacion_docente_id'] ?? 0);
        if ($asignacionPlantilla <= 0 || $asignacionPlantilla !== (int) $asignacion->id) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_CONTEXTO_DISTINTO],
            ]);
        }

        $campos = [
            'anio_escolar' => (string) ($formulario['anio_escolar'] ?? $asignacion->anio_escolar),
            'nivel' => (string) ($formulario['nivel'] ?? $asignacion->nivel),
            'grado' => (string) $asignacion->grado,
            'seccion' => (string) $asignacion->seccion,
            'sede' => (string) $asignacion->sede,
        ];

        foreach ($campos as $clave => $valorEsperado) {
            $valorPlantilla = trim((string) ($meta[$clave] ?? ''));
            if ($valorPlantilla === '' || $valorPlantilla !== $valorEsperado) {
                throw ValidationException::withMessages([
                    'archivo' => [self::MENSAJE_CONTEXTO_DISTINTO],
                ]);
            }
        }
    }

    /**
     * @param  array<string, string>  $meta
     * @param  array<string, mixed>  $layoutActual
     * @param  array<string, mixed>  $formulario
     */
    private function validarCompatibilidadPlantilla(array $meta, array $layoutActual, array $formulario): void
    {
        $modoPlantilla = $meta['modo_calificacion'] ?? PlantillaRegistroAuxiliarLayout::MODO_LEGACY;
        $modoActual = $layoutActual['modo'];
        $componentesPlantilla = json_decode($meta['componentes_json'] ?? '[]', true) ?: [];
        $componentesActuales = $layoutActual['componentes'];

        if ($modoPlantilla === PlantillaRegistroAuxiliarLayout::MODO_DINAMICO && $modoActual === PlantillaRegistroAuxiliarLayout::MODO_LEGACY) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_PLANTILLA_NO_COINCIDE],
            ]);
        }

        if ($modoPlantilla === PlantillaRegistroAuxiliarLayout::MODO_DINAMICO && $modoActual === PlantillaRegistroAuxiliarLayout::MODO_DINAMICO) {
            if (! $this->componentesCoinciden($componentesPlantilla, $componentesActuales)) {
                throw ValidationException::withMessages([
                    'archivo' => [self::MENSAJE_PLANTILLA_NO_COINCIDE],
                ]);
            }

            return;
        }

        if ($modoPlantilla === PlantillaRegistroAuxiliarLayout::MODO_LEGACY && $modoActual === PlantillaRegistroAuxiliarLayout::MODO_DINAMICO) {
            if (! $this->esCompatibilidadLegacyClt($componentesActuales)) {
                throw ValidationException::withMessages([
                    'archivo' => [self::MENSAJE_PLANTILLA_NO_COINCIDE],
                ]);
            }

            return;
        }

        // legacy + legacy: ok
    }

    /**
     * @param  array<string, string>  $meta
     */
    private function validarPeriodoAcademicoPlantilla(array $meta, int $periodoAcademicoId): void
    {
        $periodoPlantilla = (int) ($meta['periodo_academico_id'] ?? 0);

        if ($periodoPlantilla <= 0) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_PERIODO_DISTINTO],
            ]);
        }

        if ($periodoPlantilla !== $periodoAcademicoId) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_PERIODO_DISTINTO],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $mapeo
     */
    private function validarCriteriosMapeoPeriodo(array $mapeo, int $periodoAcademicoId): void
    {
        $criterioIds = collect($mapeo['criterios'] ?? [])
            ->pluck('criterio_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($criterioIds->isEmpty()) {
            return;
        }

        $periodosPorTema = \App\Models\Curricular\TemaSemanal::query()
            ->whereIn('id', $criterioIds)
            ->pluck('periodo_academico_id', 'id');

        foreach ($criterioIds as $criterioId) {
            $periodoTema = (int) ($periodosPorTema[$criterioId] ?? 0);
            if ($periodoTema !== $periodoAcademicoId) {
                throw ValidationException::withMessages([
                    'archivo' => [self::MENSAJE_PERIODO_DISTINTO],
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $mapeo
     */
    private function validarBimestralMapeoVsConfig(array $mapeo, DocenteCursoAula $asignacion, int $periodoAcademicoId): void
    {
        $colsPlantilla = $mapeo['bimestral_cols'] ?? [];
        if ($colsPlantilla === []) {
            return;
        }

        $config = $this->componentesResolver->resolver($asignacion->malla_curso_id, $periodoAcademicoId);
        $columnasBimestral = $this->columnasBimestralesDesdeConfig($config);
        $esperadas = PlantillaRegistroAuxiliarLayout::construirBimestralColsParaMapeo(
            $columnasBimestral,
            (int) ($mapeo['bimestral_cols_inicio'] ?? 0),
        );

        if (! $this->bimestralColsCoinciden($colsPlantilla, $esperadas)) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_BIMESTRAL_NO_COINCIDE],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<array<string, mixed>>
     */
    private function columnasBimestralesDesdeConfig(array $config): array
    {
        $componentes = $config['componentes_activos']->map(fn ($c) => [
            'id' => (int) $c->id,
            'codigo' => (string) $c->codigo,
            'activo' => (bool) $c->activo,
            'orden' => (int) $c->orden,
        ])->values()->all();

        $etas = $config['eta_items_activos']->map(fn ($e) => [
            'id' => (int) $e->id,
            'nombre' => (string) $e->nombre,
            'activo' => (bool) $e->activo,
            'orden' => (int) $e->orden,
        ])->values()->all();

        return PlantillaRegistroAuxiliarLayout::columnasBimestralesDesdeEval([
            'componentes' => $componentes,
            'etas' => $etas,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $plantilla
     * @param  list<array<string, mixed>>  $esperadas
     */
    private function bimestralColsCoinciden(array $plantilla, array $esperadas): bool
    {
        if (count($plantilla) !== count($esperadas)) {
            return false;
        }

        for ($i = 0; $i < count($esperadas); $i++) {
            $p = $plantilla[$i] ?? null;
            $e = $esperadas[$i];
            if ($p === null) {
                return false;
            }

            if (($p['tipo'] ?? '') !== ($e['tipo'] ?? '')) {
                return false;
            }

            if ((bool) ($p['importable'] ?? false) !== (bool) ($e['importable'] ?? false)) {
                return false;
            }

            if ((int) ($p['col'] ?? 0) !== (int) ($e['col'] ?? 0)) {
                return false;
            }

            if (isset($e['componente_id']) && (int) ($p['componente_id'] ?? 0) !== (int) $e['componente_id']) {
                return false;
            }

            if (isset($e['eta_id']) && (int) ($p['eta_id'] ?? 0) !== (int) $e['eta_id']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array{id?: int, codigo?: string, orden?: int}>  $plantilla
     * @param  list<array{id: int, codigo: string, orden: int}>  $actuales
     */
    private function componentesCoinciden(array $plantilla, array $actuales): bool
    {
        if (count($plantilla) !== count($actuales)) {
            return false;
        }

        for ($i = 0; $i < count($actuales); $i++) {
            $p = $plantilla[$i] ?? null;
            $a = $actuales[$i];
            if ($p === null) {
                return false;
            }
            if ((int) ($p['id'] ?? 0) !== (int) $a['id']) {
                return false;
            }
            if (($p['codigo'] ?? '') !== ($a['codigo'] ?? '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array{codigo: string}>  $componentesActuales
     */
    private function esCompatibilidadLegacyClt(array $componentesActuales): bool
    {
        if (count($componentesActuales) !== 3) {
            return false;
        }

        $codigos = array_map(fn (array $c) => $c['codigo'], $componentesActuales);

        return $codigos === PlantillaRegistroAuxiliarLayout::LEGACY_CODIGOS_COMPATIBLES;
    }

    /**
     * @param  iterable<int, \App\Models\Estudiante>  $estudiantes
     * @return array<int, int>
     */
    private function indexarEstudiantesPorId(iterable $estudiantes): array
    {
        $indice = [];
        foreach ($estudiantes as $est) {
            $indice[(int) $est->id] = (int) $est->id;
        }

        return $indice;
    }

    /**
     * @param  iterable<int, \App\Models\Estudiante>  $estudiantes
     * @return array<string, int>
     */
    private function indexarEstudiantesPorNombre(iterable $estudiantes): array
    {
        $indice = [];
        foreach ($estudiantes as $est) {
            $nombre = $this->normalizarNombreCompleto(trim("{$est->apellidos} {$est->nombres}"));
            $indice[$nombre] = (int) $est->id;
        }

        return $indice;
    }

    /**
     * @param  iterable<int, \App\Models\Estudiante>  $estudiantes
     */
    private function hayNombresDuplicadosEnAula(iterable $estudiantes): bool
    {
        $vistos = [];
        foreach ($estudiantes as $est) {
            $nombre = $this->normalizarNombreCompleto(trim("{$est->apellidos} {$est->nombres}"));
            if (isset($vistos[$nombre])) {
                return true;
            }
            $vistos[$nombre] = true;
        }

        return false;
    }

    private function normalizarNombreCompleto(string $nombre): string
    {
        return Str::lower(trim(preg_replace('/\s+/u', ' ', $nombre) ?? ''));
    }

    /**
     * @param  array<int, int>  $estudiantesValidos
     * @param  array<string, int>  $estudiantesPorNombre
     * @param  array<string, string>  $meta
     * @param  array<string, mixed>  $layoutActual
     * @return array{registros: list<array{estudiante_id: int, registros: list<array<string, mixed>>}>, omitidos: int}
     */
    private function extraerRegistrosDesdeHoja(
        Worksheet $sheet,
        array $mapeo,
        array $estudiantesValidos,
        array $estudiantesPorNombre,
        bool $hayNombresDuplicados,
        int $filaInicio,
        array $meta,
        array $layoutActual,
    ): array {
        $modoPlantilla = $meta['modo_calificacion'] ?? PlantillaRegistroAuxiliarLayout::MODO_LEGACY;
        $modoActual = $layoutActual['modo'];
        $usarDinamico = $modoActual === PlantillaRegistroAuxiliarLayout::MODO_DINAMICO;
        $componentesPorCodigo = collect($layoutActual['componentes'])->keyBy('codigo');

        $porEstudiante = [];
        $omitidos = 0;
        $fila = $filaInicio;
        $maxFila = $sheet->getHighestRow();

        while ($fila <= $maxFila) {
            $nombreRaw = trim((string) $sheet->getCell("B{$fila}")->getValue());
            if ($nombreRaw === '') {
                break;
            }

            $estudianteId = $this->resolverEstudianteId(
                $sheet,
                $fila,
                $meta,
                $estudiantesValidos,
                $estudiantesPorNombre,
                $hayNombresDuplicados,
                $nombreRaw,
            );

            $registros = [];

            foreach ($mapeo['criterios'] as $bloqueCriterio) {
                $criterioId = (int) ($bloqueCriterio['criterio_id'] ?? 0);
                $notaCols = $bloqueCriterio['nota_cols'] ?? [];

                if ($usarDinamico) {
                    $notasComponentes = [];
                    foreach ($notaCols as $colDef) {
                        if (($colDef['tipo'] ?? '') === 'ce') {
                            continue;
                        }

                        $colIndex = (int) ($colDef['col'] ?? 0);
                        if ($colIndex <= 0) {
                            continue;
                        }

                        $valor = $this->leerValorNumericoCelda($sheet, $colIndex, $fila);
                        if ($valor === null) {
                            continue;
                        }

                        if (($colDef['tipo'] ?? '') === 'componente') {
                            $notasComponentes[] = [
                                'componente_id' => (int) $colDef['componente_id'],
                                'nota' => $valor,
                            ];
                        } elseif (
                            $modoPlantilla === PlantillaRegistroAuxiliarLayout::MODO_LEGACY
                            && ($colDef['tipo'] ?? '') === 'legacy'
                        ) {
                            $campo = $colDef['campo'] ?? '';
                            $codigo = match ($campo) {
                                'nota_cuaderno' => 'cuaderno',
                                'nota_libro' => 'libro',
                                'nota_tarea' => 'tarea',
                                default => null,
                            };
                            $comp = $codigo !== null ? $componentesPorCodigo->get($codigo) : null;
                            if ($comp !== null) {
                                $notasComponentes[] = [
                                    'componente_id' => (int) $comp['id'],
                                    'nota' => $valor,
                                ];
                            }
                        }
                    }

                    if ($notasComponentes !== []) {
                        $registros[] = [
                            'tema_semanal_id' => $criterioId,
                            'notas_componentes' => $notasComponentes,
                        ];
                    }
                } else {
                    $payload = ['tema_semanal_id' => $criterioId];
                    $tieneNota = false;
                    foreach ($notaCols as $colDef) {
                        if (($colDef['tipo'] ?? '') === 'ce') {
                            continue;
                        }
                        if (($colDef['tipo'] ?? '') !== 'legacy') {
                            continue;
                        }
                        $colIndex = (int) ($colDef['col'] ?? 0);
                        $valor = $this->leerValorNumericoCelda($sheet, $colIndex, $fila);
                        if ($valor === null) {
                            continue;
                        }
                        $campo = $colDef['campo'] ?? null;
                        if ($campo !== null) {
                            $payload[$campo] = $valor;
                            $tieneNota = true;
                        }
                    }
                    if ($tieneNota) {
                        $registros[] = $payload;
                    }
                }
            }

            if ($registros !== []) {
                $porEstudiante[] = [
                    'estudiante_id' => $estudianteId,
                    'registros' => $registros,
                ];
            } else {
                $omitidos++;
            }

            $fila++;
        }

        return [
            'registros' => $porEstudiante,
            'omitidos' => $omitidos,
        ];
    }

    /**
     * @param  array<int, int>  $estudiantesValidos
     * @param  array<string, int>  $estudiantesPorNombre
     * @param  array<string, string>  $meta
     * @return array{registros: list<array<string, mixed>>, conteo: int}
     */
    private function extraerRegistrosBimestralesDesdeHoja(
        Worksheet $sheet,
        array $mapeo,
        array $estudiantesValidos,
        array $estudiantesPorNombre,
        bool $hayNombresDuplicados,
        int $filaInicio,
        array $meta,
    ): array {
        $bimestralCols = $mapeo['bimestral_cols'] ?? [];
        if ($bimestralCols === []) {
            return ['registros' => [], 'conteo' => 0];
        }

        $registros = [];
        $fila = $filaInicio;
        $maxFila = $sheet->getHighestRow();

        while ($fila <= $maxFila) {
            $nombreRaw = trim((string) $sheet->getCell("B{$fila}")->getValue());
            if ($nombreRaw === '') {
                break;
            }

            $estudianteId = $this->resolverEstudianteId(
                $sheet,
                $fila,
                $meta,
                $estudiantesValidos,
                $estudiantesPorNombre,
                $hayNombresDuplicados,
                $nombreRaw,
            );

            $filaPayload = ['estudiante_id' => $estudianteId];
            $tieneDato = false;

            foreach ($bimestralCols as $colDef) {
                if (! ($colDef['importable'] ?? false)) {
                    continue;
                }

                $colIndex = (int) ($colDef['col'] ?? 0);
                if ($colIndex <= 0) {
                    continue;
                }

                $valor = $this->leerValorNumericoCelda($sheet, $colIndex, $fila);
                if ($valor === null) {
                    continue;
                }

                $tipo = (string) ($colDef['tipo'] ?? '');
                if ($tipo === 'oral') {
                    $filaPayload['oral'] = $valor;
                    $tieneDato = true;
                    continue;
                }

                if ($tipo === 'examen_bimestral') {
                    $filaPayload['examen_bimestral'] = $valor;
                    $tieneDato = true;
                    continue;
                }

                if ($tipo === 'eta') {
                    $filaPayload['etas'] = $filaPayload['etas'] ?? [];
                    $filaPayload['etas'][] = [
                        'eta_item_id' => (int) ($colDef['eta_id'] ?? 0),
                        'nota' => $valor,
                    ];
                    $tieneDato = true;
                }
            }

            if ($tieneDato) {
                $registros[] = $filaPayload;
            }

            $fila++;
        }

        return [
            'registros' => $registros,
            'conteo' => count($registros),
        ];
    }

    /**
     * @param  array<int, int>  $estudiantesValidos
     * @param  array<string, int>  $estudiantesPorNombre
     * @param  array<string, string>  $meta
     */
    private function resolverEstudianteId(
        Worksheet $sheet,
        int $fila,
        array $meta,
        array $estudiantesValidos,
        array $estudiantesPorNombre,
        bool $hayNombresDuplicados,
        string $nombreRaw,
    ): int {
        $colEstudianteId = (int) ($meta['col_estudiante_id'] ?? 0);

        if ($colEstudianteId > 0) {
            $rawId = $sheet->getCellByColumnAndRow($colEstudianteId, $fila)->getValue();
            if (is_numeric($rawId) && (int) $rawId > 0) {
                $estudianteId = (int) $rawId;
                if (! isset($estudiantesValidos[$estudianteId])) {
                    throw ValidationException::withMessages([
                        'archivo' => [
                            "El estudiante con ID {$estudianteId} no pertenece al aula seleccionada (fila {$fila}).",
                        ],
                    ]);
                }

                return $estudianteId;
            }
        }

        if ($hayNombresDuplicados) {
            throw ValidationException::withMessages([
                'archivo' => [self::MENSAJE_NOMBRES_DUPLICADOS],
            ]);
        }

        $estudianteId = $estudiantesPorNombre[$this->normalizarNombreCompleto($nombreRaw)] ?? null;
        if ($estudianteId === null) {
            throw ValidationException::withMessages([
                'archivo' => ["No se encontró al estudiante «{$nombreRaw}» en el aula seleccionada (fila {$fila})."],
            ]);
        }

        return $estudianteId;
    }

    private function leerValorNumericoCelda(Worksheet $sheet, int $colIndex, int $fila): ?float
    {
        $cell = $sheet->getCellByColumnAndRow($colIndex, $fila);
        $valor = $cell->getCalculatedValue();

        if ($valor === null || $valor === '') {
            return null;
        }

        if (! is_numeric($valor)) {
            throw ValidationException::withMessages([
                'archivo' => [
                    sprintf(
                        'Valor no numérico en celda %s%d. Las notas deben estar entre 0 y 20.',
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex),
                        $fila,
                    ),
                ],
            ]);
        }

        $nota = (float) $valor;
        if ($nota < 0 || $nota > 20) {
            throw ValidationException::withMessages([
                'archivo' => [
                    sprintf(
                        'Nota fuera de rango en celda %s%d. Debe estar entre 0 y 20.',
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex),
                        $fila,
                    ),
                ],
            ]);
        }

        return $nota;
    }
}
