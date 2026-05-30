<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\NotaSemanalComponente;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\NotaSemanalCalificacionAdapter;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class NotasSemanalesComponentesDinamicosTest extends CurricularApiTestCase
{
    private const ANIO = '2026';

    private const NIVEL = 'primaria';

    #[Test]
    public function bulk_legacy_sigue_guardando_clt_y_modelo_legacy(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();

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
            ->assertCreated()
            ->assertJsonPath('notas.0.modelo_calificacion', 'legacy')
            ->assertJsonPath('notas.0.ce_calculado', '16.00');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'modelo_calificacion' => 'legacy',
            'ce_calculado' => '16.00',
        ]);

        $notaId = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->value('id');

        $this->assertDatabaseMissing('notas_semanales_componentes', [
            'nota_semanal_id' => $notaId,
        ]);
    }

    #[Test]
    public function bulk_dinamico_calcula_ce_y_guarda_filas_hijas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $componentes = $this->componentesActivos(self::NIVEL);

        $payload = $this->payloadNotasComponentes($componentes, [
            'cuaderno' => 12,
            'libro' => 15,
            'tarea' => 18,
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    array_merge(['tema_semanal_id' => $tema->id], ['notas_componentes' => $payload]),
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.modelo_calificacion', 'dinamico')
            ->assertJsonPath('notas.0.ce_calculado', '15.00')
            ->assertJsonCount(3, 'notas.0.notas_componentes');

        $nota = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $this->assertSame('dinamico', $nota->modelo_calificacion);
        $this->assertNull($nota->nota_cuaderno);
        $this->assertNull($nota->nota_libro);
        $this->assertNull($nota->nota_tarea);
        $this->assertEqualsWithDelta(15.0, (float) $nota->ce_calculado, 0.01);
        $this->assertCount(3, NotaSemanalComponente::query()->where('nota_semanal_id', $nota->id)->get());
    }

    #[Test]
    public function bulk_dinamico_guarda_snapshot_en_pesos_usados_json(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $componentes = $this->componentesActivos(self::NIVEL);
        $payload = $this->payloadNotasComponentes($componentes, ['cuaderno' => 10, 'libro' => 12]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    array_merge(['tema_semanal_id' => $tema->id], ['notas_componentes' => $payload]),
                ],
            ])
            ->assertCreated();

        $nota = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('tema_semanal_id', $tema->id)
            ->firstOrFail();

        $snapshot = $nota->pesos_usados_json;
        $this->assertIsArray($snapshot);
        $this->assertSame(NotaSemanalCalificacionAdapter::SNAPSHOT_MODELO_DINAMICO, $snapshot['modelo']);
        $this->assertCount(3, $snapshot['componentes']);
        $this->assertSame('cuaderno', $snapshot['componentes'][0]['codigo']);
    }

    #[Test]
    public function bulk_dinamico_rechaza_componente_de_otro_nivel(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $componenteSecundaria = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'secundaria')
            ->where('activo', true)
            ->orderBy('orden')
            ->firstOrFail();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'notas_componentes' => [
                            ['componente_id' => $componenteSecundaria->id, 'nota' => 15],
                        ],
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['registros.0.notas_componentes']);
    }

    #[Test]
    public function bulk_dinamico_rechaza_configuracion_activa_con_suma_distinta_de_cien(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $componentes = $this->componentesActivos(self::NIVEL);

        ComponenteCalificacionNivel::query()
            ->where('id', $componentes->firstWhere('codigo', 'cuaderno')->id)
            ->update(['peso' => 50]);

        $payload = $this->payloadNotasComponentes($componentes, ['cuaderno' => 14]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    array_merge(['tema_semanal_id' => $tema->id], ['notas_componentes' => $payload]),
                ],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function bulk_rechaza_mezcla_legacy_y_dinamico_en_mismo_registro(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $componentes = $this->componentesActivos(self::NIVEL);
        $payload = $this->payloadNotasComponentes($componentes, ['cuaderno' => 14]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 14,
                        'notas_componentes' => $payload,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['registros.0']);
    }

    #[Test]
    public function formulario_legacy_no_requiere_filas_dinamicas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 15,
                        'nota_tarea' => 17,
                    ],
                ],
            ])
            ->assertCreated();

        $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/notas-semanales/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'estudiante_id' => $estudiante->id,
            ]))
            ->assertOk()
            ->assertJsonPath("notas_por_criterio.{$tema->id}.modelo_calificacion", 'legacy')
            ->assertJsonPath("notas_por_criterio.{$tema->id}.nota_cuaderno", '15.00')
            ->assertJsonPath("notas_por_criterio.{$tema->id}.notas_componentes", []);
    }

    #[Test]
    public function formulario_dinamico_devuelve_notas_componentes(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $componentes = $this->componentesActivos(self::NIVEL);
        $payload = $this->payloadNotasComponentes($componentes, [
            'cuaderno' => 11,
            'libro' => 13,
            'tarea' => 15,
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    array_merge(['tema_semanal_id' => $tema->id], ['notas_componentes' => $payload]),
                ],
            ])
            ->assertCreated();

        $response = $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/notas-semanales/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'estudiante_id' => $estudiante->id,
            ]))
            ->assertOk()
            ->assertJsonPath('calificacion_dinamica_disponible', true)
            ->assertJsonPath("notas_por_criterio.{$tema->id}.modelo_calificacion", 'dinamico')
            ->assertJsonCount(3, "notas_por_criterio.{$tema->id}.notas_componentes");

        $this->assertGreaterThanOrEqual(3, count($response->json('componentes_calificacion')));
    }

    #[Test]
    public function guardar_nota_dinamica_recalcula_promedio_criterios_eval_bim(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();
        $periodoId = $tema->periodo_academico_id;
        $componentes = $this->componentesActivos(self::NIVEL);
        $payload = $this->payloadNotasComponentes($componentes, [
            'cuaderno' => 14,
            'libro' => 16,
            'tarea' => 18,
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    array_merge(['tema_semanal_id' => $tema->id], ['notas_componentes' => $payload]),
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '16.00');

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

    /**
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    private function componentesActivos(string $nivel): Collection
    {
        return ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', $nivel)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    /**
     * @param  array<string, float>  $notasPorCodigo
     * @return list<array{componente_id: int, nota: float}>
     */
    private function payloadNotasComponentes(Collection $componentes, array $notasPorCodigo): array
    {
        $payload = [];
        foreach ($notasPorCodigo as $codigo => $nota) {
            $config = $componentes->firstWhere('codigo', $codigo);
            $this->assertNotNull($config, "Componente {$codigo} no encontrado en fixtures.");
            $payload[] = [
                'componente_id' => $config->id,
                'nota' => $nota,
            ];
        }

        return $payload;
    }

    /**
     * @return array{0: DocenteCursoAula, 1: TemaSemanal, 2: Estudiante}
     */
    private function prepararFlujoNotasPrimaria(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => self::NIVEL,
                'grado' => '2do',
            ])
        )->assertOk();

        $mallaCurso = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', self::NIVEL)
                ->where('grado', '2do'))
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

        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $coordinador = $this->coordinador();
        $temaId = $this->actingAs($coordinador)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio dinámico',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $docenteUser = $this->docente();
        $asignacionId = $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => self::NIVEL,
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->json('id');

        $estudiante = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => self::NIVEL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);
        $tema = TemaSemanal::query()->findOrFail($temaId);

        return [$asignacion, $tema, $estudiante];
    }
}
