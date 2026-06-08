<?php

namespace App\Services\Curricular;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlantillaRegistroAuxiliarExcelService
{
    private const TITLE_FILL = 'FF4472C4';
    private const TITLE_FONT = 'FFFFFFFF';
    private const META_FILL = 'FFF2F6FC';
    private const HEADER_FILL = 'FFD9E2F3';
    private const COMPETENCIA_FILL = 'FFBDD7EE';
    private const CAPACIDAD_FILL = 'FFDEEBF7';
    private const CRITERIO_FILL = 'FFEEF3FA';
    private const BIM_FILL = 'FFDCE6F1';
    private const SUBHEADER_FILL = 'FFF3F6FC';

    private const FILA_INICIO_TABLA = PlantillaRegistroAuxiliarLayout::FILA_INICIO_TABLA;

    private const FILAS_ENCABEZADO_TABLA = PlantillaRegistroAuxiliarLayout::FILAS_ENCABEZADO_TABLA;

    private const ALTURA_FILA_COMPETENCIA = 28;
    private const ALTURA_FILA_CAPACIDADES = 38;
    private const ALTURA_FILA_CRITERIOS = 65;
    private const ALTURA_FILA_SUBHEADER = 20;
    private const ALTURA_FILA_ESTUDIANTE = 20;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function generar(array $payload): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Registro auxiliar');
        $sheet->setShowGridlines(false);

        $ultimaCol = $this->ultimaColumna($payload);
        $colEstudianteId = $ultimaCol + 1;
        $payload['col_estudiante_id'] = $colEstudianteId;

        $this->escribirPortada($sheet, $payload['encabezado'] ?? [], $ultimaCol);
        $ultimaFilaDatos = $this->escribirTabla($sheet, self::FILA_INICIO_TABLA, $payload, $ultimaCol, $colEstudianteId);

        $filaEstudiantes = self::FILA_INICIO_TABLA + self::FILAS_ENCABEZADO_TABLA;
        $sheet->freezePane('C'.$filaEstudiantes);

