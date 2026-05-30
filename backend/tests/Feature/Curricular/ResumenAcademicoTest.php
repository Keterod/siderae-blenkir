<?php

namespace Tests\Feature\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use PHPUnit\Framework\Attributes\Test;

class ResumenAcademicoTest extends EvaluacionBimestralTestCase
{
    private const ANIO = '2026';

    #[Test]
    public function resumen_inicial_sin_datos_devuelve_200_y_estado_vacio(): void
    {
        $estudiante = Estudiante::factory()->create([
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'grado' => '3 años',
            'seccion' => 'A',
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/estudiantes/{$estudiante->id}/resumen-academico")
            ->assertOk()
            ->assertJsonPath('estudiante_id', $estudiante->id)
            ->assertJsonPath('nivel', 'inicial')
            ->assertJsonPath('tiene_datos', false)
            ->assertJsonPath('ce_por_tema', [])
            ->assertJsonPath('evaluaciones_bimestrales', []);
    }

    #[Test]
    public function resumen_inicial_con_notas_y_eval_bim_incluye_campos_completos(): void
    {
        [$asignacion, $mallaCurso, $tema, $estudiante] = $this->prepararFlujoInicialConNotas();
        $periodoId = $tema->periodo_academico_id;
        $docente = $asignacion->user;

        $eta1 = $this->etaPorNombre($mallaCurso->id, $periodoId, 'ETA 1');

        $this->actingAs($docente)
            ->postJson('/api/curricular/evaluacion-bimestral/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'oral' => 16,
                        'examen_bimestral' => 12,
                        'etas' => [['eta_item_id' => $eta1->id, 'nota' => 15]],
                        'conclusion_descriptiva' => 'Avance satisfactorio en Inicial',
                    ],
                ],
            ])
            ->assertCreated();

        $json = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/estudiantes/{$estudiante->id}/resumen-academico")
            ->assertOk()
            ->assertJsonPath('tiene_datos', true)
            ->assertJsonStructure([
                'estudiante_id',
                'anio_escolar',
                'nivel',
                'grado',
                'tiene_datos',
                'ce_por_tema',
                'promedios_por_curso',
                'promedios_por_area',
                'promedios_bimestrales',
                'evaluaciones_bimestrales' => [
                    [
                        'malla_curso_id',
                        'curso',
                        'area',
                        'bimestre',
                        'promedio_criterios',
                        'oral',
                        'promedio_eta',
                        'examen_bimestral',
                        'nivel_logro_numerico',
                        'nivel_logro_literal',
                        'estado_calculo',
                        'conclusion_descriptiva',
                        'etas',
                    ],
                ],
            ])
            ->json();

        $this->assertCount(1, $json['ce_por_tema']);
        $this->assertCount(1, $json['evaluaciones_bimestrales']);
        $eval = $json['evaluaciones_bimestrales'][0];
        $this->assertSame('Avance satisfactorio en Inicial', $eval['conclusion_descriptiva']);
        $this->assertSame(16.0, (float) $eval['promedio_criterios']);
        $this->assertSame(16.0, (float) $eval['oral']);
        $this->assertNotEmpty($eval['curso']);
        $this->assertNotEmpty($eval['area']);
        $this->assertContains($eval['estado_calculo'], [
            EvalBimEstadoCalculo::Completo->value,
            EvalBimEstadoCalculo::Pendiente->value,
        ]);
        $this->assertArrayNotHasKey('riesgo', $json);
        $this->assertArrayNotHasKey('ultimo_indice_riesgo', $json);
    }

    #[Test]
    public function resumen_primaria_con_notas_sigue_devolviendo_promedios(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotasPrimaria();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'nota_cuaderno' => 14,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated();

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/estudiantes/{$estudiante->id}/resumen-academico")
            ->assertOk()
            ->assertJsonPath('nivel', 'primaria')
            ->assertJsonPath('tiene_datos', true)
            ->assertJsonCount(1, 'ce_por_tema');
    }

    /**
     * @return array{0: DocenteCursoAula, 1: MallaCurso, 2: TemaSemanal, 3: Estudiante}
     */
    private function prepararFlujoInicialConNotas(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
                'grado' => '3 años',
            ])
        )->assertOk();

        $mallaCurso = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('grado', '3 años'))
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

        $temaId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio resumen inicial',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $docenteUser = $this->docente();
        $asignacionId = $this->actingAs($this->coordinador())->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'grado' => '3 años',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->json('id');

        $estudiante = Estudiante::factory()->create([
            'grado' => '3 años',
            'seccion' => 'A',
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);
        $tema = TemaSemanal::query()->findOrFail($temaId);

        $this->actingAs($docenteUser)
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

        return [$asignacion, $mallaCurso, $tema, $estudiante];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: TemaSemanal, 2: Estudiante}
     */
    private function prepararFlujoNotasPrimaria(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $semana = SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->where('numero_semana', 1)->firstOrFail();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $temaId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema resumen primaria',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

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

        $estudiante = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);

        return [$asignacion, TemaSemanal::query()->findOrFail($temaId), $estudiante];
    }
}
