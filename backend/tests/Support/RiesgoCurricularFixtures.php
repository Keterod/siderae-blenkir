<?php

namespace Tests\Support;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\VariableSocioeconomica;
use App\Services\Curricular\EquivalenciaGradoService;
use Database\Seeders\CurricularModuleSeeder;
use Spatie\Permission\Models\Permission;

trait RiesgoCurricularFixtures
{
    protected bool $curricularRiesgoSeeded = false;

    protected function seedCurricularParaRiesgo(): void
    {
        if ($this->curricularRiesgoSeeded) {
            return;
        }

        $this->seed(CurricularModuleSeeder::class);
        foreach (['ver_malla_curricular', 'gestionar_temas_semanales'] as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }
        $this->curricularRiesgoSeeded = true;
    }

    protected function resolverTemaSemanalRiesgo(MallaCurso $mallaCurso, PeriodoAcademico $periodo): TemaSemanal
    {
        $existente = TemaSemanal::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodo->id)
            ->where('activo', true)
            ->first();

        if ($existente !== null) {
            return $existente;
        }

        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();
        $semana = SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();

        $coordinador = User::factory()->create();
        $coordinador->givePermissionTo('gestionar_temas_semanales');

        $temaId = $this->actingAs($coordinador)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio riesgo test',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        return TemaSemanal::query()->findOrFail($temaId);
    }

    protected function asegurarMallaCursoParaEstudiante(Estudiante $estudiante): MallaCurso
    {
        $this->seedCurricularParaRiesgo();

        $gradoCurricular = (new EquivalenciaGradoService)->aCurricular(
            (string) $estudiante->nivel,
            (string) $estudiante->grado,
        ) ?? $estudiante->grado;

        $consultor = User::factory()->create();
        $consultor->givePermissionTo('ver_malla_curricular');

        $this->actingAs($consultor)->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => $estudiante->anio_escolar,
                'nivel' => $estudiante->nivel,
                'grado' => $gradoCurricular,
            ]),
        )->assertOk();

        return MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', $estudiante->anio_escolar)
                ->where('nivel', $estudiante->nivel)
                ->where('grado', $gradoCurricular))
            ->where('activo', true)
            ->firstOrFail();
    }

    protected function crearVariableSocioeconomicaRiesgo(Estudiante $estudiante, array $override = []): VariableSocioeconomica
    {
        return VariableSocioeconomica::query()->create(array_merge([
            'estudiante_id' => $estudiante->id,
            'composicion_familiar' => 'nuclear',
            'nivel_socioeconomico' => 'medio',
            'acceso_internet' => true,
            'distancia_colegio_km' => 2.5,
            'anio_escolar' => $estudiante->anio_escolar,
        ], $override));
    }

    protected function crearAsistenciasDiariasRiesgo(Estudiante $estudiante, User $user, int $cantidad = 2): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            AsistenciaDiaria::query()->create([
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => $estudiante->anio_escolar,
                'nivel' => $estudiante->nivel,
                'grado' => $estudiante->grado,
                'seccion' => $estudiante->seccion,
                'sede' => $estudiante->sede,
                'fecha' => sprintf('2026-05-%02d', 10 + $i),
                'estado' => $i === 0 ? 'presente' : 'tarde',
                'registrado_por' => $user->id,
            ]);
        }
    }

    protected function crearEvalBimResultadoRiesgo(
        Estudiante $estudiante,
        float $nivelLogroNumerico = 14.0,
    ): EvalBimResultado {
        $mallaCurso = $this->asegurarMallaCursoParaEstudiante($estudiante);

        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', $estudiante->anio_escolar)
            ->where('bimestre', '1')
            ->firstOrFail();

        return EvalBimResultado::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'sede' => $estudiante->sede,
                'grado' => $estudiante->grado,
                'seccion' => $estudiante->seccion,
            ],
            [
                'nivel_logro_numerico' => $nivelLogroNumerico,
                'nivel_logro_literal' => 'A',
                'estado_calculo' => EvalBimEstadoCalculo::Completo,
                'promedio_criterios' => $nivelLogroNumerico,
            ],
        );
    }

    protected function crearNotaSemanalCeRiesgo(
        Estudiante $estudiante,
        User $user,
        float $ceCalculado = 15.0,
    ): NotaSemanal {
        $mallaCurso = $this->asegurarMallaCursoParaEstudiante($estudiante);

        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', $estudiante->anio_escolar)
            ->where('bimestre', '1')
            ->firstOrFail();

        $tema = $this->resolverTemaSemanalRiesgo($mallaCurso, $periodo);

        return NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $user->id,
                'nota_cuaderno' => $ceCalculado,
                'nota_libro' => $ceCalculado,
                'nota_tarea' => $ceCalculado,
                'ce_calculado' => $ceCalculado,
                'fecha_registro' => '2026-05-15',
            ],
        );
    }

    /**
     * @return array{0: Estudiante, 1: User}
     */
    protected function estudianteCurricularConDatosMinimos(
        array $estudianteOverride = [],
        float $nivelLogroBimestral = 14.0,
        ?User $user = null,
    ): array {
        $user ??= User::factory()->create();
        $estudiante = Estudiante::factory()->create(array_merge([
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ], $estudianteOverride));

        $this->crearEvalBimResultadoRiesgo($estudiante, $nivelLogroBimestral);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);
        $this->crearVariableSocioeconomicaRiesgo($estudiante);

        return [$estudiante, $user];
    }
}
