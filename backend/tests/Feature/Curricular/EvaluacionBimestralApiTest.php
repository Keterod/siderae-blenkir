<?php

namespace Tests\Feature\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralBulkService;
use PHPUnit\Framework\Attributes\Test;

class EvaluacionBimestralApiTest extends EvaluacionBimestralTestCase
{
    #[Test]
    public function get_config_crea_defaults_y_devuelve_cuatro_componentes_y_tres_etas(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/evaluacion-bimestral/config?'.http_build_query([
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
            ]));

        $response->assertOk()
            ->assertJsonCount(4, 'componentes')
            ->assertJsonCount(3, 'etas')
            ->assertJsonStructure(['escala_logro']);
    }

    #[Test]
    public function coordinador_puede_configurar_componentes(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/componentes', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'nombre' => 'Proyecto API',
            ])
            ->assertCreated();
    }

    #[Test]
    public function docente_no_puede_configurar_componentes(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $this->actingAs($this->docente())
            ->postJson('/api/curricular/evaluacion-bimestral/componentes', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'nombre' => 'Proyecto',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function agregar_componente_personalizado_redistribuye_pesos(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/componentes', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'nombre' => 'Exposición',
            ])
            ->assertCreated();

        $activos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(5, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso'), 0.05);
    }

    #[Test]
    public function desactivar_componente_redistribuye_pesos(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $oral = $this->componente($mallaCurso->id, $periodo->id, 'oral');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/evaluacion-bimestral/componentes/{$oral->id}", [
                'activo' => false,
            ])
            ->assertOk();

        $activos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(3, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso'), 0.05);
    }

    #[Test]
    public function agregar_eta_redistribuye_pesos_internos(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/etas', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'nombre' => 'ETA 4',
            ])
            ->assertCreated();

        $compEta = $this->componente($mallaCurso->id, $periodo->id, 'promedio_eta');
        $activos = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $compEta->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(4, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso_interno'), 0.05);
    }

    #[Test]
    public function desactivar_eta_redistribuye_pesos_internos(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $eta3 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 3');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/evaluacion-bimestral/etas/{$eta3->id}", [
                'activo' => false,
            ])
            ->assertOk();

        $compEta = $this->componente($mallaCurso->id, $periodo->id, 'promedio_eta');
        $activos = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $compEta->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(2, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso_interno'), 0.05);
    }

    #[Test]
    public function get_formulario_docente_readonly_false(): void
    {
        [$asignacion, $periodoId] = $this->prepararAsignacionDocenteEvalBim();

        $response = $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk()
            ->assertJsonPath('readonly', false)
            ->assertJsonPath('contexto.modo', 'docente');
    }

    #[Test]
    public function get_formulario_consulta_admin_readonly_false(): void
    {
        [$asignacion, $periodoId, , $mallaCurso] = $this->prepararAsignacionDocenteEvalBim();

        $response = $this->actingAs($this->administrador())
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'consulta_global' => '1',
                'anio_escolar' => $asignacion->anio_escolar,
                'nivel' => $asignacion->nivel,
                'sede' => $asignacion->sede,
                'grado' => $asignacion->grado,
                'seccion' => $asignacion->seccion,
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk()
            ->assertJsonPath('readonly', false)
            ->assertJsonPath('contexto.modo', 'consulta')
            ->assertJsonPath('contexto.asignacion_docente_id', $asignacion->id);
    }

    #[Test]
    public function docente_no_consulta_formulario_curso_no_asignado(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $docente = $this->docente();

        $this->actingAs($docente)
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'asignacion_docente_id' => 99999,
                'periodo_academico_id' => $periodo->id,
            ]))
            ->assertUnprocessable();
    }

    #[Test]
    public function bulk_guarda_oral_examen_eta_y_conclusion_y_recalcula(): void
    {
        [$asignacion, $periodoId, $estudiante, $mallaCurso] = $this->prepararAsignacionDocenteEvalBim();
        $docente = $asignacion->user;
        $tema = $this->crearTemaActivo(MallaCurso::find($asignacion->malla_curso_id), PeriodoAcademico::find($periodoId));
        $this->crearNotaSemanalConCe($estudiante, $tema, 14.0, $docente);
        $eta1 = $this->etaPorNombre($mallaCurso->id, $periodoId, 'ETA 1');

        $response = $this->actingAs($docente)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'oral' => 16,
                        'examen_bimestral' => 12,
                        'etas' => [['eta_item_id' => $eta1->id, 'nota' => 15]],
                        'conclusion_descriptiva' => 'Buen avance',
                    ],
                ],
            ]);

        $response->assertCreated();
        $fila = collect($response->json('resultados'))->firstWhere('estudiante_id', $estudiante->id);
        $this->assertNotNull($fila);
        $this->assertEquals(16, (int) $fila['oral']);
        $this->assertEquals(12, (int) $fila['examen_bimestral']);
        $this->assertSame('Buen avance', $fila['conclusion_descriptiva']);

        $this->assertDatabaseHas('eval_bim_resultados', [
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $mallaCurso->id,
            'estado_calculo' => EvalBimEstadoCalculo::Completo->value,
        ]);
    }

    #[Test]
    public function eta_cero_explicito_activa_participacion_via_api(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(2);
        [$asignacion, $periodoId] = $this->crearAsignacionParaMalla($mallaCurso, $periodo);
        $eta2 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 2');
        $docente = $asignacion->user;

        foreach ($estudiantes as $est) {
            $tema = $this->crearTemaActivo($mallaCurso, $periodo, 'CE '.$est->id);
            $this->crearNotaSemanalConCe($est, $tema, 14.0, $docente);
        }

        $this->actingAs($docente)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiantes[0]->id,
                        'oral' => 16,
                        'examen_bimestral' => 12,
                        'etas' => [
                            ['eta_item_id' => $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1')->id, 'nota' => 18],
                            ['eta_item_id' => $eta2->id, 'nota' => 0],
                        ],
                    ],
                    [
                        'estudiante_id' => $estudiantes[1]->id,
                        'oral' => 16,
                        'examen_bimestral' => 12,
                        'etas' => [
                            ['eta_item_id' => $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1')->id, 'nota' => 20],
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $resultadoB = EvalBimResultado::query()
            ->where('estudiante_id', $estudiantes[1]->id)
            ->where('malla_curso_id', $mallaCurso->id)
            ->first();

        $this->assertEqualsWithDelta(10.0, (float) $resultadoB->promedio_eta, 0.01);
    }

    #[Test]
    public function promedio_eta_activo_sin_participantes_deja_pendiente_api(): void
    {
        [$asignacion, $periodoId, $estudiante, $mallaCurso] = $this->prepararAsignacionDocenteEvalBim();
        $tema = $this->crearTemaActivo(MallaCurso::find($asignacion->malla_curso_id), PeriodoAcademico::find($periodoId));
        $this->crearNotaSemanalConCe($estudiante, $tema, 14.0, $asignacion->user);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'oral' => 16,
                        'examen_bimestral' => 12,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('eval_bim_resultados', [
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $mallaCurso->id,
            'estado_calculo' => EvalBimEstadoCalculo::Pendiente->value,
        ]);
    }

    #[Test]
    public function resultado_completo_calcula_nivel_numerico_y_literal_api(): void
    {
        [$asignacion, $periodoId, $estudiante, $mallaCurso] = $this->prepararAsignacionDocenteEvalBim();
        $this->llenarComponentesCompletosParaNivel(
            new \App\DTO\Curricular\AulaEvaluacionContext(
                $mallaCurso->id,
                $periodoId,
                'chilca',
                '2do',
                'A',
                [$estudiante->id],
            ),
            $mallaCurso,
            PeriodoAcademico::find($periodoId),
            [$estudiante],
        );

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    ['estudiante_id' => $estudiante->id],
                ],
            ]);

        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/evaluacion-bimestral/resultados?'.http_build_query([
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodoId,
                'sede' => 'chilca',
                'grado' => '2do',
                'seccion' => 'A',
                'recalcular' => '1',
            ]))
            ->assertOk();

        $fila = collect($response->json('resultados'))->firstWhere('estudiante_id', $estudiante->id);
        $this->assertNotNull($fila);
        $this->assertSame('completo', $fila['estado_calculo']);
        $this->assertSame('A', $fila['nivel_logro_literal']);
    }

    #[Test]
    public function usuario_sin_permiso_recibe_403_en_config(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $this->actingAs(User::factory()->create())
            ->getJson('/api/curricular/evaluacion-bimestral/config?'.http_build_query([
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function bulk_null_no_elimina_y_advierte(): void
    {
        [$asignacion, $periodoId, $estudiante] = $this->prepararAsignacionDocenteEvalBim();
        $oral = $this->componente($asignacion->malla_curso_id, $periodoId, 'oral');

        $this->guardarNotaScalar($estudiante, $oral, 15.0, $asignacion->user);

        $response = $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    ['estudiante_id' => $estudiante->id, 'oral' => null],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['advertencias' => [EvaluacionBimestralBulkService::ADVERTENCIA_NO_ELIMINAR_NOTA]]);
    }

    /**
     * @return array{0: DocenteCursoAula, 1: int, 2: Estudiante, 3: MallaCurso}
     */
    private function prepararAsignacionDocenteEvalBim(): array
    {
        [$mallaCurso, $periodo] = $this->mallaYPeriodoEvalBim();
        [$asignacion, $periodoId] = $this->crearAsignacionParaMalla($mallaCurso, $periodo);
        $estudiante = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        return [$asignacion, $periodoId, $estudiante, $mallaCurso];
    }

    /**
     * @return array{0: MallaCurso, 1: PeriodoAcademico}
     */
    private function mallaYPeriodoEvalBim(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $this->asegurarConfigBimestral($mallaCurso, $periodo);

        return [$mallaCurso, $periodo];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: int}
     */
    private function crearAsignacionParaMalla(MallaCurso $mallaCurso, PeriodoAcademico $periodo): array
    {
        $docenteUser = $this->docente();
        $asignacionId = $this->actingAs($this->coordinador())->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->json('id');

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);

        return [$asignacion, $periodo->id];
    }
}
