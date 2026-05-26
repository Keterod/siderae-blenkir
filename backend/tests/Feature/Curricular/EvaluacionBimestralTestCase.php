<?php

namespace Tests\Feature\Curricular;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimNotaEta;
use App\Models\Curricular\EvalBimNotaScalar;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionComponentesResolver;
use Carbon\Carbon;

abstract class EvaluacionBimestralTestCase extends CurricularApiTestCase
{
    protected function asegurarConfigBimestral(MallaCurso $mallaCurso, PeriodoAcademico $periodo): void
    {
        (new EvaluacionBimestralConfiguracionService)->asegurarConfiguracionPorDefecto(
            $mallaCurso->id,
            $periodo->id,
        );
    }

    /**
     * @return array{0: MallaCurso, 1: PeriodoAcademico, 2: AulaEvaluacionContext, 3: list<Estudiante>}
     */
    protected function prepararAulaEvaluacionBimestral(int $cantidadEstudiantes = 2): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();

        $this->asegurarConfigBimestral($mallaCurso, $periodo);

        $estudiantes = [];
        for ($i = 0; $i < $cantidadEstudiantes; $i++) {
            $estudiantes[] = Estudiante::factory()->create([
                'grado' => '2°',
                'seccion' => 'A',
                'nivel' => 'primaria',
                'sede' => 'chilca',
                'anio_escolar' => '2026',
            ]);
        }

        $aula = new AulaEvaluacionContext(
            mallaCursoId: $mallaCurso->id,
            periodoAcademicoId: $periodo->id,
            sede: 'chilca',
            grado: '2°',
            seccion: 'A',
            estudianteIds: array_map(fn (Estudiante $e) => $e->id, $estudiantes),
        );

        return [$mallaCurso, $periodo, $aula, $estudiantes];
    }

    protected function componente(
        int $mallaCursoId,
        int $periodoId,
        string $codigo,
    ): EvalBimComponente {
        return EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->where('codigo', $codigo)
            ->firstOrFail();
    }

    protected function etaPorNombre(int $mallaCursoId, int $periodoId, string $nombre): EvalBimEtaItem
    {
        $resolver = new EvaluacionComponentesResolver;
        $config = $resolver->resolver($mallaCursoId, $periodoId, false);

        return $config['eta_items']->firstWhere('nombre', $nombre)
            ?? throw new \RuntimeException("ETA {$nombre} no encontrada.");
    }

    protected function guardarNotaScalar(
        Estudiante $estudiante,
        EvalBimComponente $componente,
        ?float $nota,
        ?User $docente = null,
    ): void {
        EvalBimNotaScalar::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'eval_bim_componente_id' => $componente->id,
            ],
            [
                'nota' => $nota,
                'docente_id' => $docente?->id,
            ],
        );
    }

    protected function guardarNotaEta(
        Estudiante $estudiante,
        EvalBimEtaItem $eta,
        ?float $nota,
        ?User $docente = null,
    ): void {
        EvalBimNotaEta::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'eval_bim_eta_item_id' => $eta->id,
            ],
            [
                'nota' => $nota,
                'docente_id' => $docente?->id,
            ],
        );
    }

    protected function crearNotaSemanalConCe(
        Estudiante $estudiante,
        TemaSemanal $tema,
        float $ce,
        User $docente,
    ): void {
        NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $docente->id,
                'nota_cuaderno' => $ce,
                'nota_libro' => null,
                'nota_tarea' => null,
                'ce_calculado' => $ce,
                'fecha_registro' => Carbon::today(),
            ],
        );
    }

    protected function crearTemaActivo(MallaCurso $mallaCurso, PeriodoAcademico $periodo, string $titulo = 'Criterio test'): TemaSemanal
    {
        $competencia = \App\Models\Curricular\Competencia::query()
            ->where('area_id', $mallaCurso->area_id)
            ->firstOrFail();
        $capacidad = \App\Models\Curricular\Capacidad::query()
            ->where('competencia_id', $competencia->id)
            ->firstOrFail();

        $semana = \App\Models\Curricular\SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();

        $temaId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => $titulo,
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        return TemaSemanal::query()->findOrFail($temaId);
    }

    protected function llenarComponentesCompletosParaNivel(
        AulaEvaluacionContext $aula,
        MallaCurso $mallaCurso,
        PeriodoAcademico $periodo,
        array $estudiantes,
        float $promedioCriteriosCe = 14.0,
        float $oral = 16.0,
        float $eta1 = 15.0,
        float $examen = 12.0,
    ): void {
        $docente = $this->docente();
        $tema = $this->crearTemaActivo($mallaCurso, $periodo);

        foreach ($estudiantes as $est) {
            $this->crearNotaSemanalConCe($est, $tema, $promedioCriteriosCe, $docente);
            $this->guardarNotaScalar($est, $this->componente($mallaCurso->id, $periodo->id, 'oral'), $oral);
            $this->guardarNotaScalar($est, $this->componente($mallaCurso->id, $periodo->id, 'examen_bimestral'), $examen);
            $this->guardarNotaEta($est, $this->etaPorNombre($mallaCurso->id, $periodo->id, 'ETA 1'), $eta1);
        }
    }
}
