<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\Nota;
use App\Models\User;
use App\Services\RiesgoAcademicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\Support\RiesgoCurricularFixtures;
use Tests\TestCase;

class RiesgoTest extends TestCase
{
    use RefreshDatabase;
    use RiesgoCurricularFixtures;

    private function permisoProcesarRiesgo(): void
    {
        Permission::firstOrCreate([
            'name' => 'procesar_riesgo',
            'guard_name' => 'web',
        ]);
    }

    private function usuarioConPermiso(): User
    {
        $this->permisoProcesarRiesgo();
        $user = User::factory()->create();
        $user->givePermissionTo('procesar_riesgo');

        return $user;
    }

    private function usuarioSinPermisoProcesar(): User
    {
        $this->permisoProcesarRiesgo();

        return User::factory()->create();
    }

    /**
     * @return array{0: Estudiante, 1: User}
     */
    private function estudianteConDatosMinimos(): array
    {
        $user = $this->usuarioConPermiso();

        return $this->estudianteCurricularConDatosMinimos([], 12.5, $user);
    }

    public function test_usuario_con_permiso_procesa_riesgo_correctamente(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(function () {
            return Http::response(['indice_riesgo' => 0.55], 200);
        });

        $response = $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo",
            ['bimestre' => '2']
        );

        $response->assertCreated()
            ->assertJsonPath('nivel', 'Medio')
            ->assertJsonPath('bimestre', '2');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'http://ml-test.local/predict'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante] = $this->estudianteConDatosMinimos();
        $otro = $this->usuarioSinPermisoProcesar();

        Http::fake([
            '*' => Http::response(['indice_riesgo' => 0.5], 200),
        ]);

        $this->actingAs($otro)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        )->assertForbidden();
    }

    public function test_visitante_sin_sesion_recibe_401(): void
    {
        $estudiante = Estudiante::factory()->create();

        $this->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        )->assertUnauthorized();
    }

    public function test_rechaza_procesamiento_si_faltan_datos_minimos(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        Http::fake();

        $response = $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        );

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Faltan datos mínimos para calcular el riesgo.'])
            ->assertJsonStructure(['errors' => [
                'datos_academicos_curriculares',
                'asistencias_curriculares',
            ]]);

        Http::assertNothingSent();
    }

    public function test_procesa_riesgo_con_eval_bim_resultados_y_asistencias_diarias(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.45], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        Http::assertSent(function ($request) {
            $body = $request->data();

            return isset($body['promedio_notas'], $body['porcentaje_asistencia'])
                && (float) $body['promedio_notas'] > 0
                && (float) $body['porcentaje_asistencia'] > 0;
        });
    }

    public function test_procesa_riesgo_con_notas_semanales_como_fallback(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $this->crearNotaSemanalCeRiesgo($estudiante, $user, 16.0);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.4], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        Http::assertSent(fn ($request) => (float) $request->data()['promedio_notas'] === 16.0);
    }

    public function test_falla_si_no_hay_asistencias_diarias(): void
    {
        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026', 'nivel' => 'primaria']);

        $this->crearEvalBimResultadoRiesgo($estudiante);

        Http::fake();

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['asistencias_curriculares']]);

        Http::assertNothingSent();
    }

    public function test_procesa_riesgo_sin_variables_socioeconomicas(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $this->crearEvalBimResultadoRiesgo($estudiante);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.45], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        Http::assertSent(function ($request) {
            $body = $request->data();

            return isset($body['promedio_notas'], $body['porcentaje_asistencia'], $body['reportes_conductuales'])
                && ! isset($body['nivel_socioeconomico'])
                && ! isset($body['fast_test_puntaje']);
        });
    }

    public function test_variables_utilizadas_no_incluye_vse_ni_fast_test(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.5], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        $registro = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->firstOrFail();
        $vu = $registro->variables_utilizadas;

        $this->assertIsArray($vu);
        $this->assertTrue($vu['notas']);
        $this->assertTrue($vu['asistencia']);
        $this->assertFalse($vu['variables_socioeconomicas']);
        $this->assertFalse($vu['fast_test']);
    }

    public function test_no_exige_fast_test(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.5], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        Http::assertSent(function ($request) {
            return ! isset($request->data()['fast_test_puntaje']);
        });
    }

    public function test_reportes_conductuales_son_opcionales(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'sede' => 'chilca',
        ]);

        $this->crearEvalBimResultadoRiesgo($estudiante);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);
        // No se crean reportes conductuales

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.4], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        Http::assertSent(function ($request) {
            return (int) $request->data()['reportes_conductuales'] === 0;
        });
    }

    public function test_inicial_devuelve_mensaje_controlado(): void
    {
        $user = $this->usuarioConPermiso();
        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => '2026',
            'nivel' => 'inicial',
            'grado' => '3 años',
        ]);

        Http::fake();

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => RiesgoAcademicoService::MENSAJE_INICIAL_NO_DISPONIBLE,
            ]);

        Http::assertNothingSent();
    }

    public function test_no_requiere_nota_ni_asistencia_legacy(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        $this->assertSame(0, Nota::query()->where('estudiante_id', $estudiante->id)->count());
        $this->assertSame(0, Asistencia::query()->where('estudiante_id', $estudiante->id)->count());

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.5], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();
    }

    public function test_guarda_indice_de_riesgo_en_base_de_datos(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(function () {
            return Http::response(['indice_riesgo' => 0.42], 200);
        });

        $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        )->assertCreated();

        $this->assertDatabaseHas('indices_riesgo', [
            'estudiante_id' => $estudiante->id,
            'nivel' => 'Medio',
        ]);

        $this->assertSame(1, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }

    public static function indicesYClasificacionEsperada(): array
    {
        return [
            'alto' => [0.82, 'Alto'],
            'medio' => [0.55, 'Medio'],
            'bajo' => [0.25, 'Bajo'],
            'borde_alto' => [0.70, 'Alto'],
            'borde_medio' => [0.40, 'Medio'],
        ];
    }

    #[DataProvider('indicesYClasificacionEsperada')]
    public function test_clasificacion_alto_medio_bajo_funciona(float $indice, string $nivelEsperado): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake(function () use ($indice) {
            return Http::response(['indice_riesgo' => $indice], 200);
        });

        $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        )->assertCreated()->assertJsonPath('nivel', $nivelEsperado);
    }

    public function test_error_controlado_si_flask_no_responde(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimos();

        Http::fake([
            '*' => Http::response('Service Unavailable', 503),
        ]);

        $response = $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        );

        $response->assertStatus(503)->assertJsonStructure(['message']);

        $this->assertSame(0, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }
}
