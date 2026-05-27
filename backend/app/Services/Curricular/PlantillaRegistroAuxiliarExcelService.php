<?php

namespace App\Services\Curricular;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

    /** Fila donde inicia la tabla (encabezado competencia). */
    public const FILA_INICIO_TABLA = 6;

    /** Filas de encabezado de columnas antes de estudiantes. */
    private const FILAS_ENCABEZADO_TABLA = 4;

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

        $this->escribirPortada($sheet, $payload['encabezado'] ?? [], $ultimaCol);
        $ultimaFilaDatos = $this->escribirTabla($sheet, self::FILA_INICIO_TABLA, $payload, $ultimaCol);

        $filaEstudiantes = self::FILA_INICIO_TABLA + self::FILAS_ENCABEZADO_TABLA;
        $sheet->freezePane('C'.$filaEstudiantes);

        $this->aplicarAnchos($sheet, $ultimaCol, $payload);
        $this->aplicarBordesExteriores($sheet, self::FILA_INICIO_TABLA, $ultimaCol, $ultimaFilaDatos);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $binary !== false ? $binary : '';
    }

    /**
     * @param  array<string, mixed>  $encabezado
     */
    private function escribirPortada(Worksheet $sheet, array $encabezado, int $ultimaCol): void
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

        $metaBlocks = [
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
    private function escribirTabla(Worksheet $sheet, int $startRow, array $payload, int $ultimaCol): int
    {
        $columnasCriterios = $payload['columnas_criterios'] ?? [];
        $columnasBimestral = $payload['columnas_bimestral'] ?? [];
        $incluirNotas = (bool) ($payload['incluir_notas'] ?? false);
        $pesosNivel = $payload['pesos_nivel_componentes'] ?? [];

        $rowComp = $startRow;
        $rowCap = $startRow + 1;
        $rowCrit = $startRow + 2;
        $rowSub = $startRow + 3;

        $bimColsStart = 3 + count($columnasCriterios) * 4;
        $ultimaColIndex = $ultimaCol;
        $mapaColumnas = $this->construirMapaColumnas($columnasCriterios, $columnasBimestral, $bimColsStart);

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
        $this->aplicarMergeAgrupado($sheet, $rowComp, $columnasCriterios, 'competencia_nombre', 4, self::COMPETENCIA_FILL, true);
        $this->aplicarMergeAgrupado($sheet, $rowCap, $columnasCriterios, 'capacidad_nombre', 4, self::CAPACIDAD_FILL, false);
        $idx = 3;
        foreach ($columnasCriterios as $col) {
            $sheet->mergeCellsByColumnAndRow($idx, $rowCrit, $idx + 3, $rowCrit);
            $sheet->setCellValueByColumnAndRow($idx, $rowCrit, $col['criterio_titulo'] ?? '');
            $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($idx).$rowCrit.':'.Coordinate::stringFromColumnIndex($idx + 3).$rowCrit, [
                'bold' => true,
                'fill' => self::CRITERIO_FILL,
                'h' => Alignment::HORIZONTAL_CENTER,
                'v' => Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ]);
            $idx += 4;
        }

        // Subcolumnas C L T CE
        $idx = 3;
        foreach ($columnasCriterios as $_) {
            foreach (['C', 'L', 'T', 'CE'] as $sub) {
                $sheet->setCellValueByColumnAndRow($idx, $rowSub, $sub);
                $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($idx).$rowSub, [
                    'bold' => true,
                    'fill' => self::SUBHEADER_FILL,
                    'h' => Alignment::HORIZONTAL_CENTER,
                    'v' => Alignment::VERTICAL_CENTER,
                    'size' => 9,
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

            $sheet->setCellValueByColumnAndRow(1, $dataRow, $est['numero'] ?? '');
            $sheet->setCellValueByColumnAndRow(2, $dataRow, $est['nombre'] ?? '');
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
                $cCol = $idx;
                $lCol = $idx + 1;
                $tCol = $idx + 2;
                $ceCol = $idx + 3;

                if ($incluirNotas) {
                    $this->setNumericCell($sheet, $cCol, $dataRow, $notas['c'] ?? null);
                    $this->setNumericCell($sheet, $lCol, $dataRow, $notas['l'] ?? null);
                    $this->setNumericCell($sheet, $tCol, $dataRow, $notas['t'] ?? null);
                    $this->setNumericCell($sheet, $ceCol, $dataRow, $notas['ce'] ?? null);
                } else {
                    $cLetter = Coordinate::stringFromColumnIndex($cCol);
                    $lLetter = Coordinate::stringFromColumnIndex($lCol);
                    $tLetter = Coordinate::stringFromColumnIndex($tCol);
                    $formula = sprintf(
                        '=IFERROR(IF(COUNT(%1$s,%2$s,%3$s)=0,"",ROUND(AVERAGE(%1$s,%2$s,%3$s),2)),"")',
                        $cLetter.$dataRow,
                        $lLetter.$dataRow,
                        $tLetter.$dataRow,
                    );
                    $sheet->setCellValueByColumnAndRow($ceCol, $dataRow, $formula);
                }

                foreach ([$cCol, $lCol, $tCol, $ceCol] as $c) {
                    $this->estiloCelda($sheet, Coordinate::stringFromColumnIndex($c).$dataRow, [
                        'h' => Alignment::HORIZONTAL_CENTER,
                        'v' => Alignment::VERTICAL_CENTER,
                    ]);
                }
                $idx += 4;
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
     *         etas: list<int>
     *     }
     * }
     */
    private function construirMapaColumnas(array $columnasCriterios, array $columnasBimestral, int $bimColsStart): array
    {
        $ceCols = [];
        $col = 3;
        foreach ($columnasCriterios as $_) {
            $ceCols[] = $col + 3;
            $col += 4;
        }

        $bim = [
            'promedio_criterios' => null,
            'oral' => null,
            'promedio_eta' => null,
            'examen_bimestral' => null,
            'nivel_numerico' => null,
            'nivel_literal' => null,
            'etas' => [],
        ];

        $c = $bimColsStart;
        foreach ($columnasBimestral as $def) {
            $tipo = $def['tipo'] ?? '';
            if ($tipo === 'eta') {
                $bim['etas'][] = $c;
            } elseif (array_key_exists($tipo, $bim)) {
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
     *     etas: list<int>
     * }  $bim
     * @param  list<array{codigo: string, peso: float}>  $pesosNivel
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
            $col = $codigoCol[$comp['codigo']] ?? null;
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
        $criterios = count($payload['columnas_criterios'] ?? []) * 4;
        $bim = count($payload['columnas_bimestral'] ?? []);

        return 2 + $criterios + $bim;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function aplicarAnchos(Worksheet $sheet, int $ultimaCol, array $payload): void
    {
        $sheet->getColumnDimension('A')->setWidth(3.5);
        $sheet->getColumnDimension('B')->setWidth(36);

        $col = 3;
        foreach ($payload['columnas_criterios'] ?? [] as $_) {
            foreach ([4, 4, 4, 5.5] as $w) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col++))->setWidth($w);
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
}
