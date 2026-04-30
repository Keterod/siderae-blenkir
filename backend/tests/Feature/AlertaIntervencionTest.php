<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\Intervencion;
use App\Models\Nota;
use App\Models\User;
use App\Models\VariableSocioeconomica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AlertaIntervencionTest extends TestCase
{
    use RefreshDatabase;

    private function crearPermisosAlertas(): void
    {
        foreach (['ver_alertas', 'registrar_intervencion', 'procesar_riesgo', 'registrar_datos_academicos'] as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
    }

    private function usuarioVerAlertas(): User
    {
        $this->crearPermisosAlertas();
        $user = User::factory()->create();
        $user->givePermissionTo('ver_alertas');

        return $user;
    }

    private function usuarioVerYRegistrar(): User
    {
        $this->crearPermisosAlertas();
        $user = User::factory()->create();
        $user->givePermissionTo(['ver_alertas', 'registrar_intervencion']);

        return $user;
    }

    private function usuarioProcesarRiesgo(): User
    {
        $this->crearPermisosAlertas();
        $user = User::factory()->create();
        $user->givePermissionTo(['procesar_riesgo', 'registrar_datos_academicos']);

        return $user;
    }

    /**
     * @return array{0: Estudiante, 1: User}
     */
    private function estudianteConDatosMinimosYUsuarioProcesar(): array
    {
        $user = $this->usuarioProcesarRiesgo();

        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        Nota::query()->create([
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'curso' => 'Matemática',
            'nota' => 10,
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
            'nivel_socioeconomico' => 'bajo',
            'acceso_internet' => false,
            'distancia_colegio_km' => 3,
            'anio_escolar' => '2026',
        ]);

        return [$estudiante, $user];
    }

    public function test_genera_alerta_automatica_al_procesar_riesgo_alto(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimosYUsuarioProcesar();

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.85], 200));

        $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/procesar-riesgo"
        )->assertCreated()->assertJsonPath('nivel', 'Alto');

        $this->assertDatabaseHas('alertas', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'pendiente',
        ]);

        $indice = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->latest('id')->first();
        $this->assertNotNull($indice);
        $this->assertDatabaseHas('alertas', [
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
        ]);
    }

    public function test_no_duplica_alerta_si_ya_existe_activa(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);

        [$estudiante, $user] = $this->estudianteConDatosMinimosYUsuarioProcesar();

        Http::fake(fn () => Http::response(['indice_riesgo' => 0.85], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")->assertCreated();
        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")->assertCreated();

        $this->assertSame(1, Alerta::query()->where('estudiante_id', $estudiante->id)->count());
        $this->assertSame(2, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }

    public function test_usuario_con_permiso_puede_listar_alertas(): void
    {
        $user = $this->usuarioVerAlertas();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.8,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => ['promedio_notas' => 10],
            'modelos_scores' => null,
        ]);

        Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'factores_influyentes' => null,
            'recomendacion' => 'Seguimiento',
        ]);

        $this->actingAs($user)->getJson('/api/alertas')->assertOk()->assertJsonCount(1);
    }

    public function test_usuario_sin_permiso_recibe_403_al_listar_alertas(): void
    {
        $this->crearPermisosAlertas();
        $sinPermiso = User::factory()->create();

        $this->actingAs($sinPermiso)->getJson('/api/alertas')->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_ver_detalle_de_alerta(): void
    {
        $user = $this->usuarioVerAlertas();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.72,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ]);

        $alerta = Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'recomendacion' => 'Test',
        ]);

        $this->actingAs($user)->getJson("/api/alertas/{$alerta->id}")
            ->assertOk()
            ->assertJsonPath('id', $alerta->id)
            ->assertJsonPath('estado', 'pendiente');
    }

    public function test_usuario_con_permiso_puede_registrar_intervencion(): void
    {
        $user = $this->usuarioVerYRegistrar();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.75,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ]);

        $alerta = Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'recomendacion' => 'Seguimiento',
        ]);

        $response = $this->actingAs($user)->postJson("/api/alertas/{$alerta->id}/intervenciones", [
            'tipo' => 'academica',
            'descripcion' => 'Entrevista con apoderados.',
            'fecha' => '2026-04-15',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('intervenciones', [
            'alerta_id' => $alerta->id,
            'tipo' => 'academica',
        ]);
    }

    public function test_al_registrar_intervencion_alerta_pasa_a_en_atencion(): void
    {
        $user = $this->usuarioVerYRegistrar();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.75,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ]);

        $alerta = Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'recomendacion' => 'Seguimiento',
        ]);

        $this->actingAs($user)->postJson("/api/alertas/{$alerta->id}/intervenciones", [
            'tipo' => 'emocional',
            'descripcion' => 'Acompañamiento emocional.',
            'fecha' => '2026-04-10',
        ])->assertCreated();

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => 'en_atencion',
        ]);
    }

    public function test_no_se_puede_cerrar_alerta_sin_intervencion(): void
    {
        $user = $this->usuarioVerYRegistrar();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.75,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ]);

        $alerta = Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'recomendacion' => 'Seguimiento',
        ]);

        $this->actingAs($user)->postJson("/api/alertas/{$alerta->id}/cerrar", [
            'resultado_cierre' => 'Caso resuelto',
        ])->assertStatus(422);
    }

    public function test_se_puede_cerrar_alerta_con_intervencion_previa(): void
    {
        $user = $this->usuarioVerYRegistrar();
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.75,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ]);

        $alerta = Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'en_atencion',
            'recomendacion' => 'Seguimiento',
        ]);

        Intervencion::query()->create([
            'alerta_id' => $alerta->id,
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $user->id,
            'tipo' => 'familiar',
            'descripcion' => 'Contacto con familia',
            'fecha' => '2026-04-01',
        ]);

        $this->actingAs($user)->postJson("/api/alertas/{$alerta->id}/cerrar", [
            'resultado_cierre' => 'Seguimiento completado con mejoras observadas.',
        ])->assertOk();

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => 'cerrada',
        ]);

        $this->assertNotNull($alerta->fresh()->fecha_cierre);
        $this->assertSame($user->id, (int) $alerta->fresh()->cerrada_por);
    }

    public function test_visitante_sin_sesion_recibe_no_autorizado_al_listar_alertas(): void
    {
        $this->getJson('/api/alertas')->assertUnauthorized();
    }
}
