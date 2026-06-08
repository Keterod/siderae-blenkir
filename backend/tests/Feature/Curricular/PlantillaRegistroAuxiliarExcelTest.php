<?php

namespace Tests\Feature\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\NotaSemanalComponente;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\ImportPlantillaRegistroAuxiliarService;
use App\Services\Curricular\NotaSemanalCalificacionAdapter;
use App\Services\Curricular\PlantillaRegistroAuxiliarLayout;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Curricular\Concerns\PreparaFlujoNotasSemanalesDinamicas;

class PlantillaRegistroAuxiliarExcelTest extends EvaluacionBimestralTestCase
{
    use PreparaFlujoNotasSemanalesDinamicas;

    #[Test]
    public function descarga_plantilla_legacy_cuando_no_hay_configuracion_dinamica_valida(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', self::NIVEL)
            ->update(['activo' => false]);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $this->assertSame(PlantillaRegistroAuxiliarLayout::MODO_LEGACY, $meta['modo_calificacion']);

        $sheet = $this->leerHojaRegistro($binary);
        $this->assertSame('C', (string) $sheet->getCell('C9')->getValue());
        $this->assertSame('L', (string) $sheet->getCell('D9')->getValue());
        $this->assertSame('T', (string) $sheet->getCell('E9')->getValue());
        $this->assertSame('CE', (string) $sheet->getCell('F9')->getValue());
    }

    #[Test]
    public function descarga_plantilla_dinamica_con_columnas_por_componentes_activos(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $this->assertSame(PlantillaRegistroAuxiliarLayout::MODO_DINAMICO, $meta['modo_calificacion']);

        $componentes = json_decode($meta['componentes_json'], true);
        $this->assertCount(3, $componentes);
        $this->assertSame('cuaderno', $componentes[0]['codigo']);

        $sheet = $this->leerHojaRegistro($binary);
        $this->assertStringContainsString('Cuaderno', (string) $sheet->getCell('C9')->getValue());
        $this->assertStringContainsString('Libro', (string) $sheet->getCell('D9')->getValue());
        $this->assertStringContainsString('Tarea', (string) $sheet->getCell('E9')->getValue());
        $this->assertSame('CE', (string) $sheet->getCell('F9')->getValue());
    }

    #[Test]
    public function plantilla_incluye_periodo_academico_id_y_columna_oculta_estudiante_id_en_meta(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $this->assertSame((string) $periodoId, $meta['periodo_academico_id'] ?? '');
        $this->assertNotSame('', $meta['col_estudiante_id'] ?? '');
        $this->assertSame('2do', $meta['grado'] ?? '');
        $this->assertSame('A', $meta['seccion'] ?? '');
        $this->assertSame('chilca', $meta['sede'] ?? '');
        $this->assertSame((string) $asignacion->id, $meta['asignacion_docente_id'] ?? '');
    }

