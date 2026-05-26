<?php

namespace Tests\Feature\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimResultado;
use App\Services\Curricular\EvaluacionBimestral\EvalBimResultadoPersistService;
use App\Services\Curricular\EvaluacionBimestral\EscalaLogroService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use App\Services\Curricular\EvaluacionBimestral\EtaParticipacionPorAulaService;
use App\Services\Curricular\EvaluacionBimestral\NivelLogroBimestralService;
use App\Services\Curricular\EvaluacionBimestral\PesosComponentesService;
use App\Services\Curricular\EvaluacionBimestral\PesosEtaInternosService;
use App\Services\Curricular\EvaluacionBimestral\PromedioCriteriosService;
use App\Services\Curricular\EvaluacionBimestral\PromedioEtaService;
use PHPUnit\Framework\Attributes\Test;

class EvaluacionBimestralTest extends EvaluacionBimestralTestCase
{
    #[Test]
    public function configuracion_por_defecto_crea_cuatro_componentes_y_tres_etas(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        $componentes = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(4, $componentes);
        $this->assertEqualsWithDelta(100.0, (float) $componentes->sum('peso'), 0.02);

        $eta = $this->componente($mallaCurso->id, $periodo->id, 'promedio_eta');
        $items = EvalBimEtaItem::query()->where('eval_bim_componente_id', $eta->id)->where('activo', true)->get();
        $this->assertCount(3, $items);
        $this->assertEqualsWithDelta(100.0, (float) $items->sum('peso_interno'), 0.02);
    }

    #[Test]
    public function promedio_criterios_ignora_criterios_sin_ce(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $docente = $this->docente();
        $est = $estudiantes[0];

        $temaConCe = $this->crearTemaActivo($mallaCurso, $periodo, 'Con CE');
        $this->crearNotaSemanalConCe($est, $temaConCe, 16.0, $docente);
        $this->crearTemaActivo($mallaCurso, $periodo, 'Sin CE');

        $res = (new PromedioCriteriosService)->calcularParaEstudiante($aula, $est->id);

        $this->assertFalse($res['pendiente']);
        $this->assertSame(16.0, $res['valor']);
        $this->assertSame(1, $res['cantidad_criterios']);
    }

