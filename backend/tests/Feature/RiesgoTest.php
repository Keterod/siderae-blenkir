<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\Nota;
use App\Models\User;
use App\Models\VariableSocioeconomica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RiesgoTest extends TestCase
{
    use RefreshDatabase;

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
        $estudiante = Estudiante::factory()->create([
            'anio_escolar' => '2026',
        ]);

        Nota::query()->create([
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'curso' => 'Matemática',
            'nota' => 12.5,
            'nota_conducta' => null,
        ]);

        Asistencia::query()->create([
            'estudiante_id' => $estudiante->id,
            'semana_inicio' => '2026-04-01',
            'estado' => 'presente',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'registrado_por' => $user->id,
        ]);

        VariableSocioeconomica::query()->create([
            'estudiante_id' => $estudiante->id,
            'composicion_familiar' => 'nuclear',
            'nivel_socioeconomico' => 'medio',
            'acceso_internet' => true,
            'distancia_colegio_km' => 2.5,
            'anio_escolar' => '2026',
        ]);

        return [$estudiante, $user];
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
            ->assertJsonFragment(['message' => 'Faltan datos mínimos para calcular el riesgo.']);

        Http::assertNothingSent();
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