        $this->ocultarColumnaEstudianteId($sheet, $colEstudianteId);
        $this->aplicarAnchos($sheet, $ultimaCol, $payload);
        $this->aplicarBordesExteriores($sheet, self::FILA_INICIO_TABLA, $ultimaCol, $ultimaFilaDatos);
        $this->escribirHojaMeta($spreadsheet, $payload);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $binary !== false ? $binary : '';
    }

    /**
     * @param  array{colegio: string, sede: string, anio_escolar: string, nivel: string, grado: string, seccion: string, bimestre: string|int, modo: string}  $resumen
     * @param  list<array{titulo: string, payload: array<string, mixed>}>  $hojasCurso
     */
    public function generarExcelAula(array $resumen, array $hojasCurso): string
    {
        $spreadsheet = new Spreadsheet;
        $estSheet = $spreadsheet->getActiveSheet();
        $estSheet->setTitle(PlantillaExcelAulaLayout::HOJA_ESTUDIANTES);
        $estSheet->setShowGridlines(false);
        $this->escribirHojaEstudiantesAula($estSheet, $resumen);

        foreach ($hojasCurso as $item) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($item['titulo']);
            $sheet->setShowGridlines(false);

            $payload = $item['payload'];
            $ultimaCol = $this->ultimaColumna($payload);
            $this->escribirPortada($sheet, $payload['encabezado'] ?? [], $ultimaCol, sinDocente: true);
            $ultimaFilaDatos = $this->escribirTabla(
                $sheet,
                self::FILA_INICIO_TABLA,
                $payload,
                $ultimaCol,
                0,
                modoAula: true,
            );

            $filaEstudiantes = self::FILA_INICIO_TABLA + self::FILAS_ENCABEZADO_TABLA;
            $sheet->freezePane('C'.$filaEstudiantes);
            $this->aplicarAnchos($sheet, $ultimaCol, $payload);
            $this->aplicarBordesExteriores($sheet, self::FILA_INICIO_TABLA, $ultimaCol, $ultimaFilaDatos);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $binary !== false ? $binary : '';
    }

    /**
     * @param  array{colegio: string, sede: string, anio_escolar: string, nivel: string, grado: string, seccion: string, bimestre: string|int, modo: string}  $resumen
     */
    private function escribirHojaEstudiantesAula(Worksheet $sheet, array $resumen): void
    {
        $ultimaLetra = 'F';

        $sheet->mergeCells("A1:{$ultimaLetra}1");
        $sheet->setCellValue('A1', $resumen['colegio'] ?? 'SIDERAE-Blenkir');
        $sheet->getRowDimension(1)->setRowHeight(26);
        $this->estiloCelda($sheet, "A1:{$ultimaLetra}1", [
            'bold' => true,
            'size' => 14,
            'fill' => self::TITLE_FILL,
            'fontColor' => self::TITLE_FONT,
            'h' => Alignment::HORIZONTAL_CENTER,
            'v' => Alignment::VERTICAL_CENTER,
        ]);

        $lineas = [
            ['Sede', $resumen['sede'] ?? 'Chilca'],
            ['Año escolar', $resumen['anio_escolar'] ?? ''],
            ['Nivel', mb_strtoupper((string) ($resumen['nivel'] ?? ''))],
            ['Grado', $resumen['grado'] ?? ''],
            ['Sección', $resumen['seccion'] ?? ''],
            ['Bimestre', $this->etiquetaBimestre($resumen['bimestre'] ?? '')],
            ['Modo', 'Sin datos'],
        ];

        $row = 2;
        foreach ($lineas as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->mergeCells("B{$row}:{$ultimaLetra}{$row}");
            $sheet->setCellValue("B{$row}", $value);
            $this->estiloCelda($sheet, "A{$row}", ['bold' => true, 'fill' => self::META_FILL, 'size' => 10]);
            $this->estiloCelda($sheet, "B{$row}:{$ultimaLetra}{$row}", ['fill' => self::META_FILL, 'size' => 10]);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        $sheet->getRowDimension($row)->setRowHeight(6);
        $headerRow = PlantillaExcelAulaLayout::FILA_ENCABEZADO_ESTUDIANTES;
        $sheet->setCellValue("A{$headerRow}", 'N°');
        $sheet->setCellValue("B{$headerRow}", 'Apellidos y nombres');
        $this->estiloCelda($sheet, "A{$headerRow}:B{$headerRow}", [
            'bold' => true,
            'fill' => self::HEADER_FILL,
            'h' => Alignment::HORIZONTAL_CENTER,
        ]);

        $inicio = PlantillaExcelAulaLayout::FILA_INICIO_DATOS;
        $fin = $inicio + PlantillaExcelAulaLayout::FILAS_ESTUDIANTES - 1;
        for ($fila = $inicio; $fila <= $fin; $fila++) {
            $sheet->setCellValue("A{$fila}", $fila - $inicio + 1);
            $this->estiloCelda($sheet, "A{$fila}", ['h' => Alignment::HORIZONTAL_CENTER]);
            $this->estiloCelda($sheet, "B{$fila}", [
                'h' => Alignment::HORIZONTAL_LEFT,
                'wrap' => true,
            ]);
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(42);
        $sheet->freezePane('A'.$inicio);
    }

    /**
     * @param  array<string, mixed>  $encabezado
     */
    private function escribirPortada(Worksheet $sheet, array $encabezado, int $ultimaCol, bool $sinDocente = false): void
    {
        $ultimaLetra = Coordinate::stringFromColumnIndex(max($ultimaCol, 6));

        $sheet->mergeCells("A1:{$ultimaLetra}1");
        $sheet->setCellValue('A1', $encabezado['titulo'] ?? 'REGISTRO AUXILIAR DE EVALUACIÓN DE LOS APRENDIZAJES');
        $sheet->getRowDimension(1)->setRowHeight(28);
        $this->estiloCelda($sheet, "A1:{$ultimaLetra}1", [
            'bold' => true,
            'size' => 14,
            'fill' => self::TITLE_FILL,
            'fontColor' => self::TITLE_FONT,
            'h' => Alignment::HORIZONTAL_CENTER,
            'v' => Alignment::VERTICAL_CENTER,
        ]);

        $bimestreLabel = $this->etiquetaBimestre($encabezado['bimestre'] ?? '');
        $sheet->mergeCells("A2:{$ultimaLetra}2");
        $sheet->setCellValue('A2', $bimestreLabel);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $this->estiloCelda($sheet, "A2:{$ultimaLetra}2", [
            'bold' => true,
            'size' => 12,
            'fill' => self::HEADER_FILL,
            'h' => Alignment::HORIZONTAL_CENTER,
            'v' => Alignment::VERTICAL_CENTER,
        ]);

        $metaBlocks = $sinDocente
            ? [
                ['A3', 'B3', 'D3', 'ÁREA', mb_strtoupper((string) ($encabezado['area'] ?? ''))],
                ['E3', 'F3', 'I3', 'CURSO', mb_strtoupper((string) ($encabezado['curso'] ?? ''))],
            ]
            : [
                ['A3', 'B3', 'C3', 'ÁREA', mb_strtoupper((string) ($encabezado['area'] ?? ''))],
                ['D3', 'E3', 'F3', 'CURSO', mb_strtoupper((string) ($encabezado['curso'] ?? ''))],
                ['G3', 'H3', 'I3', 'DOCENTE', (string) ($encabezado['docente'] ?? '')],
            ];
        foreach ($metaBlocks as [$c0, $c1, $cEnd, $label, $value]) {
            $sheet->setCellValue($c0, $label);
            $sheet->mergeCells("{$c1}:{$cEnd}");
            $sheet->setCellValue($c1, $value);
            $this->estiloCelda($sheet, $c0, ['bold' => true, 'fill' => self::META_FILL, 'h' => Alignment::HORIZONTAL_LEFT, 'size' => 9]);
            $this->estiloCelda($sheet, "{$c1}:{$cEnd}", ['fill' => self::META_FILL, 'wrap' => true, 'size' => 9]);
        }

        $contexto = sprintf(
            'AÑO %s · %s · GRADO %s · SECCIÓN %s · SEDE %s',
            $encabezado['anio_escolar'] ?? '',
            mb_strtoupper((string) ($encabezado['nivel'] ?? '')),
            $encabezado['grado'] ?? '',
            $encabezado['seccion'] ?? '',
            mb_strtoupper((string) ($encabezado['sede'] ?? '')),
        );
        $sheet->mergeCells("A4:{$ultimaLetra}4");
        $sheet->setCellValue('A4', $contexto);
        $this->estiloCelda($sheet, "A4:{$ultimaLetra}4", [
            'bold' => true,
            'fill' => self::META_FILL,
            'h' => Alignment::HORIZONTAL_CENTER,
            'size' => 10,
        ]);

        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(6);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function escribirTabla(
        Worksheet $sheet,
        int $startRow,
        array $payload,
        int $ultimaCol,
        int $colEstudianteId,
        bool $modoAula = false,
    ): int {
        $columnasCriterios = $payload['columnas_criterios'] ?? [];
        $columnasBimestral = $payload['columnas_bimestral'] ?? [];
        $columnasNota = $payload['columnas_nota'] ?? PlantillaRegistroAuxiliarLayout::columnasNotaLegacy();
        $colsPorCriterio = (int) ($payload['columnas_por_criterio'] ?? count($columnasNota));
        $incluirNotas = (bool) ($payload['incluir_notas'] ?? false);
        $pesosNivel = $payload['pesos_nivel_componentes'] ?? [];

        $rowComp = $startRow;
        $rowCap = $startRow + 1;
        $rowCrit = $startRow + 2;
        $rowSub = $startRow + 3;

        $bimColsStart = 3 + count($columnasCriterios) * $colsPorCriterio;
        $ultimaColIndex = $ultimaCol;
        $mapaColumnas = $this->construirMapaColumnas($columnasCriterios, $columnasBimestral, $bimColsStart, $colsPorCriterio);

        // Columnas fijas N° y Nombres (combinadas en 4 filas de encabezado)
        $sheet->mergeCellsByColumnAndRow(1, $rowComp, 1, $rowSub);
        $sheet->mergeCellsByColumnAndRow(2, $rowComp, 2, $rowSub);
        $sheet->setCellValueByColumnAndRow(1, $rowComp, 'N°');
        $sheet->setCellValueByColumnAndRow(2, $rowComp, 'NOMBRES Y APELLIDOS');
        $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex(1).$rowComp.':'.Coordinate::stringFromColumnIndex(2).$rowSub, [
            'bold' => true,
            'fill' => self::HEADER_FILL,
            'h' => Alignment::HORIZONTAL_CENTER,
            'v' => Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'border' => Border::BORDER_MEDIUM,
        ]);

        // Merges competencia / capacidad / criterio (desde columna 3)
        $this->aplicarMergeAgrupado($sheet, $rowComp, $columnasCriterios, 'competencia_nombre', $colsPorCriterio, self::COMPETENCIA_FILL, true);
        $this->aplicarMergeAgrupado($sheet, $rowCap, $columnasCriterios, 'capacidad_nombre', $colsPorCriterio, self::CAPACIDAD_FILL, false);
        $idx = 3;
        foreach ($columnasCriterios as $col) {
            $sheet->mergeCellsByColumnAndRow($idx, $rowCrit, $idx + $colsPorCriterio - 1, $rowCrit);
            $sheet->setCellValueByColumnAndRow($idx, $rowCrit, $col['criterio_titulo'] ?? '');
            $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($idx).$rowCrit.':'.Coordinate::stringFromColumnIndex($idx + $colsPorCriterio - 1).$rowCrit, [
                'bold' => true,
                'fill' => self::CRITERIO_FILL,
                'h' => Alignment::HORIZONTAL_CENTER,
                'v' => Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ]);
            $idx += $colsPorCriterio;
        }

        // Subcolumnas de notas (legacy C/L/T o componentes dinámicos + CE)
        $idx = 3;
        foreach ($columnasCriterios as $_) {
            foreach ($columnasNota as $def) {
                $etiqueta = $def['etiqueta'] ?? '';
                if (($def['tipo'] ?? '') === 'componente' && isset($def['peso'])) {
                    $etiqueta = sprintf('%s (%s%%)', $def['etiqueta'], round((float) $def['peso']));
                }
                $sheet->setCellValueByColumnAndRow($idx, $rowSub, $etiqueta);
                $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($idx).$rowSub, [
                    'bold' => true,
                    'fill' => self::SUBHEADER_FILL,
                    'h' => Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'size' => 9,
                    'wrap' => true,
                ]);
                $idx++;
            }
        }

        // Encabezado bimestral integrado
        if ($bimColsStart <= $ultimaColIndex && count($columnasBimestral) > 0) {
            $this->escribirEncabezadoBimestral(
                $sheet,
                $columnasBimestral,
                $bimColsStart,
                $ultimaColIndex,
                $rowComp,
                $rowCap,
                $rowCrit,
                $rowSub,
            );
        }

        $sheet->getRowDimension($rowComp)->setRowHeight(self::ALTURA_FILA_COMPETENCIA);
        $sheet->getRowDimension($rowCap)->setRowHeight(self::ALTURA_FILA_CAPACIDADES);
        $sheet->getRowDimension($rowCrit)->setRowHeight(self::ALTURA_FILA_CRITERIOS);
        $sheet->getRowDimension($rowSub)->setRowHeight(self::ALTURA_FILA_SUBHEADER);

        // Borde grueso entre criterios y bimestral
        if ($bimColsStart <= $ultimaColIndex && count($columnasCriterios) > 0) {
            $colBimLetter = Coordinate::stringFromColumnIndex($bimColsStart);
            $range = $colBimLetter.$rowComp.':'.Coordinate::stringFromColumnIndex($ultimaColIndex).$rowSub;
            $sheet->getStyle($range)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        }

        // Filas estudiantes
        $dataRow = $rowSub + 1;
        foreach ($payload['estudiantes'] ?? [] as $est) {
            $sheet->getRowDimension($dataRow)->setRowHeight(self::ALTURA_FILA_ESTUDIANTE);

            if ($modoAula) {
                $hojaEst = PlantillaExcelAulaLayout::HOJA_ESTUDIANTES;
                $this->asignarFormulaCelda($sheet, 1, $dataRow, "={$hojaEst}!A{$dataRow}");
                $this->asignarFormulaCelda($sheet, 2, $dataRow, "={$hojaEst}!B{$dataRow}");
            } else {
                $sheet->setCellValueByColumnAndRow(1, $dataRow, $est['numero'] ?? '');
                $sheet->setCellValueByColumnAndRow(2, $dataRow, $est['nombre'] ?? '');
                if (isset($est['estudiante_id']) && $colEstudianteId > 0) {
                    $sheet->setCellValueByColumnAndRow($colEstudianteId, $dataRow, (int) $est['estudiante_id']);
                }
            }
            $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex(1).$dataRow, [
                'h' => Alignment::HORIZONTAL_CENTER,
                'v' => Alignment::VERTICAL_CENTER,
            ]);
            $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex(2).$dataRow, [
                'h' => Alignment::HORIZONTAL_LEFT,
                'v' => Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ]);

            $idx = 3;
            foreach ($est['notas_criterio'] ?? [] as $notas) {
                $valores = $notas['valores'] ?? [];
                $startColGrupo = $idx;

                for ($i = 0; $i < count($columnasNota); $i++) {
                    $def = $columnasNota[$i];
                    $col = $idx + $i;
                    $valor = $valores[$i] ?? null;

                    if (($def['tipo'] ?? '') === 'ce') {
                        if ($incluirNotas) {
                            $this->setNumericCell($sheet, $col, $dataRow, $valor);
                        } else {
                            $sheet->setCellValueByColumnAndRow(
                                $col,
                                $dataRow,
                                $this->formulaCeGrupoCriterio($startColGrupo, $dataRow, $columnasNota),
                            );
                        }
                    } elseif ($incluirNotas) {
                        $this->setNumericCell($sheet, $col, $dataRow, $valor);
                    }

                    $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($col).$dataRow, [
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                    ]);
                }

                $idx += count($columnasNota);
            }

            if (! $incluirNotas) {
                $this->escribirFormulasBimestrales($sheet, $mapaColumnas, $pesosNivel, $dataRow);
            }

            $bIdx = $bimColsStart;
            foreach ($est['bimestral'] ?? [] as $i => $valor) {
                $colDef = $columnasBimestral[$i] ?? [];
                $tipo = $colDef['tipo'] ?? '';
                $esFormula = ! $incluirNotas && in_array($tipo, [
                    'promedio_criterios',
                    'promedio_eta',
                    'nivel_numerico',
                    'nivel_literal',
                ], true);

                if (! $esFormula && $valor !== null && $valor !== '') {
                    if (is_numeric($valor)) {
                        $this->setNumericCell($sheet, $bIdx, $dataRow, $valor);
                    } else {
                        $sheet->setCellValueByColumnAndRow($bIdx, $dataRow, $valor);
                    }
                }

                $esConclusion = $tipo === 'conclusion';
                $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($bIdx).$dataRow, [
                    'h' => $esConclusion ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'wrap' => $esConclusion,
                ]);
                $bIdx++;
            }

            $dataRow++;
        }

        if ($dataRow > $rowSub + 1) {
            $bodyRange = 'A'.($rowSub + 1).':'.Coordinate::stringFromColumnIndex($ultimaColIndex).($dataRow - 1);
            $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $headerRange = 'A'.$rowComp.':'.Coordinate::stringFromColumnIndex($ultimaColIndex).$rowSub;
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return max($dataRow - 1, $rowSub);
    }

    /**
     * @param  list<array<string, mixed>>  $columnasCriterios
     * @param  list<array<string, mixed>>  $columnasBimestral
     * @return array{
     *     ce_cols: list<int>,
     *     bim: array{
     *         promedio_criterios: ?int,
     *         oral: ?int,
     *         promedio_eta: ?int,
     *         examen_bimestral: ?int,
     *         nivel_numerico: ?int,
     *         nivel_literal: ?int,
     *         etas: list<int>,
     *         personalizados: array<int, int>
     *     }
     * }
     */
    private function construirMapaColumnas(array $columnasCriterios, array $columnasBimestral, int $bimColsStart, int $colsPorCriterio): array
    {
        $ceCols = [];
        $col = 3;
        foreach ($columnasCriterios as $_) {
            $ceCols[] = $col + $colsPorCriterio - 1;
            $col += $colsPorCriterio;
        }

        $bim = [
            'promedio_criterios' => null,
            'oral' => null,
            'promedio_eta' => null,
            'examen_bimestral' => null,
            'nivel_numerico' => null,
            'nivel_literal' => null,
            'etas' => [],
            'personalizados' => [],
        ];

        $c = $bimColsStart;
        foreach ($columnasBimestral as $def) {
            $tipo = $def['tipo'] ?? '';
            if ($tipo === 'eta') {
                $bim['etas'][] = $c;
            } elseif ($tipo === 'personalizado' && isset($def['componente_id'])) {
                $bim['personalizados'][(int) $def['componente_id']] = $c;
            } elseif (array_key_exists($tipo, $bim) && ! is_array($bim[$tipo])) {
                $bim[$tipo] = $c;
            }
            $c++;
        }

        return ['ce_cols' => $ceCols, 'bim' => $bim];
    }

    /**
     * @param  array{
     *     ce_cols: list<int>,
     *     bim: array{
     *         promedio_criterios: ?int,
     *         oral: ?int,
     *         promedio_eta: ?int,
     *         examen_bimestral: ?int,
     *         nivel_numerico: ?int,
     *         nivel_literal: ?int,
     *         etas: list<int>
     *     }
     * }  $mapa
     * @param  list<array{codigo: string, peso: float}>  $pesosNivel
     */
    private function escribirFormulasBimestrales(Worksheet $sheet, array $mapa, array $pesosNivel, int $dataRow): void
    {
        $bim = $mapa['bim'];

        if ($bim['promedio_criterios'] !== null && $mapa['ce_cols'] !== []) {
            $sheet->setCellValueByColumnAndRow(
                $bim['promedio_criterios'],
                $dataRow,
                $this->formulaPromedioCriterio($mapa['ce_cols'], $dataRow),
            );
        }

        if ($bim['promedio_eta'] !== null && $bim['etas'] !== []) {
            $sheet->setCellValueByColumnAndRow(
                $bim['promedio_eta'],
                $dataRow,
                $this->formulaPromedioEta($bim['etas'], $dataRow),
            );
        }

        if ($bim['nivel_numerico'] !== null && $pesosNivel !== []) {
            $sheet->setCellValueByColumnAndRow(
                $bim['nivel_numerico'],
                $dataRow,
                $this->formulaNivelNumerico($bim, $pesosNivel, $dataRow),
            );
        }

        if ($bim['nivel_literal'] !== null && $bim['nivel_numerico'] !== null) {
            $sheet->setCellValueByColumnAndRow(
                $bim['nivel_literal'],
                $dataRow,
                $this->formulaNivelLiteral($bim['nivel_numerico'], $dataRow),
            );
        }
    }

    /**
     * @param  list<int>  $ceCols
     */
    private function formulaPromedioCriterio(array $ceCols, int $row): string
    {
        $refs = array_map(
            fn (int $col) => Coordinate::stringFromColumnIndex($col).$row,
            $ceCols,
        );
        $joined = implode(',', $refs);

        return sprintf('=IFERROR(IF(COUNT(%1$s)=0,"",ROUND(AVERAGE(%1$s),2)),"")', $joined);
    }

    /**
     * @param  list<int>  $etaCols
     */
    private function formulaPromedioEta(array $etaCols, int $row): string
    {
        $refs = array_map(
            fn (int $col) => Coordinate::stringFromColumnIndex($col).$row,
            $etaCols,
        );
        $joined = implode(',', $refs);

        return sprintf('=IFERROR(IF(COUNT(%1$s)=0,"",ROUND(AVERAGE(%1$s),2)),"")', $joined);
    }

    /**
     * @param  array{
     *     promedio_criterios: ?int,
     *     oral: ?int,
     *     promedio_eta: ?int,
     *     examen_bimestral: ?int,
     *     nivel_numerico: ?int,
     *     nivel_literal: ?int,
     *     etas: list<int>,
     *     personalizados: array<int, int>
     * }  $bim
     * @param  list<array{codigo?: string, tipo?: string, componente_id?: int, peso: float}>  $pesosNivel
     */
    private function formulaNivelNumerico(array $bim, array $pesosNivel, int $row): string
    {
        $codigoCol = [
            'promedio_criterios' => $bim['promedio_criterios'],
            'oral' => $bim['oral'],
            'promedio_eta' => $bim['promedio_eta'],
            'examen_bimestral' => $bim['examen_bimestral'],
        ];

        $refs = [];
        $terms = [];
        foreach ($pesosNivel as $comp) {
            if (($comp['tipo'] ?? '') === 'personalizado') {
                $col = $bim['personalizados'][$comp['componente_id'] ?? 0] ?? null;
            } else {
                $col = $codigoCol[$comp['codigo'] ?? ''] ?? null;
            }

            if ($col === null) {
                continue;
            }
            $ref = Coordinate::stringFromColumnIndex($col).$row;
            $refs[] = $ref;
            $terms[] = sprintf('%s*%s', $ref, round($comp['peso'] / 100, 4));
        }

        if ($refs === []) {
            return '=""';
        }

        $expected = count($refs);

        return sprintf(
            '=IFERROR(IF(COUNT(%s)<%d,"",ROUND(%s,2)),"")',
            implode(',', $refs),
            $expected,
            implode('+', $terms),
        );
    }

    private function formulaNivelLiteral(int $nivelCol, int $row): string
    {
        $ref = Coordinate::stringFromColumnIndex($nivelCol).$row;

        return sprintf(
            '=IF(%1$s="","",IF(%1$s>=18,"AD",IF(%1$s>=14,"A",IF(%1$s>=11,"B","C"))))',
            $ref,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $columnasBimestral
     */
    private function escribirEncabezadoBimestral(
        Worksheet $sheet,
        array $columnasBimestral,
        int $bimColsStart,
        int $ultimaColIndex,
        int $rowComp,
        int $rowCap,
        int $rowCrit,
        int $rowSub,
    ): void {
        $sheet->mergeCellsByColumnAndRow($bimColsStart, $rowComp, $ultimaColIndex, $rowCap);
        $sheet->setCellValueByColumnAndRow($bimColsStart, $rowComp, 'EVALUACIÓN BIMESTRAL');
        $this->estiloCelda(
            $sheet,
            Coordinate::stringFromColumnIndex($bimColsStart).$rowComp.':'.Coordinate::stringFromColumnIndex($ultimaColIndex).$rowCap,
            [
                'bold' => true,
                'fill' => self::BIM_FILL,
                'h' => Alignment::HORIZONTAL_CENTER,
                'v' => Alignment::VERTICAL_CENTER,
                'wrap' => true,
                'size' => 11,
            ],
        );

        $col = $bimColsStart;
        $i = 0;
        while ($i < count($columnasBimestral)) {
            $def = $columnasBimestral[$i];
            $tipo = $def['tipo'] ?? '';

            if ($tipo === 'eta') {
                $etaStart = $col;
                $j = $i;
                while ($j < count($columnasBimestral) && ($columnasBimestral[$j]['tipo'] ?? '') === 'eta') {
                    $j++;
                }
                $etaEnd = $col + ($j - $i) - 1;
                $sheet->mergeCellsByColumnAndRow($etaStart, $rowCrit, $etaEnd, $rowCrit);
                $sheet->setCellValueByColumnAndRow($etaStart, $rowCrit, 'ETA');
                $this->estiloCelda(
                    $sheet,
                    Coordinate::stringFromColumnIndex($etaStart).$rowCrit.':'.Coordinate::stringFromColumnIndex($etaEnd).$rowCrit,
                    [
                        'bold' => true,
                        'fill' => self::BIM_FILL,
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                        'wrap' => true,
                    ],
                );
                for ($k = $i; $k < $j; $k++) {
                    $sheet->setCellValueByColumnAndRow($col, $rowSub, $columnasBimestral[$k]['etiqueta'] ?? ('ETA '.($k - $i + 1)));
                    $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($col).$rowSub, [
                        'bold' => true,
                        'fill' => self::SUBHEADER_FILL,
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                        'size' => 8,
                        'wrap' => true,
                    ]);
                    $col++;
                }
                $i = $j;
                continue;
            }

            if ($tipo === 'personalizado') {
                $sheet->setCellValueByColumnAndRow($col, $rowSub, $def['etiqueta'] ?? '');
                $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($col).$rowSub, [
                    'bold' => true,
                    'fill' => self::SUBHEADER_FILL,
                    'h' => Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'size' => 9,
                    'wrap' => true,
                ]);
                $col++;
                $i++;

                continue;
            }

            if ($tipo === 'nivel_numerico' && ($columnasBimestral[$i + 1]['tipo'] ?? '') === 'nivel_literal') {
                $sheet->mergeCellsByColumnAndRow($col, $rowCrit, $col + 1, $rowCrit);
                $sheet->setCellValueByColumnAndRow($col, $rowCrit, 'NIVEL DE LOGRO EN EL BIMESTRE');
                $this->estiloCelda(
                    $sheet,
                    Coordinate::stringFromColumnIndex($col).$rowCrit.':'.Coordinate::stringFromColumnIndex($col + 1).$rowCrit,
                    [
                        'bold' => true,
                        'fill' => self::BIM_FILL,
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                        'wrap' => true,
                    ],
                );
                $sheet->setCellValueByColumnAndRow($col, $rowSub, 'NUM.');
                $sheet->setCellValueByColumnAndRow($col + 1, $rowSub, 'LIT.');
                foreach ([$col, $col + 1] as $c) {
                    $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($c).$rowSub, [
                        'bold' => true,
                        'fill' => self::SUBHEADER_FILL,
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                        'size' => 8,
                    ]);
                }
                $col += 2;
                $i += 2;
                continue;
            }

            $mergeRows = in_array($tipo, ['promedio_criterios', 'oral', 'promedio_eta', 'examen_bimestral', 'conclusion'], true);
            if ($mergeRows) {
                $sheet->mergeCellsByColumnAndRow($col, $rowCrit, $col, $rowSub);
            }
            $etiqueta = $def['etiqueta'] ?? '';
            if ($tipo === 'conclusion') {
                $etiqueta = 'CONCLUSIONES DESCRIPTIVAS';
            }
            $sheet->setCellValueByColumnAndRow($col, $rowCrit, $etiqueta);
            $endRow = $mergeRows ? $rowSub : $rowCrit;
            $this->estiloCelda(
                $sheet,
                Coordinate::stringFromColumnIndex($col).$rowCrit.':'.Coordinate::stringFromColumnIndex($col).$endRow,
                [
                    'bold' => true,
                    'fill' => self::BIM_FILL,
                    'h' => Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'wrap' => true,
                    'size' => $tipo === 'conclusion' ? 8 : 9,
                ],
            );
            $col++;
            $i++;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $columnas
     */
    private function aplicarMergeAgrupado(
        Worksheet $sheet,
        int $row,
        array $columnas,
        string $campo,
        int $span,
        string $fill,
        bool $bold,
    ): void {
        if ($columnas === []) {
            return;
        }

        $idx = 3;
        $i = 0;
        while ($i < count($columnas)) {
            $nombre = $columnas[$i][$campo] ?? '';
            $j = $i + 1;
            while ($j < count($columnas) && ($columnas[$j][$campo] ?? '') === $nombre) {
                $j++;
            }
            $start = $idx;
            $end = $idx + ($j - $i) * $span - 1;
            $sheet->mergeCellsByColumnAndRow($start, $row, $end, $row);
            $sheet->setCellValueByColumnAndRow($start, $row, mb_strtoupper($nombre));
            $this->estiloCelda(
                $sheet,
                Coordinate::stringFromColumnIndex($start).$row.':'.Coordinate::stringFromColumnIndex($end).$row,
                [
                    'bold' => $bold,
                    'fill' => $fill,
                    'h' => Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'wrap' => true,
                ],
            );
            $idx = $end + 1;
            $i = $j;
        }
    }

    /**
     * @param  array<string, mixed>  $opts
     */
    private function estiloCelda(Worksheet $sheet, string $range, array $opts): void
    {
        $style = $sheet->getStyle($range);
        if ($opts['bold'] ?? false) {
            $style->getFont()->setBold(true);
        }
        if (isset($opts['size'])) {
            $style->getFont()->setSize($opts['size']);
        }
        if (isset($opts['fontColor'])) {
            $style->getFont()->getColor()->setARGB($opts['fontColor']);
        }
        if (isset($opts['fill'])) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($opts['fill']);
        }
        if (isset($opts['h'])) {
            $style->getAlignment()->setHorizontal($opts['h']);
        }
        if (isset($opts['v'])) {
            $style->getAlignment()->setVertical($opts['v']);
        }
        if ($opts['wrap'] ?? false) {
            $style->getAlignment()->setWrapText(true);
        }
        if ($opts['shrink'] ?? false) {
            $style->getAlignment()->setShrinkToFit(true);
        }
        if (isset($opts['border'])) {
            $style->getBorders()->getAllBorders()->setBorderStyle($opts['border']);
        }
    }

    private function setNumericCell(Worksheet $sheet, int $col, int $row, mixed $valor): void
    {
        if ($valor === null || $valor === '') {
            return;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, is_numeric($valor) ? (float) $valor : $valor);
    }

    private function aplicarBordesExteriores(Worksheet $sheet, int $startRow, int $ultimaCol, int $ultimaFila): void
    {
        if ($ultimaFila < $startRow) {
            return;
        }
        $range = 'A'.$startRow.':'.Coordinate::stringFromColumnIndex($ultimaCol).$ultimaFila;
        $borders = $sheet->getStyle($range)->getBorders();
        $borders->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $borders->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $borders->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $borders->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ultimaColumna(array $payload): int
    {
        $colsPorCriterio = (int) ($payload['columnas_por_criterio'] ?? 4);
        $criterios = count($payload['columnas_criterios'] ?? []) * $colsPorCriterio;
        $bim = count($payload['columnas_bimestral'] ?? []);

        return 2 + $criterios + $bim;
    }

    /**
     * @param  list<array<string, mixed>>  $columnasNota
     */
    private function formulaCeGrupoCriterio(int $startCol, int $row, array $columnasNota): string
    {
        $refs = [];
        $terms = [];
        $pesos = [];

        foreach ($columnasNota as $i => $def) {
            if (($def['tipo'] ?? '') === 'ce') {
                continue;
            }

            $ref = Coordinate::stringFromColumnIndex($startCol + $i).$row;
            $refs[] = $ref;

            if (isset($def['peso'])) {
                $peso = (float) $def['peso'];
                $pesos[] = $peso;
                $terms[] = sprintf('%s*%s', $ref, round($peso / 100, 4));
            }
        }

        if ($refs === []) {
            return '=""';
        }

        $joined = implode(',', $refs);

        if ($pesos === [] || count(array_unique(array_map(fn (float $p) => round($p, 2), $pesos))) <= 1) {
            return sprintf('=IFERROR(IF(COUNT(%1$s)=0,"",ROUND(AVERAGE(%1$s),2)),"")', $joined);
        }

        return sprintf('=IFERROR(IF(COUNT(%1$s)=0,"",ROUND(%2$s,2)),"")', $joined, implode('+', $terms));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function escribirHojaMeta(Spreadsheet $spreadsheet, array $payload): void
    {
        $meta = $spreadsheet->createSheet();
        $meta->setTitle(PlantillaRegistroAuxiliarLayout::META_SHEET);
        $meta->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $filas = [
            ['plantilla_version', (string) PlantillaRegistroAuxiliarLayout::PLANTILLA_VERSION],
            ['modo_calificacion', (string) ($payload['modo_calificacion_plantilla'] ?? PlantillaRegistroAuxiliarLayout::MODO_LEGACY)],
            ['periodo_academico_id', (string) ($payload['periodo_academico_id'] ?? '')],
            ['anio_escolar', (string) ($payload['encabezado']['anio_escolar'] ?? '')],
            ['nivel', (string) ($payload['encabezado']['nivel'] ?? '')],
            ['grado', (string) ($payload['encabezado']['grado'] ?? '')],
            ['seccion', (string) ($payload['encabezado']['seccion'] ?? '')],
            ['sede', (string) ($payload['encabezado']['sede'] ?? '')],
            ['asignacion_docente_id', (string) ($payload['asignacion_docente_id'] ?? '')],
            ['fila_inicio_datos', (string) PlantillaRegistroAuxiliarLayout::FILA_INICIO_DATOS],
            ['col_estudiante_id', (string) ($payload['col_estudiante_id'] ?? '')],
            ['componentes_json', json_encode($payload['componentes_calificacion'] ?? [], JSON_UNESCAPED_UNICODE)],
            ['mapeo_importacion_json', json_encode($payload['mapeo_importacion'] ?? [], JSON_UNESCAPED_UNICODE)],
            ['columnas_bimestral_json', json_encode($payload['columnas_bimestral'] ?? [], JSON_UNESCAPED_UNICODE)],
        ];

        $row = 1;
        foreach ($filas as [$clave, $valor]) {
            $meta->setCellValue("A{$row}", $clave);
            $meta->setCellValue("B{$row}", $valor);
            $row++;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function aplicarAnchos(Worksheet $sheet, int $ultimaCol, array $payload): void
    {
        $sheet->getColumnDimension('A')->setWidth(3.5);
        $sheet->getColumnDimension('B')->setWidth(36);

        $col = 3;
        $columnasNota = $payload['columnas_nota'] ?? PlantillaRegistroAuxiliarLayout::columnasNotaLegacy();
        foreach ($payload['columnas_criterios'] ?? [] as $_) {
            foreach ($columnasNota as $def) {
                $letter = Coordinate::stringFromColumnIndex($col++);
                $width = (($def['tipo'] ?? '') === 'ce') ? 5.5 : 5.5;
                if (($def['tipo'] ?? '') === 'componente') {
                    $width = 7.5;
                }
                $sheet->getColumnDimension($letter)->setWidth($width);
            }
        }

        $columnasBim = $payload['columnas_bimestral'] ?? [];
        for ($i = 0; $i < count($columnasBim); $i++) {
            $letter = Coordinate::stringFromColumnIndex($col++);
            $tipo = $columnasBim[$i]['tipo'] ?? '';
            $width = match ($tipo) {
                'conclusion' => 32,
                'nivel_literal' => 5,
                'nivel_numerico' => 7,
                'eta' => 6,
                'oral', 'examen_bimestral', 'promedio_criterios', 'promedio_eta' => 10,
                default => 10,
            };
            $sheet->getColumnDimension($letter)->setWidth($width);
        }
    }

    private function etiquetaBimestre(mixed $bimestre): string
    {
        $n = (int) $bimestre;
        $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        return ($romanos[$n] ?? (string) $n).' BIMESTRE';
    }

    private function ocultarColumnaEstudianteId(Worksheet $sheet, int $colEstudianteId): void
    {
        $letter = Coordinate::stringFromColumnIndex($colEstudianteId);
        $dimension = $sheet->getColumnDimension($letter);
        $dimension->setVisible(false);
        $dimension->setWidth(0);
        $dimension->setCollapsed(true);
    }

    private function asignarFormulaCelda(Worksheet $sheet, int $col, int $row, string $formula): void
    {
        $coordinate = Coordinate::stringFromColumnIndex($col).$row;
        $sheet->getCell($coordinate)->setValueExplicit($formula, DataType::TYPE_FORMULA);
    }
}