    #[Test]
    public function rechaza_importacion_cuando_periodo_no_coincide_con_plantilla(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoBimestre1 = $tema->periodo_academico_id;

        $periodoBimestre2 = PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO)
            ->where('bimestre', '2')
            ->firstOrFail();

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoBimestre1);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
            ['col' => 4, 'fila' => 10, 'valor' => 14],
            ['col' => 5, 'fila' => 10, 'valor' => 16],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoBimestre2->id, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $mensaje = (string) ($response->json('errors.archivo.0') ?? '');
        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_PERIODO_DISTINTO,
            $mensaje,
        );

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
        ]);
    }

    #[Test]
    public function importa_correctamente_estudiantes_con_mismo_nombre_usando_estudiante_id(): void
    {
        [$asignacion, $tema, $estudiante1] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $estudiante2 = Estudiante::factory()->create([
            'apellidos' => $estudiante1->apellidos,
            'nombres' => $estudiante1->nombres,
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => self::NIVEL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
            ['col' => 4, 'fila' => 10, 'valor' => 14],
            ['col' => 5, 'fila' => 10, 'valor' => 16],
            ['col' => 3, 'fila' => 11, 'valor' => 18],
            ['col' => 4, 'fila' => 11, 'valor' => 19],
            ['col' => 5, 'fila' => 11, 'valor' => 20],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $componenteCuaderno = $this->componentesActivos(self::NIVEL)->firstWhere('codigo', 'cuaderno');
        $this->assertNotNull($componenteCuaderno);

        $notaEst1 = NotaSemanal::query()
            ->where('estudiante_id', $estudiante1->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();
        $notaEst2 = NotaSemanal::query()
            ->where('estudiante_id', $estudiante2->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $this->assertSame(
            12.0,
            (float) NotaSemanalComponente::query()
                ->where('nota_semanal_id', $notaEst1->id)
                ->where('componente_calificacion_nivel_id', $componenteCuaderno->id)
                ->value('nota'),
        );
        $this->assertSame(
            18.0,
            (float) NotaSemanalComponente::query()
                ->where('nota_semanal_id', $notaEst2->id)
                ->where('componente_calificacion_nivel_id', $componenteCuaderno->id)
                ->value('nota'),
        );
    }

    #[Test]
    public function rechaza_importacion_sin_estudiante_id_cuando_hay_nombres_duplicados(): void
    {
        [$asignacion, $tema, $estudiante1] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        Estudiante::factory()->create([
            'apellidos' => $estudiante1->apellidos,
            'nombres' => $estudiante1->nombres,
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => self::NIVEL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->quitarEstudianteIdDePlantilla($binary);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $mensaje = (string) ($response->json('errors.archivo.0') ?? '');
        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_NOMBRES_DUPLICADOS,
            $mensaje,
        );
    }

    #[Test]
    public function importar_excel_dinamico_guarda_modelo_dinamico_y_filas_hijas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
            ['col' => 4, 'fila' => 10, 'valor' => 14],
            ['col' => 5, 'fila' => 10, 'valor' => 16],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertCreated()
            ->assertJsonPath('notas.0.modelo_calificacion', 'dinamico')
            ->assertJsonPath('omitidos', 0);

        $nota = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $this->assertSame('dinamico', $nota->modelo_calificacion);
        $this->assertCount(3, NotaSemanalComponente::query()->where('nota_semanal_id', $nota->id)->get());
    }

    #[Test]
    public function importar_excel_dinamico_calcula_ce_calculado(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '16.00');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'ce_calculado' => '16.00',
        ]);
    }

    #[Test]
    public function importar_excel_dinamico_guarda_snapshot_en_pesos_usados_json(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 10],
            ['col' => 4, 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $nota = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $snapshot = $nota->pesos_usados_json;
        $this->assertIsArray($snapshot);
        $this->assertSame(NotaSemanalCalificacionAdapter::SNAPSHOT_MODELO_DINAMICO, $snapshot['modelo']);
        $this->assertGreaterThanOrEqual(2, count($snapshot['componentes']));
    }

    #[Test]
    public function rechaza_plantilla_legacy_clt_cuando_componentes_actuales_no_son_clt(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasInicial();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaLegacyForzada($asignacion, $periodoId, 'inicial');
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 15],
            ['col' => 5, 'fila' => 10, 'valor' => 16],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $mensaje = (string) ($response->json('errors.archivo.0') ?? '');
        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_PLANTILLA_NO_COINCIDE,
            $mensaje,
        );
    }

    #[Test]
    public function acepta_plantilla_legacy_clt_si_codigos_actuales_son_cuaderno_libro_tarea(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaLegacyForzada($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertCreated()
            ->assertJsonPath('notas.0.modelo_calificacion', 'dinamico')
            ->assertJsonPath('notas.0.ce_calculado', '16.00');

        $nota = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $this->assertSame('dinamico', $nota->modelo_calificacion);
        $this->assertCount(3, NotaSemanalComponente::query()->where('nota_semanal_id', $nota->id)->get());
    }

    #[Test]
    public function importar_excel_dinamico_recalcula_promedio_criterios_eval_bim(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $this->assertDatabaseHas('eval_bim_resultados', [
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $asignacion->malla_curso_id,
            'periodo_academico_id' => $periodoId,
            'promedio_criterios' => 16.0,
        ]);

        $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]))
            ->assertOk()
            ->assertJsonPath("resultados_por_estudiante.{$estudiante->id}.promedio_criterios", 16);
    }

    #[Test]
    public function rechaza_plantilla_dinamica_con_componentes_distintos_a_configuracion_actual(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);

        ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', self::NIVEL)
            ->where('codigo', 'tarea')
            ->update(['activo' => false]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);
    }

    #[Test]
    public function rechaza_importacion_sin_hoja_meta(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->quitarHojaMetaDePlantilla($binary);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);
        $mensaje = (string) ($response->json('errors.archivo.0') ?? '');
        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_SIN_META,
            $mensaje,
        );
    }

    #[Test]
    public function rechaza_nota_fuera_de_rango_con_422_json(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 25],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);
        $this->assertStringContainsString(
            'Nota fuera de rango',
            (string) ($response->json('errors.archivo.0') ?? ''),
        );

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
        ]);
    }

    #[Test]
    public function rechaza_estudiante_id_fuera_del_aula(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);
        $colEstudianteId = (int) ($meta['col_estudiante_id'] ?? 0);
        $this->assertGreaterThan(0, $colEstudianteId);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => $colEstudianteId, 'fila' => 10, 'valor' => 999999],
            ['col' => 3, 'fila' => 10, 'valor' => 14],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);
        $this->assertStringContainsString(
            'no pertenece al aula seleccionada',
            (string) ($response->json('errors.archivo.0') ?? ''),
        );

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
        ]);
    }

    #[Test]
    public function rechaza_docente_sin_asignacion_con_403(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
        ]);

        $otroDocente = $this->docente();

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary, $otroDocente)
            ->assertForbidden()
            ->assertJsonPath('message', 'Solo puede importar notas en sus asignaciones activas.');
    }

    #[Test]
    public function descarga_plantilla_v3_con_bimestral_cols_en_metadata(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $this->assertSame('3', $meta['plantilla_version'] ?? '');
        $this->assertNotSame('', $meta['columnas_bimestral_json'] ?? '');

        $mapeo = json_decode($meta['mapeo_importacion_json'] ?? '[]', true);
        $this->assertIsArray($mapeo['bimestral_cols'] ?? null);
        $this->assertNotEmpty($mapeo['bimestral_cols']);

        $oral = collect($mapeo['bimestral_cols'])->firstWhere('tipo', 'oral');
        $this->assertNotNull($oral);
        $this->assertTrue($oral['importable'] ?? false);

        $promEta = collect($mapeo['bimestral_cols'])->firstWhere('tipo', 'promedio_eta');
        $this->assertNotNull($promEta);
        $this->assertFalse($promEta['importable'] ?? true);
    }

    #[Test]
    public function excel_solitario_incluye_columna_componente_personalizado_activo(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);
        $this->crearComponentePersonalizadoActivo($asignacion, $periodoId, 'Exposición');

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);
        $mapeo = json_decode($meta['mapeo_importacion_json'] ?? '[]', true);

        $personalizado = collect($mapeo['bimestral_cols'] ?? [])->firstWhere('tipo', 'personalizado');
        $this->assertNotNull($personalizado);
        $this->assertSame('EXPOSICIÓN', mb_strtoupper((string) ($personalizado['etiqueta'] ?? '')));
        $this->assertFalse($personalizado['importable'] ?? true);

        $oral = collect($mapeo['bimestral_cols'])->firstWhere('tipo', 'oral');
        $examen = collect($mapeo['bimestral_cols'])->firstWhere('tipo', 'examen_bimestral');
        $this->assertNotNull($oral);
        $this->assertNotNull($examen);

        $sheet = $this->leerHojaRegistro($binary);
        $this->assertStringContainsString(
            'EXPOSICIÓN',
            mb_strtoupper((string) $sheet->getCellByColumnAndRow((int) $personalizado['col'], 9)->getValue()),
        );
    }

    #[Test]
    public function excel_solitario_no_incluye_componente_personalizado_inactivo(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $componente = $this->crearComponentePersonalizadoActivo($asignacion, $periodoId, 'Proyecto');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/evaluacion-bimestral/componentes/{$componente->id}", [
                'activo' => false,
            ])
            ->assertOk();

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);
        $mapeo = json_decode($meta['mapeo_importacion_json'] ?? '[]', true);

        $this->assertNull(collect($mapeo['bimestral_cols'] ?? [])->firstWhere('tipo', 'personalizado'));
    }

    #[Test]
    public function formula_nivel_numerico_incluye_columna_y_peso_del_personalizado(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);
        $personalizado = $this->crearComponentePersonalizadoActivo($asignacion, $periodoId, 'Exposición');

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $colPersonalizado = $this->colBimestralDesdeMeta($meta, 'personalizado', 'EXPOSICIÓN');
        $colNivel = $this->colBimestralDesdeMeta($meta, 'nivel_numerico');

        $sheet = $this->leerHojaRegistro($binary);
        $formula = (string) $sheet->getCellByColumnAndRow($colNivel, 10)->getValue();
        $refPersonalizado = Coordinate::stringFromColumnIndex($colPersonalizado).'10';

        $this->assertStringContainsString($refPersonalizado, $formula);

        $pesoEsperado = round((float) $personalizado->fresh()->peso / 100, 4);
        $this->assertStringContainsString('*'.$pesoEsperado, $formula);
    }

    #[Test]
    public function excel_solitario_con_notas_muestra_valor_del_personalizado(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);
        $personalizado = $this->crearComponentePersonalizadoActivo($asignacion, $periodoId, 'Exposición');

        $this->guardarNotaScalar($estudiante, $personalizado, 17.0);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId, true);
        $meta = $this->leerMetaPlantilla($binary);
        $colPersonalizado = $this->colBimestralDesdeMeta($meta, 'personalizado', 'EXPOSICIÓN');

        $sheet = $this->leerHojaRegistro($binary);
        $this->assertEquals(17, $sheet->getCellByColumnAndRow($colPersonalizado, 10)->getValue());
    }

    #[Test]
    public function importa_oral_etas_y_examen_desde_excel(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $malla = MallaCurso::query()->findOrFail($asignacion->malla_curso_id);
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 1'), 'fila' => 10, 'valor' => 15],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 2'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 3'), 'fila' => 10, 'valor' => 17],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertCreated()
            ->assertJsonPath('importados_criterios', 1)
            ->assertJsonPath('importados_bimestral', 1);

        $oral = $this->componente($malla->id, $periodoId, 'oral');
        $examen = $this->componente($malla->id, $periodoId, 'examen_bimestral');

        $this->assertDatabaseHas('eval_bim_notas_scalar', [
            'estudiante_id' => $estudiante->id,
            'eval_bim_componente_id' => $oral->id,
            'nota' => '16.00',
        ]);
        $this->assertDatabaseHas('eval_bim_notas_scalar', [
            'estudiante_id' => $estudiante->id,
            'eval_bim_componente_id' => $examen->id,
            'nota' => '12.00',
        ]);
        $this->assertDatabaseHas('eval_bim_notas_eta', [
            'estudiante_id' => $estudiante->id,
            'eval_bim_eta_item_id' => $this->etaPorNombre($malla->id, $periodoId, 'ETA 1')->id,
            'nota' => '15.00',
        ]);
    }

    #[Test]
    public function importacion_bimestral_recalcula_promedio_eta_desde_etas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 1'), 'fila' => 10, 'valor' => 15],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 2'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 3'), 'fila' => 10, 'valor' => 17],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->firstOrFail();

        $this->assertEqualsWithDelta(16.0, (float) $resultado->promedio_eta, 0.01);
    }

    #[Test]
    public function importacion_bimestral_recalcula_nivel_logro_y_estado_desde_servidor(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 1'), 'fila' => 10, 'valor' => 15],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 2'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 3'), 'fila' => 10, 'valor' => 17],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->firstOrFail();

        $this->assertSame(EvalBimEstadoCalculo::Completo, $resultado->estado_calculo);
        $this->assertNotNull($resultado->nivel_logro_numerico);
        $this->assertNotNull($resultado->nivel_logro_literal);
    }

    #[Test]
    public function no_importa_promedio_eta_aunque_excel_tenga_valor_manual(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'promedio_eta'), 'fila' => 10, 'valor' => 99],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 1'), 'fila' => 10, 'valor' => 10],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 2'), 'fila' => 10, 'valor' => 10],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 3'), 'fila' => 10, 'valor' => 10],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->firstOrFail();

        $this->assertEqualsWithDelta(10.0, (float) $resultado->promedio_eta, 0.01);
        $this->assertNotEqualsWithDelta(99.0, (float) $resultado->promedio_eta, 0.01);
    }

    #[Test]
    public function no_importa_nivel_logro_ni_estado_aunque_excel_tenga_valores_manuales(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'nivel_numerico'), 'fila' => 10, 'valor' => 1],
            ['col' => $this->colBimestralDesdeMeta($meta, 'nivel_literal'), 'fila' => 10, 'valor' => 'Z'],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 1'), 'fila' => 10, 'valor' => 15],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 2'), 'fila' => 10, 'valor' => 16],
            ['col' => $this->colBimestralDesdeMeta($meta, 'eta', 'ETA 3'), 'fila' => 10, 'valor' => 17],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 12],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->firstOrFail();

        $this->assertSame(EvalBimEstadoCalculo::Completo, $resultado->estado_calculo);
        $this->assertNotSame('1.00', (string) $resultado->nivel_logro_numerico);
        $this->assertNotSame('Z', (string) $resultado->nivel_logro_literal);
    }

    #[Test]
    public function rechaza_nota_bimestral_fuera_de_rango_con_422_json(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 25],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);
        $this->assertStringContainsString(
            'Nota fuera de rango',
            (string) ($response->json('errors.archivo.0') ?? ''),
        );

        $this->assertDatabaseMissing('eval_bim_notas_scalar', [
            'estudiante_id' => $estudiante->id,
        ]);
    }

    #[Test]
    public function celdas_bimestrales_vacias_no_borran_notas_bimestrales_existentes(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $malla = MallaCurso::query()->findOrFail($asignacion->malla_curso_id);
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);
        $oral = $this->componente($malla->id, $periodoId, 'oral');

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'oral' => 15,
                    ],
                ],
            ])
            ->assertCreated();

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)->assertCreated();

        $this->assertDatabaseHas('eval_bim_notas_scalar', [
            'estudiante_id' => $estudiante->id,
            'eval_bim_componente_id' => $oral->id,
            'nota' => '15.00',
        ]);
    }

    #[Test]
    public function rollback_completo_si_criterios_validos_pero_nota_bimestral_invalida(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $this->colBimestralDesdeMeta($meta, 'examen_bimestral'), 'fila' => 10, 'valor' => 25],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
        ]);
        $this->assertDatabaseMissing('eval_bim_notas_scalar', [
            'estudiante_id' => $estudiante->id,
        ]);
    }

    #[Test]
    public function rechaza_plantilla_si_configuracion_bimestral_cambio_despues_de_descargarla(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $malla = MallaCurso::query()->findOrFail($asignacion->malla_curso_id);
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);

        EvalBimEtaItem::query()
            ->where('id', $this->etaPorNombre($malla->id, $periodoId, 'ETA 3')->id)
            ->update(['activo' => false]);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => $this->colBimestralDesdeMeta($meta, 'oral'), 'fila' => 10, 'valor' => 16],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_BIMESTRAL_NO_COINCIDE,
            (string) ($response->json('errors.archivo.0') ?? ''),
        );
    }

    #[Test]
    public function mantiene_importacion_criterios_semanales_fase_4b(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $this->asegurarEvalBimParaAsignacion($asignacion, $periodoId);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
            ['col' => 4, 'fila' => 10, 'valor' => 14],
            ['col' => 5, 'fila' => 10, 'valor' => 16],
        ]);

        $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertCreated()
            ->assertJsonPath('importados_criterios', 1)
            ->assertJsonPath('importados_bimestral', 0)
            ->assertJsonPath('omitidos', 0);

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
        ]);
    }

    #[Test]
    public function rollback_si_una_fila_valida_y_otra_invalida_no_guarda_notas(): void
    {
        [$asignacion, $tema, $estudiante1] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        Estudiante::factory()->create([
            'apellidos' => 'Apellido Rollback',
            'nombres' => 'Segundo',
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => self::NIVEL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $meta = $this->leerMetaPlantilla($binary);
        $colEstudianteId = (int) ($meta['col_estudiante_id'] ?? 0);

        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
            ['col' => 4, 'fila' => 10, 'valor' => 16],
            ['col' => 5, 'fila' => 10, 'valor' => 18],
            ['col' => $colEstudianteId, 'fila' => 11, 'valor' => 999999],
            ['col' => 3, 'fila' => 11, 'valor' => 12],
            ['col' => 4, 'fila' => 11, 'valor' => 14],
            ['col' => 5, 'fila' => 11, 'valor' => 16],
        ]);

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante1->id,
            'tema_semanal_id' => $tema->id,
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);

        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante1->id,
            'tema_semanal_id' => $tema->id,
        ]);
    }

    #[Test]
    public function rechaza_plantilla_con_asignacion_o_aula_distinta(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->cambiarValorMetaPlantilla($binary, 'sede', 'auquimarca');
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 14],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoId, $binary)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertJson422NoRedirect($response);
        $mensaje = (string) ($response->json('errors.archivo.0') ?? '');
        $this->assertStringContainsString(
            ImportPlantillaRegistroAuxiliarService::MENSAJE_CONTEXTO_DISTINTO,
            $mensaje,
        );
    }

    #[Test]
    public function errores_importacion_devuelven_422_json_no_302(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $periodoBimestre2 = PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO)
            ->where('bimestre', '2')
            ->firstOrFail();

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);
        $binary = $this->rellenarNotasEnPlantilla($binary, [
            ['col' => 3, 'fila' => 10, 'valor' => 12],
        ]);

        $response = $this->importarPlantillaArchivo($asignacion, $periodoBimestre2->id, $binary);

        $response->assertStatus(422);
        $this->assertFalse($response->isRedirect(), 'La importación rechazada no debe redirigir con 302.');
        $this->assertJson422NoRedirect($response);
    }

    private function asegurarEvalBimParaAsignacion(DocenteCursoAula $asignacion, int $periodoId): void
    {
        $periodo = PeriodoAcademico::query()->findOrFail($periodoId);
        $malla = MallaCurso::query()->findOrFail($asignacion->malla_curso_id);
        $this->asegurarConfigBimestral($malla, $periodo);
    }

    private function crearComponentePersonalizadoActivo(
        DocenteCursoAula $asignacion,
        int $periodoId,
        string $nombre,
    ): EvalBimComponente {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/componentes', [
                'malla_curso_id' => $asignacion->malla_curso_id,
                'periodo_academico_id' => $periodoId,
                'nombre' => $nombre,
            ])
            ->assertCreated();

        return EvalBimComponente::query()
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->where('nombre', $nombre)
            ->firstOrFail();
    }

    /**
     * @param  array<string, string>  $meta
     */
    private function colBimestralDesdeMeta(array $meta, string $tipo, ?string $etiqueta = null): int
    {
        $mapeo = json_decode($meta['mapeo_importacion_json'] ?? '[]', true);
        $this->assertIsArray($mapeo);

        foreach ($mapeo['bimestral_cols'] ?? [] as $col) {
            if (($col['tipo'] ?? '') !== $tipo) {
                continue;
            }
            if ($etiqueta !== null && mb_strtoupper((string) ($col['etiqueta'] ?? '')) !== mb_strtoupper($etiqueta)) {
                continue;
            }

            return (int) $col['col'];
        }

        $this->fail("Columna bimestral {$tipo} no encontrada en el mapeo de la plantilla.");
    }

    private function descargarPlantillaBinaria(DocenteCursoAula $asignacion, int $periodoId, bool $incluirNotas = false): string
    {
        $query = [
            'asignacion_docente_id' => $asignacion->id,
            'periodo_academico_id' => $periodoId,
            'incluir_notas' => $incluirNotas ? '1' : '0',
        ];

        $response = $this->actingAs($asignacion->user)
            ->get('/api/curricular/notas-semanales/plantilla-excel?'.http_build_query($query));

        $response->assertOk();

        return $response->streamedContent();
    }

    private function descargarPlantillaLegacyForzada(DocenteCursoAula $asignacion, int $periodoId, ?string $nivel = null): string
    {
        $nivel = $nivel ?? self::NIVEL;

        ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', $nivel)
            ->update(['activo' => false]);

        $binary = $this->descargarPlantillaBinaria($asignacion, $periodoId);

        ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', $nivel)
            ->update(['activo' => true]);

        return $binary;
    }

    private function quitarEstudianteIdDePlantilla(string $binary): string
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);
        $metaSheet = $spreadsheet->getSheetByName(PlantillaRegistroAuxiliarLayout::META_SHEET);
        $this->assertNotNull($metaSheet);

        $colEstudianteId = 0;
        $row = 1;
        while (true) {
            $clave = trim((string) $metaSheet->getCell("A{$row}")->getValue());
            if ($clave === '') {
                break;
            }
            if ($clave === 'col_estudiante_id') {
                $colEstudianteId = (int) $metaSheet->getCell("B{$row}")->getValue();
                $metaSheet->setCellValue("B{$row}", '');
                break;
            }
            $row++;
        }

        if ($colEstudianteId > 0) {
            $sheet = $spreadsheet->getSheetByName('Registro auxiliar') ?? $spreadsheet->getActiveSheet();
            for ($fila = PlantillaRegistroAuxiliarLayout::FILA_INICIO_DATOS; $fila <= $sheet->getHighestRow(); $fila++) {
                $nombre = trim((string) $sheet->getCell("B{$fila}")->getValue());
                if ($nombre === '') {
                    break;
                }
                $sheet->setCellValueByColumnAndRow($colEstudianteId, $fila, null);
            }
        }

        return $this->serializarSpreadsheet($spreadsheet);
    }

    /**
     * @param  list<array{col: int, fila: int, valor: float|int}>  $celdas
     */
    private function rellenarNotasEnPlantilla(string $binary, array $celdas): string
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);
        $sheet = $spreadsheet->getSheetByName('Registro auxiliar') ?? $spreadsheet->getActiveSheet();

        foreach ($celdas as $celda) {
            $sheet->setCellValueByColumnAndRow($celda['col'], $celda['fila'], $celda['valor']);
        }

        return $this->serializarSpreadsheet($spreadsheet);
    }

    private function importarPlantillaArchivo(
        DocenteCursoAula $asignacion,
        int $periodoId,
        string $binary,
        ?User $user = null,
    ): \Illuminate\Testing\TestResponse {
        $tmp = tempnam(sys_get_temp_dir(), 'import_plantilla_xlsx_');
        file_put_contents($tmp, $binary);

        return $this->actingAs($user ?? $asignacion->user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/curricular/notas-semanales/importar-excel', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'archivo' => new UploadedFile(
                    $tmp,
                    'plantilla_notas.xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    null,
                    true,
                ),
            ]);
    }

    private function assertJson422NoRedirect(\Illuminate\Testing\TestResponse $response): void
    {
        $this->assertFalse($response->isRedirect());
        $response->assertJsonStructure(['message', 'errors']);
    }

    private function quitarHojaMetaDePlantilla(string $binary): string
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);
        $metaSheet = $spreadsheet->getSheetByName(PlantillaRegistroAuxiliarLayout::META_SHEET);
        $this->assertNotNull($metaSheet);
        $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($metaSheet));

        return $this->serializarSpreadsheet($spreadsheet);
    }

    private function cambiarValorMetaPlantilla(string $binary, string $clave, string $valor): string
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);
        $metaSheet = $spreadsheet->getSheetByName(PlantillaRegistroAuxiliarLayout::META_SHEET);
        $this->assertNotNull($metaSheet);

        $row = 1;
        while (true) {
            $claveActual = trim((string) $metaSheet->getCell("A{$row}")->getValue());
            if ($claveActual === '') {
                break;
            }
            if ($claveActual === $clave) {
                $metaSheet->setCellValue("B{$row}", $valor);
                break;
            }
            $row++;
        }

        return $this->serializarSpreadsheet($spreadsheet);
    }

    /**
     * @return array<string, string>
     */
    private function leerMetaPlantilla(string $binary): array
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);
        $metaSheet = $spreadsheet->getSheetByName(PlantillaRegistroAuxiliarLayout::META_SHEET);
        $this->assertNotNull($metaSheet);

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

        return $meta;
    }

    private function leerHojaRegistro(string $binary): Worksheet
    {
        $spreadsheet = $this->cargarSpreadsheetDesdeBinario($binary);

        return $spreadsheet->getSheetByName('Registro auxiliar') ?? $spreadsheet->getActiveSheet();
    }

    private function cargarSpreadsheetDesdeBinario(string $binary): Spreadsheet
    {
        $tmp = tempnam(sys_get_temp_dir(), 'plantilla_xlsx_');
        file_put_contents($tmp, $binary);
        $spreadsheet = IOFactory::load($tmp);
        @unlink($tmp);

        return $spreadsheet;
    }

    private function serializarSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'plantilla_xlsx_out_');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmp);
        $binary = (string) file_get_contents($tmp);
        @unlink($tmp);
        $spreadsheet->disconnectWorksheets();

        return $binary;
    }
}
