<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use PHPUnit\Framework\Attributes\Test;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotasSemanalesInicialTest extends CurricularApiTestCase
{
    private const ANIO = '2026';

    private const NIVEL = CatalogoNivelGrado::NIVEL_INICIAL;

    private const GRADO = '3 años';

    private const SEDE = 'chilca';

    private const SECCION = 'A';

    #[Test]
    public function puede_provisionar_malla_inicial_tres_anos_2026(): void
    {
        $response = $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => self::NIVEL,
                'grado' => self::GRADO,
            ])
        );

        $response->assertOk()
            ->assertJsonPath('nivel', self::NIVEL)
            ->assertJsonPath('grado', self::GRADO);

        $this->assertTrue(
            MallaCurricular::query()
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', self::NIVEL)
                ->where('grado', self::GRADO)
                ->exists()
        );

        $this->assertGreaterThanOrEqual(
            1,
            MallaCurso::query()
                ->whereHas('mallaCurricular', fn ($q) => $q
                    ->where('anio_escolar', self::ANIO)
                    ->where('nivel', self::NIVEL)
                    ->where('grado', self::GRADO))
                ->where('activo', true)
                ->count()
        );
    }

    #[Test]
    public function puede_asignar_docente_a_curso_inicial(): void
    {
        [$mallaCurso, $asignacion] = $this->prepararAsignacionInicial();

        $this->assertSame(self::NIVEL, $asignacion->nivel);
        $this->assertSame(self::GRADO, $asignacion->grado);
        $this->assertSame(self::SECCION, $asignacion->seccion);
        $this->assertSame(self::SEDE, $asignacion->sede);
        $this->assertSame($mallaCurso->id, $asignacion->malla_curso_id);
        $this->assertTrue($asignacion->activo);
    }

    #[Test]
    public function puede_crear_criterio_para_curso_inicial(): void
    {
        [, , $tema] = $this->prepararFlujoNotasInicial();

        $this->assertInstanceOf(TemaSemanal::class, $tema);
        $this->assertTrue($tema->activo);
        $this->assertSame('Criterio Inicial I', $tema->titulo);
    }

    #[Test]
    public function formulario_notas_inicial_devuelve_200_con_estudiantes_y_criterios(): void
    {
        [$asignacion, , $tema, $estudiante1] = $this->prepararFlujoNotasInicial(2);
        $periodoId = $tema->periodo_academico_id;

        $response = $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/notas-semanales/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk()
            ->assertJsonPath('consulta_global', false)
            ->assertJsonCount(1, 'criterios')
            ->assertJsonPath('criterios.0.id', $tema->id);

        $ids = collect($response->json('estudiantes'))->pluck('id')->all();
        $this->assertContains($estudiante1->id, $ids);
        $this->assertGreaterThanOrEqual(2, count($ids));
    }

    #[Test]
    public function bulk_guarda_clt_y_calcula_ce_para_inicial(): void
    {
        [$asignacion, , $tema, $estudiante] = $this->prepararFlujoNotasInicial();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 15,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '15.00');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'nota_cuaderno' => '14.00',
            'nota_libro' => '15.00',
            'nota_tarea' => '16.00',
            'ce_calculado' => '15.00',
        ]);
    }

    #[Test]
    public function bulk_multiestudiante_funciona_para_inicial(): void
    {
        [$asignacion, , $tema, $estudiante1, $estudiante2] = $this->prepararFlujoNotasInicial(2);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante1->id,
                        'registros' => [
                            [
                                'tema_semanal_id' => $tema->id,
                                'nota_cuaderno' => 12,
                                'nota_tarea' => 14,
                            ],
                        ],
                    ],
                    [
                        'estudiante_id' => $estudiante2->id,
                        'registros' => [
                            [
                                'tema_semanal_id' => $tema->id,
                                'nota_cuaderno' => 16,
                                'nota_libro' => 18,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'notas');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante1->id,
            'tema_semanal_id' => $tema->id,
            'ce_calculado' => '13.00',
        ]);
        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante2->id,
            'tema_semanal_id' => $tema->id,
            'ce_calculado' => '17.00',
        ]);
    }

    #[Test]
    public function guardar_notas_inicial_recalcula_promedio_criterios_eval_bim(): void
    {
        [$asignacion, , $tema, $estudiante] = $this->prepararFlujoNotasInicial();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 16,
                        'nota_tarea' => 18,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('eval_bim_resultados', [
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $asignacion->malla_curso_id,
            'periodo_academico_id' => $periodoId,
            'sede' => self::SEDE,
            'grado' => self::GRADO,
            'seccion' => self::SECCION,
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
    public function plantilla_excel_inicial_descarga_correctamente(): void
    {
        [$asignacion, , $tema, $estudiante] = $this->prepararFlujoNotasInicial();
        $periodoId = $tema->periodo_academico_id;

        $response = $this->actingAs($asignacion->user)
            ->get('/api/curricular/notas-semanales/plantilla-excel?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type'),
        );

        $sheet = $this->leerHojaPlantillaExcel($response);
        $this->assertStringContainsString('REGISTRO AUXILIAR', (string) $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString(
            trim("{$estudiante->apellidos} {$estudiante->nombres}"),
            (string) $sheet->getCell('B10')->getValue(),
        );
        $this->assertSame('Criterio Inicial I', (string) $sheet->getCell('C8')->getValue());
    }

    #[Test]
    public function bulk_asignacion_rechaza_grado_invalido_para_nivel_inicial(): void
    {
        [$mallaCurso] = $this->prepararMallaInicial();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => self::ANIO,
                'nivel' => self::NIVEL,
                'grado' => '2do',
                'seccion' => self::SECCION,
                'sede' => self::SEDE,
                'malla_curso_ids' => [$mallaCurso->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['grado']);
    }

    /**
     * @return array{0: MallaCurso, 1: PeriodoAcademico, 2: SemanaAcademica}
     */
    private function prepararMallaInicial(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => self::NIVEL,
                'grado' => self::GRADO,
            ])
        )->assertOk();

        $mallaCurso = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', self::NIVEL)
                ->where('grado', self::GRADO))
            ->where('activo', true)
            ->orderBy('id')
            ->firstOrFail();

        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO)
            ->where('bimestre', '1')
            ->firstOrFail();

        $semana = SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();

        return [$mallaCurso, $periodo, $semana];
    }

    /**
     * @return array{0: MallaCurso, 1: DocenteCursoAula}
     */
    private function prepararAsignacionInicial(): array
    {
        [$mallaCurso] = $this->prepararMallaInicial();
        $coordinador = $this->coordinador();
        $docenteUser = $this->docente();

        $asignacionId = $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => self::NIVEL,
            'grado' => self::GRADO,
            'seccion' => self::SECCION,
            'sede' => self::SEDE,
        ])->json('id');

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);

        return [$mallaCurso, $asignacion];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: MallaCurso, 2: TemaSemanal, 3: Estudiante, 4?: Estudiante}
     */
    private function prepararFlujoNotasInicial(int $cantidadEstudiantes = 1): array
    {
        [$mallaCurso, $asignacion] = $this->prepararAsignacionInicial();
        [, $periodo, $semana] = $this->prepararMallaInicial();

        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $temaId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio Inicial I',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $tema = TemaSemanal::query()->findOrFail($temaId);

        $estudiantes = [];
        for ($i = 0; $i < $cantidadEstudiantes; $i++) {
            $estudiantes[] = Estudiante::factory()->create([
                'grado' => self::GRADO,
                'seccion' => self::SECCION,
                'nivel' => self::NIVEL,
                'sede' => self::SEDE,
                'anio_escolar' => self::ANIO,
            ]);
        }

        $resultado = [$asignacion, $mallaCurso, $tema, $estudiantes[0]];
        if ($cantidadEstudiantes >= 2) {
            $resultado[] = $estudiantes[1];
        }

        return $resultado;
    }

    private function leerHojaPlantillaExcel(\Illuminate\Testing\TestResponse $response): Worksheet
    {
        $response->assertOk();
        $tmp = tempnam(sys_get_temp_dir(), 'plantilla_inicial_xlsx_');
        file_put_contents($tmp, $response->streamedContent());
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        return $sheet;
    }
}
