<?php

namespace App\Services\Curricular;

class PlantillaRegistroAuxiliarLayout
{
    public const PLANTILLA_VERSION = 3;

    /** @var list<string> */
    public const TIPOS_BIMESTRAL_IMPORTABLES = ['oral', 'eta', 'examen_bimestral'];

    public const MODO_LEGACY = 'legacy';

    public const MODO_DINAMICO = 'dinamico';

    public const META_SHEET = '_meta';

    public const FILA_INICIO_TABLA = 6;

    public const FILAS_ENCABEZADO_TABLA = 4;

    public const FILA_INICIO_DATOS = 10;

    /** @var list<string> */
    public const LEGACY_CODIGOS_COMPATIBLES = ['cuaderno', 'libro', 'tarea'];

    /**
     * @param  array<string, mixed>  $formulario
     * @return array{
     *     modo: string,
     *     componentes: list<array{id: int, codigo: string, nombre: string, peso: float, orden: int}>,
     *     columnas_nota: list<array<string, mixed>>,
     *     columnas_por_criterio: int
     * }
     */
    public static function resolverDesdeFormulario(array $formulario): array
    {
        $modo = ($formulario['calificacion_dinamica_disponible'] ?? false) === true
            ? self::MODO_DINAMICO
            : self::MODO_LEGACY;

        $componentes = collect($formulario['componentes_calificacion'] ?? [])
            ->sortBy('orden')
            ->values()
            ->map(fn (array $c) => [
                'id' => (int) $c['id'],
                'codigo' => (string) $c['codigo'],
                'nombre' => (string) $c['nombre'],
                'peso' => (float) $c['peso'],
                'orden' => (int) $c['orden'],
            ])
            ->all();

        if ($modo === self::MODO_DINAMICO && $componentes === []) {
            $modo = self::MODO_LEGACY;
        }

        $columnasNota = $modo === self::MODO_DINAMICO
            ? self::columnasNotaDinamicas($componentes)
            : self::columnasNotaLegacy();

        return [
            'modo' => $modo,
            'componentes' => $componentes,
            'columnas_nota' => $columnasNota,
            'columnas_por_criterio' => count($columnasNota),
        ];
    }

