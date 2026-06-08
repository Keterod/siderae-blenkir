<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use App\Services\Curricular\PlantillaExcelAulaLayout;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\Test;

class ExcelAulaTest extends CurricularApiTestCase
{
    /**
     * @return array<string, string|int>
     */
    private function queryExcelAula(): array
    {
        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', '2026')
            ->where('bimestre', '1')
            ->firstOrFail();

        return [
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'periodo_academico_id' => $periodo->id,
            'modo' => PlantillaExcelAulaLayout::MODO_SIN_DATOS,
        ];
    }

    #[Test]
    public function administrador_puede_descargar_excel_aula(): void
    {
        $this->actingAs($this->administrador())
            ->get('/api/curricular/excel-aula?'.http_build_query($this->queryExcelAula()))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function coordinador_puede_descargar_excel_aula(): void
    {
        $binary = $this->descargarExcelAula($this->coordinador());
        $spreadsheet = $this->cargarSpreadsheet($binary);

        $this->assertNotNull($spreadsheet->getSheetByName(PlantillaExcelAulaLayout::HOJA_ESTUDIANTES));
        $this->assertGreaterThan(1, $spreadsheet->getSheetCount());
    }

    #[Test]
    public function docente_recibe_403_al_descargar_excel_aula(): void
    {
        $this->actingAs($this->docente())
            ->get('/api/curricular/excel-aula?'.http_build_query($this->queryExcelAula()))
            ->assertForbidden();
    }

    #[Test]
    public function directivo_recibe_403_al_descargar_excel_aula(): void
    {
        $this->actingAs($this->directivo())
            ->get('/api/curricular/excel-aula?'.http_build_query($this->queryExcelAula()))
            ->assertForbidden();
    }

    #[Test]
    public function responde_422_si_no_hay_cursos_activos_en_malla(): void
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do',
        )->assertOk();

        MallaCurso::query()->update(['activo' => false]);

        $response = $this->actingAs($this->coordinador())
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/curricular/excel-aula?'.http_build_query($this->queryExcelAula()));

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cursos']);

        $this->assertStringContainsString(
            'No hay cursos activos en la malla para este nivel y grado.',
            (string) ($response->json('errors.cursos.0') ?? ''),
        );
    }

    #[Test]
    public function excel_contiene_hoja_estudiantes_y_hojas_de_cursos(): void
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do',
        )->assertOk();

        $cantidadCursos = MallaCurso::query()->where('activo', true)->count();
        $this->assertGreaterThan(0, $cantidadCursos);

        $spreadsheet = $this->cargarSpreadsheet($this->descargarExcelAula($this->coordinador()));

        $this->assertNotNull($spreadsheet->getSheetByName(PlantillaExcelAulaLayout::HOJA_ESTUDIANTES));
        $this->assertSame(1 + $cantidadCursos, $spreadsheet->getSheetCount());
    }

    #[Test]
    public function excel_aula_incluye_columna_componente_personalizado_activo(): void
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do',
        )->assertOk();

        $mallaCurso = MallaCurso::query()->where('activo', true)->orderBy('id')->firstOrFail();
        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', '2026')
            ->where('bimestre', '1')
            ->firstOrFail();

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/componentes', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'nombre' => 'Exposición',
            ])
            ->assertCreated();

        $spreadsheet = $this->cargarSpreadsheet($this->descargarExcelAula($this->coordinador()));

        $hojaCurso = null;
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($sheet->getTitle() !== PlantillaExcelAulaLayout::HOJA_ESTUDIANTES) {
                $hojaCurso = $sheet;
                break;
            }
        }

        $this->assertNotNull($hojaCurso);

        $encontrado = false;
        foreach (range(1, 60) as $col) {
            foreach ([8, 9] as $row) {
                $valor = mb_strtoupper((string) $hojaCurso->getCellByColumnAndRow($col, $row)->getValue());
                if (str_contains($valor, 'EXPOSICIÓN')) {
                    $encontrado = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($encontrado, 'La hoja de curso debe incluir la columna del componente personalizado activo.');
    }

    #[Test]
    public function hoja_curso_contiene_formula_hacia_estudiantes(): void
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do',
        )->assertOk();

        $spreadsheet = $this->cargarSpreadsheet($this->descargarExcelAula($this->coordinador()));

        $hojaCurso = null;
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($sheet->getTitle() !== PlantillaExcelAulaLayout::HOJA_ESTUDIANTES) {
                $hojaCurso = $sheet;
                break;
            }
        }

        $this->assertNotNull($hojaCurso);

        $celdaB10 = $hojaCurso->getCell('B10');
        $this->assertTrue($celdaB10->isFormula(), 'B10 debe ser una fórmula, no texto.');
        $this->assertSame(
            '='.PlantillaExcelAulaLayout::HOJA_ESTUDIANTES.'!B10',
            (string) $celdaB10->getValue(),
        );

        $celdaA10 = $hojaCurso->getCell('A10');
        $this->assertTrue($celdaA10->isFormula());
        $this->assertSame(
            '='.PlantillaExcelAulaLayout::HOJA_ESTUDIANTES.'!A10',
            (string) $celdaA10->getValue(),
        );

        $celdaB49 = $hojaCurso->getCell('B49');
        $this->assertTrue($celdaB49->isFormula());
        $this->assertSame(
            '='.PlantillaExcelAulaLayout::HOJA_ESTUDIANTES.'!B49',
            (string) $celdaB49->getValue(),
        );
    }

    private function descargarExcelAula(\App\Models\User $user): string
    {
        $response = $this->actingAs($user)
            ->get('/api/curricular/excel-aula?'.http_build_query($this->queryExcelAula()));

        $response->assertOk();

        return $response->streamedContent();
    }

    private function cargarSpreadsheet(string $binary): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $tmp = tempnam(sys_get_temp_dir(), 'excel_aula_');
        file_put_contents($tmp, $binary);
        $spreadsheet = IOFactory::load($tmp);
        @unlink($tmp);

        return $spreadsheet;
    }
}
