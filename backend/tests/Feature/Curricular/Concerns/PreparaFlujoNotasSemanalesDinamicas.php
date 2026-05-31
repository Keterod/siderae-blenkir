<?php

namespace Tests\Feature\Curricular\Concerns;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use Illuminate\Support\Collection;

trait PreparaFlujoNotasSemanalesDinamicas
{
    protected const ANIO = '2026';

    protected const NIVEL = 'primaria';

    /**
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    protected function componentesActivos(string $nivel): Collection
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
    protected function payloadNotasComponentes(Collection $componentes, array $notasPorCodigo): array
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
    protected function prepararFlujoNotasPrimaria(): array
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

    /**
     * @return array{0: DocenteCursoAula, 1: TemaSemanal, 2: Estudiante}
     */
    protected function prepararFlujoNotasInicial(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'grado' => '3 años',
            ])
        )->assertOk();

        $mallaCurso = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', 'inicial')
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

        $coordinador = $this->coordinador();
        $temaId = $this->actingAs($coordinador)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio Inicial Excel',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $docenteUser = $this->docente();
        $asignacionId = $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => 'inicial',
            'grado' => '3 años',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->json('id');

        $estudiante = Estudiante::factory()->create([
            'grado' => '3 años',
            'seccion' => 'A',
            'nivel' => 'inicial',
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO,
        ]);

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);
        $tema = TemaSemanal::query()->findOrFail($temaId);

        return [$asignacion, $tema, $estudiante];
    }
}