    /**
     * @param  list<array{id: int, codigo: string, nombre: string, peso: float, orden: int}>  $componentes
     * @return list<array<string, mixed>>
     */
    public static function columnasNotaDinamicas(array $componentes): array
    {
        $columnas = [];
        foreach ($componentes as $componente) {
            $columnas[] = [
                'tipo' => 'componente',
                'componente_id' => $componente['id'],
                'codigo' => $componente['codigo'],
                'etiqueta' => $componente['nombre'],
                'peso' => $componente['peso'],
            ];
        }
        $columnas[] = [
            'tipo' => 'ce',
            'etiqueta' => 'CE',
        ];

        return $columnas;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function columnasNotaLegacy(): array
    {
        return [
            ['tipo' => 'legacy', 'campo' => 'nota_cuaderno', 'etiqueta' => 'C'],
            ['tipo' => 'legacy', 'campo' => 'nota_libro', 'etiqueta' => 'L'],
            ['tipo' => 'legacy', 'campo' => 'nota_tarea', 'etiqueta' => 'T'],
            ['tipo' => 'ce', 'etiqueta' => 'CE'],
        ];
    }

    public static function esColumnaBimestralImportable(string $tipo): bool
    {
        return in_array($tipo, self::TIPOS_BIMESTRAL_IMPORTABLES, true);
    }

    /**
     * @param  list<array<string, mixed>>  $columnasBimestral
     * @return list<array<string, mixed>>
     */
    public static function construirBimestralColsParaMapeo(array $columnasBimestral, int $colInicio): array
    {
        $cols = [];
        $col = $colInicio;

        foreach ($columnasBimestral as $def) {
            $tipo = (string) ($def['tipo'] ?? '');
            $entry = [
                'tipo' => $tipo,
                'col' => $col,
                'importable' => self::esColumnaBimestralImportable($tipo),
                'etiqueta' => (string) ($def['etiqueta'] ?? ''),
            ];

            if (isset($def['componente_id'])) {
                $entry['componente_id'] = (int) $def['componente_id'];
            }

            if (isset($def['eta_id'])) {
                $entry['eta_id'] = (int) $def['eta_id'];
            }

            $cols[] = $entry;
            $col++;
        }

        return $cols;
    }

    /**
     * @param  array{componentes?: iterable<int, array<string, mixed>>, etas?: iterable<int, array<string, mixed>>}  $evalBim
     * @return list<array<string, mixed>>
     */
    public static function columnasBimestralesDesdeEval(array $evalBim): array
    {
        $columnas = [];
        $componentes = collect($evalBim['componentes'] ?? [])->where('activo', true)->sortBy('orden')->values();
        $etas = collect($evalBim['etas'] ?? [])->where('activo', true)->sortBy('orden')->values();

        $promCrit = $componentes->firstWhere('codigo', 'promedio_criterios');
        if ($promCrit) {
            $columnas[] = [
                'tipo' => 'promedio_criterios',
                'etiqueta' => 'PROMEDIO DE CRITERIO',
                'componente_id' => $promCrit['id'],
            ];
        }

        $oral = $componentes->firstWhere('codigo', 'oral');
        if ($oral) {
            $columnas[] = ['tipo' => 'oral', 'etiqueta' => 'ORAL', 'componente_id' => $oral['id']];
        }

        foreach ($etas as $eta) {
            $columnas[] = [
                'tipo' => 'eta',
                'etiqueta' => mb_strtoupper((string) ($eta['nombre'] ?? '')),
                'eta_id' => $eta['id'],
            ];
        }

        $promEta = $componentes->firstWhere('codigo', 'promedio_eta');
        if ($promEta) {
            $columnas[] = [
                'tipo' => 'promedio_eta',
                'etiqueta' => 'PROMEDIO ETA',
                'componente_id' => $promEta['id'],
            ];
        }

        $examen = $componentes->firstWhere('codigo', 'examen_bimestral');
        if ($examen) {
            $columnas[] = [
                'tipo' => 'examen_bimestral',
                'etiqueta' => 'EXAMEN BIMESTRAL',
                'componente_id' => $examen['id'],
            ];
        }

        $columnas[] = ['tipo' => 'nivel_numerico', 'etiqueta' => 'NIVEL NUMÉRICO'];
        $columnas[] = ['tipo' => 'nivel_literal', 'etiqueta' => 'NIVEL LITERAL'];
        $columnas[] = ['tipo' => 'conclusion', 'etiqueta' => 'CONCLUSIONES DESCRIPTIVAS'];

        return $columnas;
    }

    /**
     * @param  list<array{criterio_id: int}>  $columnasCriterios
     * @param  list<array<string, mixed>>  $columnasNota
     * @param  list<array<string, mixed>>  $columnasBimestral
     * @return array{
     *     criterios: list<array{criterio_id: int, cols_inicio: int, nota_cols: list<array<string, mixed>>}>,
     *     bimestral_cols_inicio: int,
     *     columnas_bimestral: int,
     *     bimestral_cols: list<array<string, mixed>>
     * }
     */
    public static function construirMapeoImportacion(array $columnasCriterios, array $columnasNota, array $columnasBimestral): array
    {
        $criterios = [];
        $col = 3;

        foreach ($columnasCriterios as $colCriterio) {
            $notaCols = [];
            foreach ($columnasNota as $def) {
                $notaCols[] = array_merge($def, ['col' => $col]);
                $col++;
            }
            $criterios[] = [
                'criterio_id' => (int) $colCriterio['criterio_id'],
                'cols_inicio' => $notaCols[0]['col'] ?? $col,
                'nota_cols' => $notaCols,
            ];
        }

        $bimestralColsInicio = $col;

        return [
            'criterios' => $criterios,
            'bimestral_cols_inicio' => $bimestralColsInicio,
            'columnas_bimestral' => count($columnasBimestral),
            'bimestral_cols' => self::construirBimestralColsParaMapeo($columnasBimestral, $bimestralColsInicio),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $nota
     * @param  list<array<string, mixed>>  $columnasNota
     * @return list<mixed>
     */
    public static function valoresNotaParaPlantilla(?array $nota, array $columnasNota, string $modo, bool $incluirNotas): array
    {
        $valores = [];

        foreach ($columnasNota as $def) {
            if (($def['tipo'] ?? '') === 'ce') {
                $valores[] = $incluirNotas ? self::valorCelda($nota['ce_calculado'] ?? null) : null;
                continue;
            }

            if (($def['tipo'] ?? '') === 'legacy') {
                $campo = $def['campo'];
                $valores[] = $incluirNotas ? self::valorCelda($nota[$campo] ?? null) : null;
                continue;
            }

            if (($def['tipo'] ?? '') === 'componente') {
                $valores[] = $incluirNotas
                    ? self::valorComponenteDesdeNota($nota, (int) $def['componente_id'], $def['codigo'], $modo)
                    : null;
            }
        }

        return $valores;
    }

    /**
     * @param  array<string, mixed>|null  $nota
     */
    private static function valorComponenteDesdeNota(?array $nota, int $componenteId, string $codigo, string $modoPlantilla): mixed
    {
        if ($nota === null) {
            return null;
        }

        if (($nota['modelo_calificacion'] ?? 'legacy') === 'dinamico') {
            foreach ($nota['notas_componentes'] ?? [] as $item) {
                if ((int) ($item['componente_id'] ?? 0) === $componenteId) {
                    return self::valorCelda($item['nota'] ?? null);
                }
            }

            return null;
        }

        $mapa = [
            'cuaderno' => 'nota_cuaderno',
            'libro' => 'nota_libro',
            'tarea' => 'nota_tarea',
        ];
        $campo = $mapa[$codigo] ?? null;

        return $campo !== null ? self::valorCelda($nota[$campo] ?? null) : null;
    }

    private static function valorCelda(mixed $valor): mixed
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return is_numeric($valor) ? round((float) $valor, 2) : $valor;
    }
}
