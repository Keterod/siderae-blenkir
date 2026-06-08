<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimNotaScalar;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use PHPUnit\Framework\Attributes\Test;

class ConfiguracionBimestralGradoTest extends CurricularApiTestCase
{
    private const ANIO = '2026';

    /**
     * @return array{componentes: list<array<string, mixed>>, etas: list<array<string, mixed>>}
     */
    private function plantillaPorDefecto(): array
    {
        return [
            'componentes' => [
                ['codigo' => 'promedio_criterios', 'nombre' => 'Promedio de criterios', 'peso' => 25, 'activo' => true, 'orden' => 1],
                ['codigo' => 'oral', 'nombre' => 'Oral', 'peso' => 25, 'activo' => true, 'orden' => 2],
                ['codigo' => 'promedio_eta', 'nombre' => 'Promedio ETA', 'peso' => 25, 'activo' => true, 'orden' => 3],
                ['codigo' => 'examen_bimestral', 'nombre' => 'Examen bimestral', 'peso' => 25, 'activo' => true, 'orden' => 4],
            ],
            'etas' => [
                ['nombre' => 'ETA 1', 'peso_interno' => 33.33, 'activo' => true, 'orden' => 1],
                ['nombre' => 'ETA 2', 'peso_interno' => 33.33, 'activo' => true, 'orden' => 2],
                ['nombre' => 'ETA 3', 'peso_interno' => 33.34, 'activo' => true, 'orden' => 3],
            ],
        ];
    }

