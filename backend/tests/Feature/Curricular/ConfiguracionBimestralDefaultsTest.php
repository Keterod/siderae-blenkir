<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use PHPUnit\Framework\Attributes\Test;

class ConfiguracionBimestralDefaultsTest extends CurricularApiTestCase
{
    #[Test]
    public function configuracion_por_defecto_crea_cuatro_componentes_bimestrales_y_tres_etas(): void
    {
        [$mallaCurso, $periodo] = $this->prepararMallaPorNivel('primaria', '2do', '2026');

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );

        $activos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $this->assertCount(4, $activos);
        $this->assertSame(
            ['promedio_criterios', 'oral', 'promedio_eta', 'examen_bimestral'],
            $activos->pluck('codigo')->all(),
        );
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso'), 0.02);

        $eta = $activos->firstWhere('codigo', 'promedio_eta');
        $items = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $eta->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(3, $items);
        $this->assertEqualsWithDelta(100.0, (float) $items->sum('peso_interno'), 0.02);
    }

    #[Test]
    public function configuracion_bimestral_no_crea_componentes_cuaderno_libro_tarea(): void
    {
        [$mallaCurso, $periodo] = $this->prepararMallaPorNivel('primaria', '2do', '2026');

        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );

        $codigosClt = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->whereIn('codigo', ['cuaderno', 'libro', 'tarea'])
            ->pluck('codigo')
            ->all();

        $this->assertSame([], $codigosClt);
    }

    #[Test]
    public function api_config_expone_cuatro_componentes_bimestrales(): void
    {
        [$mallaCurso, $periodo] = $this->prepararMallaPorNivel('primaria', '2do', '2026');

        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/evaluacion-bimestral/config?'.http_build_query([
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
            ]));

        $response->assertOk()
            ->assertJsonCount(4, 'componentes')
            ->assertJsonCount(3, 'etas')
            ->assertJsonPath('componentes.0.codigo', 'promedio_criterios');
    }

    #[Test]
    public function pesos_evaluacion_backend_sigue_disponible_aunque_ui_este_oculta(): void
    {
        [$mallaCurso] = $this->prepararMallaPorNivel('primaria', '2do', '2026');

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/pesos/resolver?malla_curso_id='.$mallaCurso->id)
            ->assertOk()
            ->assertJsonPath('pesos.cuaderno', 33.33);
    }

    /**
     * @return array{0: MallaCurso, 1: PeriodoAcademico}
     */
    private function prepararMallaPorNivel(string $nivel, string $grado, string $anioEscolar): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => $anioEscolar,
                'nivel' => $nivel,
                'grado' => $grado,
            ])
        )->assertOk();

        $mallaCurso = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q->where('anio_escolar', $anioEscolar)->where('nivel', $nivel))
            ->firstOrFail();

        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('bimestre', '1')
            ->firstOrFail();

        return [$mallaCurso, $periodo];
    }
}
