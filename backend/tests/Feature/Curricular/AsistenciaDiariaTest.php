<?php

namespace Tests\Feature\Curricular;

use App\Models\Asistencia;
use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurso;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\User;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class AsistenciaDiariaTest extends CurricularApiTestCase
{
    private const ANIO = '2026';

    private const FECHA = '2026-05-27';

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['registrar_asistencia_curricular', 'ver_asistencia_curricular'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    protected function usuarioConAsistenciaCurricular(array $extra = []): User
    {
        return $this->userWithPermissions(array_merge([
            'registrar_asistencia_curricular',
            'ver_asistencia_curricular',
        ], $extra));
    }

    protected function coordinadorAsistencia(): User
    {
        return $this->userWithPermissions([
            'gestionar_asignaciones_docente',
            'registrar_asistencia_curricular',
            'ver_asistencia_curricular',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function queryContextoInicial(): array
    {
        return [
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'sede' => 'chilca',
            'grado' => '3 años',
            'seccion' => 'A',
            'fecha' => self::FECHA,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function queryContextoPrimaria(): array
    {
        return [
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'sede' => 'chilca',
            'grado' => '1°',
            'seccion' => 'A',
            'fecha' => self::FECHA,
        ];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: Estudiante, 2: User}
     */
    private function prepararDocenteConAsignacionInicial(): array
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
            ->firstOrFail();

        $docente = $this->usuarioConAsistenciaCurricular();
        $docente->assignRole('docente');

        $asignacionResponse = $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'grado' => '3 años',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $asignacionResponse->assertCreated();
        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionResponse->json('id'));

        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'grado' => '3 años',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        return [$asignacion, $estudiante, $docente];
    }

    #[Test]
    public function formulario_lista_estudiantes_inicial_tres_anos_chilca(): void
    {
        [, $estudiante, $docente] = $this->prepararDocenteConAsignacionInicial();

        $response = $this->actingAs($docente)->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query($this->queryContextoInicial())
        );

        $response->assertOk()
            ->assertJsonPath('contexto.grado', '3 años')
            ->assertJsonPath('contexto.nivel', CatalogoNivelGrado::NIVEL_INICIAL);

        $ids = collect($response->json('estudiantes'))->pluck('id')->all();
        $this->assertContains($estudiante->id, $ids);
    }

    #[Test]
    public function formulario_lista_estudiantes_primaria(): void
    {
        $coordinador = $this->coordinadorAsistencia();

        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '1°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $response = $this->actingAs($coordinador)->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query($this->queryContextoPrimaria())
        );

        $response->assertOk()
            ->assertJsonPath('contexto.nivel', CatalogoNivelGrado::NIVEL_PRIMARIA);

        $ids = collect($response->json('estudiantes'))->pluck('id')->all();
        $this->assertContains($estudiante->id, $ids);
    }

    #[Test]
    public function coordinador_puede_ver_cualquier_aula(): void
    {
        Estudiante::factory()->create([
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '2°',
            'seccion' => 'B',
            'sede' => 'auquimarca',
        ]);

        $this->actingAs($this->coordinadorAsistencia())->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
                'sede' => 'auquimarca',
                'grado' => '2°',
                'seccion' => 'B',
                'fecha' => self::FECHA,
            ])
        )->assertOk();
    }

    #[Test]
    public function docente_puede_ver_aula_con_asignacion_activa(): void
    {
        [, $estudiante, $docente] = $this->prepararDocenteConAsignacionInicial();

        $this->actingAs($docente)->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query($this->queryContextoInicial())
        )->assertOk()->assertJsonFragment(['id' => $estudiante->id]);
    }

    #[Test]
    public function docente_con_asignacion_primaria_2do_puede_ver_formulario_grado_estudiante_2(): void
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
                'grado' => '2do',
            ])
        )->assertOk();

        $mallaCurso = \App\Models\Curricular\MallaCurso::query()->firstOrFail();
        $docente = $this->usuarioConAsistenciaCurricular();
        $docente->assignRole('docente');

        $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->assertCreated();

        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '2°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $this->actingAs($docente)->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
                'sede' => 'chilca',
                'grado' => '2°',
                'seccion' => 'A',
                'fecha' => self::FECHA,
            ])
        )->assertOk()->assertJsonFragment(['id' => $estudiante->id]);
    }

    #[Test]
    public function docente_no_puede_ver_aula_sin_asignacion_activa(): void
    {
        $docente = $this->usuarioConAsistenciaCurricular();

        $this->actingAs($docente)->getJson(
            '/api/curricular/asistencias-diarias/formulario?'.http_build_query($this->queryContextoInicial())
        )->assertForbidden();
    }

    #[Test]
    public function bulk_crea_asistencia_diaria(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $coordinador = $this->coordinadorAsistencia();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'presente', 'observacion' => null],
            ],
        ]);

        $this->actingAs($coordinador)->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertCreated()
            ->assertJsonPath('creados', 1)
            ->assertJsonPath('actualizados', 0);

        $this->assertDatabaseHas('asistencias_diarias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'presente',
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
        ]);
        $this->assertSame(1, AsistenciaDiaria::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereDate('fecha', self::FECHA)
            ->count());
    }

    #[Test]
    public function bulk_actualiza_sin_duplicar(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $coordinador = $this->coordinadorAsistencia();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'presente'],
            ],
        ]);

        $this->actingAs($coordinador)->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertCreated();

        $registradoPor = AsistenciaDiaria::query()->where('estudiante_id', $estudiante->id)->value('registrado_por');

        $payload['filas'][0]['estado'] = 'falta';
        $payload['filas'][0]['observacion'] = 'Sin aviso';

        $this->actingAs($coordinador)->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertCreated()
            ->assertJsonPath('creados', 0)
            ->assertJsonPath('actualizados', 1);

        $this->assertSame(1, AsistenciaDiaria::query()->where('estudiante_id', $estudiante->id)->count());
        $this->assertDatabaseHas('asistencias_diarias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'falta',
            'observacion' => 'Sin aviso',
            'registrado_por' => $registradoPor,
        ]);
    }

    #[Test]
    public function bulk_rechaza_estudiante_fuera_del_aula(): void
    {
        [, $estudianteAula] = $this->prepararDocenteConAsignacionInicial();
        $otro = Estudiante::factory()->create([
            'anio_escolar' => self::ANIO,
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'grado' => '5 años',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudianteAula->id, 'estado' => 'presente'],
                ['estudiante_id' => $otro->id, 'estado' => 'presente'],
            ],
        ]);

        $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['filas.1.estudiante_id']);
    }

    #[Test]
    public function bulk_rechaza_estudiante_inactivo(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $estudiante->update(['activo' => false]);

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'presente'],
            ],
        ]);

        $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['filas.0.estudiante_id']);
    }

    #[Test]
    public function bulk_rechaza_estado_invalido(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'ausente'],
            ],
        ]);

        $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['filas.0.estado']);
    }

    #[Test]
    public function bulk_acepta_observacion_opcional(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'justificado'],
            ],
        ]);

        $this->actingAs($this->coordinadorAsistencia())->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('asistencias_diarias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'justificado',
            'observacion' => null,
        ]);
    }

    #[Test]
    public function docente_no_puede_registrar_aula_sin_asignacion(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $docenteSinAula = $this->usuarioConAsistenciaCurricular();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'presente'],
            ],
        ]);

        $this->actingAs($docenteSinAula)->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertForbidden();
    }

    #[Test]
    public function resumen_por_estudiante_calcula_totales(): void
    {
        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $coordinador = $this->coordinadorAsistencia();

        foreach (['presente', 'tarde', 'falta', 'justificado'] as $i => $estado) {
            AsistenciaDiaria::query()->create([
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => self::ANIO,
                'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
                'grado' => '3 años',
                'seccion' => 'A',
                'sede' => 'chilca',
                'fecha' => '2026-05-'.str_pad((string) ($i + 20), 2, '0', STR_PAD_LEFT),
                'estado' => $estado,
                'registrado_por' => $coordinador->id,
            ]);
        }

        $response = $this->actingAs($coordinador)->getJson(
            '/api/curricular/asistencias-diarias/resumen?'.http_build_query([
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => self::ANIO,
            ])
        );

        $response->assertOk()
            ->assertJsonPath('totales.total_registros', 4)
            ->assertJsonPath('totales.presente', 1)
            ->assertJsonPath('totales.tarde', 1)
            ->assertJsonPath('totales.falta', 1)
            ->assertJsonPath('totales.justificado', 1)
            ->assertJsonPath('totales.porcentaje_asistencia_efectiva', 75);
    }

    #[Test]
    public function bulk_no_dispara_riesgo_academico(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(['*' => Http::response(['indice_riesgo' => 0.5], 200)]);

        [, $estudiante] = $this->prepararDocenteConAsignacionInicial();
        $coordinador = $this->coordinadorAsistencia();

        $antesRiesgo = IndiceRiesgo::query()->count();
        $antesLegacy = Asistencia::query()->count();

        $payload = array_merge($this->queryContextoInicial(), [
            'filas' => [
                ['estudiante_id' => $estudiante->id, 'estado' => 'falta'],
            ],
        ]);

        $this->actingAs($coordinador)->postJson('/api/curricular/asistencias-diarias/bulk', $payload)
            ->assertCreated()
            ->assertJsonMissing(['riesgo']);

        $this->assertSame($antesRiesgo, IndiceRiesgo::query()->count());
        $this->assertSame($antesLegacy, Asistencia::query()->count());
        Http::assertNothingSent();
    }
}