    /**
     * @return array{0: PeriodoAcademico, 1: list<MallaCurso>}
     */
    private function prepararGradoPrimaria2do(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
            ])
        )->assertOk();

        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO)
            ->where('bimestre', '1')
            ->firstOrFail();

        $cursos = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', 'primaria')
                ->where('grado', '2do'))
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return [$periodo, $cursos->all()];
    }

    #[Test]
    public function modo_por_curso_sigue_funcionando(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();
        $mallaCurso = $cursos[0];

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/evaluacion-bimestral/config?'.http_build_query([
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
            ]))
            ->assertOk()
            ->assertJsonCount(4, 'componentes')
            ->assertJsonCount(3, 'etas');
    }

    #[Test]
    public function bulk_aplica_configuracion_a_todos_los_cursos_activos_del_grado(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();
        $this->assertGreaterThanOrEqual(2, count($cursos));

        $plantilla = $this->plantillaPorDefecto();
        $plantilla['componentes'][0]['peso'] = 30;
        $plantilla['componentes'][1]['peso'] = 20;
        $plantilla['componentes'][2]['peso'] = 30;
        $plantilla['componentes'][3]['peso'] = 20;

        $response = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $plantilla,
            ])
            ->assertOk()
            ->assertJsonPath('total_afectados', count($cursos));

        $this->assertCount(count($cursos), $response->json('cursos_afectados'));

        foreach ($cursos as $mallaCurso) {
            $oral = EvalBimComponente::query()
                ->where('malla_curso_id', $mallaCurso->id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('codigo', 'oral')
                ->firstOrFail();

            $this->assertEqualsWithDelta(20.0, (float) $oral->peso, 0.02);
        }
    }

    #[Test]
    public function no_aplica_a_cursos_inactivos(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();
        $inactivo = $cursos[0];

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $inactivo->id,
            $periodo->id,
        );

        $inactivo->activo = false;
        $inactivo->save();

        $activos = array_values(array_filter($cursos, fn (MallaCurso $c) => $c->id !== $inactivo->id));

        $plantilla = $this->plantillaPorDefecto();
        $plantilla['componentes'][0]['peso'] = 32;
        $plantilla['componentes'][1]['peso'] = 18;
        $plantilla['componentes'][2]['peso'] = 25;
        $plantilla['componentes'][3]['peso'] = 25;

        $response = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $plantilla,
            ])
            ->assertOk()
            ->assertJsonPath('total_afectados', count($activos));

        $omitidos = collect($response->json('cursos_omitidos'))->pluck('malla_curso_id');
        $this->assertTrue($omitidos->contains($inactivo->id));

        $oralInactivo = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $inactivo->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $this->assertEqualsWithDelta(25.0, $oralInactivo, 0.02);

        $oralActivo = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $activos[0]->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $this->assertEqualsWithDelta(18.0, $oralActivo, 0.02);
    }

    #[Test]
    public function responde_422_si_no_hay_cursos_activos(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();

        foreach ($cursos as $curso) {
            $curso->activo = false;
            $curso->save();
        }

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $this->plantillaPorDefecto(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['grado']);
    }

    #[Test]
    public function rollback_si_la_plantilla_no_suma_cien(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();
        $mallaCurso = $cursos[0];

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );

        $pesoOralAntes = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $plantillaInvalida = $this->plantillaPorDefecto();
        $plantillaInvalida['componentes'][0]['peso'] = 10;
        $plantillaInvalida['componentes'][1]['peso'] = 10;
        $plantillaInvalida['componentes'][2]['peso'] = 10;
        $plantillaInvalida['componentes'][3]['peso'] = 10;

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $plantillaInvalida,
            ])
            ->assertStatus(422);

        $pesoOralDespues = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $this->assertEqualsWithDelta($pesoOralAntes, $pesoOralDespues, 0.01);
    }

    #[Test]
    public function docente_sin_permiso_no_puede_aplicar_por_grado(): void
    {
        [$periodo] = $this->prepararGradoPrimaria2do();

        $this->actingAs($this->docente())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $this->plantillaPorDefecto(),
            ])
            ->assertForbidden();
    }

    #[Test]
    public function no_toca_cursos_de_otro_grado(): void
    {
        [$periodo, $cursos2do] = $this->prepararGradoPrimaria2do();

        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '1ro',
            ])
        )->assertOk();

        $curso1ro = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', 'primaria')
                ->where('grado', '1ro'))
            ->where('activo', true)
            ->firstOrFail();

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $curso1ro->id,
            $periodo->id,
        );

        $oral1roAntes = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $curso1ro->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $plantilla = $this->plantillaPorDefecto();
        $plantilla['componentes'][0]['peso'] = 35;
        $plantilla['componentes'][1]['peso'] = 15;
        $plantilla['componentes'][2]['peso'] = 25;
        $plantilla['componentes'][3]['peso'] = 25;

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $plantilla,
            ])
            ->assertOk()
            ->assertJsonPath('total_afectados', count($cursos2do));

        $oral1roDespues = (float) EvalBimComponente::query()
            ->where('malla_curso_id', $curso1ro->id)
            ->where('codigo', 'oral')
            ->value('peso');

        $this->assertEqualsWithDelta($oral1roAntes, $oral1roDespues, 0.01);
        $this->assertEqualsWithDelta(15.0, (float) EvalBimComponente::query()
            ->where('malla_curso_id', $cursos2do[0]->id)
            ->where('codigo', 'oral')
            ->value('peso'), 0.02);
    }

    #[Test]
    public function no_borra_notas_ni_resultados_existentes(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();
        $mallaCurso = $cursos[0];

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );

        $oral = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('codigo', 'oral')
            ->firstOrFail();

        $estudiante = Estudiante::factory()->create([
            'sede' => 'chilca',
            'nivel' => 'primaria',
            'anio_escolar' => self::ANIO,
        ]);

        EvalBimNotaScalar::query()->create([
            'estudiante_id' => $estudiante->id,
            'eval_bim_componente_id' => $oral->id,
            'nota' => 14.5,
        ]);

        EvalBimResultado::query()->create([
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'sede' => 'chilca',
            'grado' => '2°',
            'seccion' => 'A',
            'estado_calculo' => 'pendiente',
        ]);

        $notasAntes = EvalBimNotaScalar::query()->count();
        $resultadosAntes = EvalBimResultado::query()->count();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $this->plantillaPorDefecto(),
            ])
            ->assertOk();

        $this->assertSame($notasAntes, EvalBimNotaScalar::query()->count());
        $this->assertSame($resultadosAntes, EvalBimResultado::query()->count());
    }

    #[Test]
    public function bulk_aplica_pesos_editados_en_plantilla(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();

        $plantilla = $this->plantillaPorDefecto();
        $plantilla['componentes'][0]['peso'] = 40;
        $plantilla['componentes'][1]['peso'] = 20;
        $plantilla['componentes'][2]['peso'] = 20;
        $plantilla['componentes'][3]['peso'] = 20;

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $plantilla,
            ])
            ->assertOk();

        foreach ($cursos as $mallaCurso) {
            $this->assertEqualsWithDelta(
                40.0,
                (float) EvalBimComponente::query()
                    ->where('malla_curso_id', $mallaCurso->id)
                    ->where('codigo', 'promedio_criterios')
                    ->value('peso'),
                0.02,
            );
            $this->assertEqualsWithDelta(
                20.0,
                (float) EvalBimComponente::query()
                    ->where('malla_curso_id', $mallaCurso->id)
                    ->where('codigo', 'oral')
                    ->value('peso'),
                0.02,
            );
        }
    }

    #[Test]
    public function suma_componentes_activos_queda_en_cien_por_curso(): void
    {
        [$periodo, $cursos] = $this->prepararGradoPrimaria2do();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/evaluacion-bimestral/config/aplicar-grado', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'grado' => '2do',
                'periodo_academico_id' => $periodo->id,
                'plantilla' => $this->plantillaPorDefecto(),
            ])
            ->assertOk();

        foreach ($cursos as $mallaCurso) {
            $suma = (float) EvalBimComponente::query()
                ->where('malla_curso_id', $mallaCurso->id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('activo', true)
                ->sum('peso');

            $this->assertEqualsWithDelta(100.0, $suma, 0.02, "Curso {$mallaCurso->id}");

            $eta = EvalBimComponente::query()
                ->where('malla_curso_id', $mallaCurso->id)
                ->where('codigo', 'promedio_eta')
                ->firstOrFail();

            $sumaEtas = (float) EvalBimEtaItem::query()
                ->where('eval_bim_componente_id', $eta->id)
                ->where('activo', true)
                ->sum('peso_interno');

            $this->assertEqualsWithDelta(100.0, $sumaEtas, 0.02);
        }
    }
}