    #[Test]
    public function promedio_criterios_pendiente_si_no_hay_ce_y_componente_activo(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $this->crearTemaActivo($mallaCurso, $periodo, 'Sin notas');

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $calculo->estadoCalculo);
        $this->assertContains('promedio_criterios', $calculo->pendientes);
    }

    #[Test]
    public function eta_con_cero_explicito_activa_participacion_para_toda_el_aula(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(2);
        $eta2 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 2');

        $this->guardarNotaEta($estudiantes[0], $eta2, 0.0);

        $participacion = (new EtaParticipacionPorAulaService)->resolverParticipacion(
            $aula,
            EvalBimEtaItem::query()->where('eval_bim_componente_id', $eta2->eval_bim_componente_id)->where('activo', true)->get(),
        );

        $this->assertTrue($participacion['participantes']->contains('id', $eta2->id));
    }

    #[Test]
    public function eta_activa_sin_ninguna_nota_en_aula_no_participa(): void
    {
        [$mallaCurso, $periodo, $aula] = $this->prepararAulaEvaluacionBimestral(2);
        $eta2 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 2');

        $participacion = (new EtaParticipacionPorAulaService)->resolverParticipacion(
            $aula,
            EvalBimEtaItem::query()->where('id', $eta2->id)->get(),
        );

        $this->assertCount(0, $participacion['participantes']);
    }

    #[Test]
    public function promedio_eta_activo_sin_participantes_deja_resultado_pendiente(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $docente = $this->docente();
        $tema = $this->crearTemaActivo($mallaCurso, $periodo);
        $this->crearNotaSemanalConCe($estudiantes[0], $tema, 14.0, $docente);
        $this->guardarNotaScalar($estudiantes[0], $this->componente($mallaCurso->id, $periodo->id, 'oral'), 16.0);
        $this->guardarNotaScalar($estudiantes[0], $this->componente($mallaCurso->id, $periodo->id, 'examen_bimestral'), 12.0);

        $resultado = (new EvalBimResultadoPersistService)->recalcularEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $resultado->estado_calculo);
        $this->assertNull($resultado->nivel_logro_numerico);
        $this->assertNull($resultado->promedio_eta);
    }

    #[Test]
    public function eta_participante_sin_nota_del_alumno_cuenta_cero_en_promedio(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(2);
        $eta1 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1');
        $eta2 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 2');

        $this->guardarNotaEta($estudiantes[0], $eta1, 18.0);
        $this->guardarNotaEta($estudiantes[0], $eta2, 20.0);
        $this->guardarNotaEta($estudiantes[1], $eta1, 20.0);

        $etaAula = (new PromedioEtaService)->calcularParaAula(
            $aula,
            EvalBimEtaItem::query()->where('eval_bim_componente_id', $eta1->eval_bim_componente_id)->where('activo', true)->get(),
        );

        $this->assertSame(19.0, $etaAula['por_estudiante'][$estudiantes[0]->id]['valor']);
        $this->assertSame(10.0, $etaAula['por_estudiante'][$estudiantes[1]->id]['valor']);
    }

    #[Test]
    public function eta_inactiva_en_config_no_participa_aunque_haya_notas(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $eta3 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 3');
        $eta3->activo = false;
        $eta3->save();

        $this->guardarNotaEta($estudiantes[0], $eta3, 18.0);

        $participacion = (new EtaParticipacionPorAulaService)->resolverParticipacion(
            $aula,
            EvalBimEtaItem::query()->where('eval_bim_componente_id', $eta3->eval_bim_componente_id)->where('activo', true)->get(),
        );

        $this->assertCount(0, $participacion['participantes']);
    }

    #[Test]
    public function oral_activo_vacio_deja_pendiente(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $docente = $this->docente();
        $tema = $this->crearTemaActivo($mallaCurso, $periodo);
        $this->crearNotaSemanalConCe($estudiantes[0], $tema, 14.0, $docente);
        $this->guardarNotaEta($estudiantes[0], $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1'), 15.0);
        $this->guardarNotaScalar($estudiantes[0], $this->componente($mallaCurso->id, $periodo->id, 'examen_bimestral'), 12.0);

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $calculo->estadoCalculo);
        $this->assertContains('oral', $calculo->pendientes);
        $this->assertNull($calculo->oral);
    }

    #[Test]
    public function examen_activo_vacio_deja_pendiente(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $docente = $this->docente();
        $tema = $this->crearTemaActivo($mallaCurso, $periodo);
        $this->crearNotaSemanalConCe($estudiantes[0], $tema, 14.0, $docente);
        $this->guardarNotaScalar($estudiantes[0], $this->componente($mallaCurso->id, $periodo->id, 'oral'), 16.0);
        $this->guardarNotaEta($estudiantes[0], $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1'), 15.0);

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $calculo->estadoCalculo);
        $this->assertContains('examen_bimestral', $calculo->pendientes);
    }

    #[Test]
    public function componentes_inactivos_no_participan_en_nivel(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $oral = $this->componente($mallaCurso->id, $periodo->id, 'oral');
        $oral->activo = false;
        $oral->save();

        (new PesosComponentesService)->redistribuirTrasDesactivar($mallaCurso->id, $periodo->id);

        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Completo, $calculo->estadoCalculo);
        $this->assertNull($calculo->oral);
        $this->assertEqualsWithDelta(13.67, (float) $calculo->nivelLogroNumerico, 0.05);
    }

    #[Test]
    public function pesos_se_redistribuyen_al_agregar_componente_personalizado(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();

        (new EvaluacionBimestralConfiguracionService)->crearComponentePersonalizado(
            $mallaCurso->id,
            $periodo->id,
            'Proyecto',
        );

        $activos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(5, $activos);
        foreach ($activos as $c) {
            $this->assertEqualsWithDelta(20.0, (float) $c->peso, 0.02);
        }
    }

    #[Test]
    public function pesos_se_redistribuyen_al_desactivar_componente(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $oral = $this->componente($mallaCurso->id, $periodo->id, 'oral');
        $oral->activo = false;
        $oral->save();

        (new PesosComponentesService)->redistribuirTrasDesactivar($mallaCurso->id, $periodo->id);

        $activos = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(3, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso'), 0.02);
        foreach ($activos as $c) {
            $this->assertEqualsWithDelta(33.33, (float) $c->peso, 0.02);
        }
    }

    #[Test]
    public function pesos_eta_se_redistribuyen_al_agregar_eta(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $compEta = $this->componente($mallaCurso->id, $periodo->id, 'promedio_eta');

        $nueva = EvalBimEtaItem::query()->create([
            'eval_bim_componente_id' => $compEta->id,
            'nombre' => 'ETA 4',
            'peso_interno' => 0,
            'orden' => 4,
            'activo' => true,
        ]);

        (new PesosEtaInternosService)->redistribuirTrasAgregar($nueva, $compEta->id);

        $activos = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $compEta->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(4, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso_interno'), 0.02);
    }

    #[Test]
    public function pesos_eta_se_redistribuyen_al_desactivar_eta(): void
    {
        [$mallaCurso, $periodo] = $this->prepararAulaEvaluacionBimestral();
        $compEta = $this->componente($mallaCurso->id, $periodo->id, 'promedio_eta');
        $eta3 = $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 3');
        $eta3->activo = false;
        $eta3->save();

        (new PesosEtaInternosService)->redistribuirTrasDesactivar($compEta->id);

        $activos = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $compEta->id)
            ->where('activo', true)
            ->get();

        $this->assertCount(2, $activos);
        $this->assertEqualsWithDelta(100.0, (float) $activos->sum('peso_interno'), 0.02);
    }

    #[Test]
    public function nivel_logro_calcula_correctamente_con_cuatro_componentes_completos(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Completo, $calculo->estadoCalculo);
        $this->assertEqualsWithDelta(14.25, (float) $calculo->nivelLogroNumerico, 0.01);
    }

    #[Test]
    public function nivel_literal_ad_a_b_c(): void
    {
        $escala = new EscalaLogroService;

        $this->assertSame('AD', $escala->literalDesdeNumerico(19.0));
        $this->assertSame('A', $escala->literalDesdeNumerico(15.0));
        $this->assertSame('B', $escala->literalDesdeNumerico(12.0));
        $this->assertSame('C', $escala->literalDesdeNumerico(8.0));
    }

    #[Test]
    public function nivel_literal_en_resultado_persistido(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $resultado = (new EvalBimResultadoPersistService)->recalcularEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame('A', $resultado->nivel_logro_literal);
        $this->assertEqualsWithDelta(14.25, (float) $resultado->nivel_logro_numerico, 0.01);
    }

    #[Test]
    public function conclusion_descriptiva_opcional_no_afecta_calculo_numerico(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $persist = new EvalBimResultadoPersistService;
        $r1 = $persist->recalcularEstudiante($aula, $estudiantes[0]->id, 'Texto inicial');
        $nivel1 = $r1->nivel_logro_numerico;

        $r2 = $persist->actualizarConclusion($aula, $estudiantes[0]->id, 'Conclusión ampliada sin recalcular nivel');
        $this->assertSame('Conclusión ampliada sin recalcular nivel', $r2->conclusion_descriptiva);
        $this->assertEquals($nivel1, $r2->nivel_logro_numerico);
    }

    #[Test]
    public function eta_esta_cargada_distingue_null_y_cero(): void
    {
        $this->assertFalse(EtaParticipacionPorAulaService::etaEstaCargada(null));
        $this->assertTrue(EtaParticipacionPorAulaService::etaEstaCargada(0.0));
        $this->assertTrue(EtaParticipacionPorAulaService::etaEstaCargada(12.5));
    }

    #[Test]
    public function personalizado_activo_vacio_deja_pendiente(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(1);
        $personalizado = (new EvaluacionBimestralConfiguracionService)->crearComponentePersonalizado(
            $mallaCurso->id,
            $periodo->id,
            'Proyecto',
        );

        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $calculo = (new NivelLogroBimestralService)->calcularParaEstudiante($aula, $estudiantes[0]->id);

        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $calculo->estadoCalculo);
        $this->assertContains($personalizado->codigo, $calculo->pendientes);
    }

    #[Test]
    public function resultado_cache_se_actualiza_al_recalcular_aula(): void
    {
        [$mallaCurso, $periodo, $aula, $estudiantes] = $this->prepararAulaEvaluacionBimestral(2);
        $this->llenarComponentesCompletosParaNivel($aula, $mallaCurso, $periodo, $estudiantes);

        $resultados = (new EvalBimResultadoPersistService)->recalcularAula($aula);

        $this->assertCount(2, $resultados);
        $this->assertSame(2, EvalBimResultado::query()->where('malla_curso_id', $mallaCurso->id)->count());
        $this->assertNotNull($resultados[0]->calculado_en);
    }
}
