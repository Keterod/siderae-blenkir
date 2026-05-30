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
use Tests\Support\RiesgoCurricularFixtures;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;
    use RiesgoCurricularFixtures;

    private function seedPermissions(array $names): void
    {
        foreach ($names as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
    }

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_crear_estudiante_registra_actividad(): void
    {
        $this->seedPermissions(['gestionar_estudiantes']);
        $user = $this->userWith(['gestionar_estudiantes']);

        $response = $this->actingAs($user)->postJson('/api/estudiantes', [
            'codigo' => 'EST-LOG-01',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $response->assertCreated();
        $id = $response->json('id');
        $this->assertNotNull($id);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'estudiante.creado',
            'causer_id' => $user->id,
            'causer_type' => User::class,
            'subject_type' => Estudiante::class,
            'subject_id' => $id,
        ]);
    }

    public function test_editar_estudiante_registra_actividad(): void
    {
        $this->seedPermissions(['gestionar_estudiantes']);
        $user = $this->userWith(['gestionar_estudiantes']);
        $estudiante = Estudiante::factory()->create(['nombres' => 'Luis']);

        $this->actingAs($user)->putJson("/api/estudiantes/{$estudiante->id}", [
            'codigo' => $estudiante->codigo,
            'nombres' => 'Luis Carlos',
            'apellidos' => $estudiante->apellidos,
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => $estudiante->grado,
            'seccion' => $estudiante->seccion,
            'nivel' => $estudiante->nivel,
            'sede' => $estudiante->sede,
            'anio_escolar' => $estudiante->anio_escolar,
        ])->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'estudiante.actualizado',
            'causer_id' => $user->id,
            'subject_type' => Estudiante::class,
            'subject_id' => $estudiante->id,
        ]);
    }

    public function test_registrar_nota_registra_actividad(): void
    {
        $this->seedPermissions(['registrar_datos_academicos']);
        $user = $this->userWith(['registrar_datos_academicos']);
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        $response = $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/notas", [
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'curso' => 'Matemática',
            'nota' => 15,
            'nota_conducta' => null,
        ]);

        $response->assertCreated();
        $notaId = $response->json('id');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'nota.registrada',
            'causer_id' => $user->id,
            'subject_type' => Nota::class,
            'subject_id' => $notaId,
        ]);
    }

    public function test_registrar_asistencia_registra_actividad(): void
    {
        $this->seedPermissions(['registrar_datos_academicos']);
        $user = $this->userWith(['registrar_datos_academicos']);
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        $response = $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/asistencias", [
            'semana_inicio' => '2026-04-07',
            'estado' => 'presente',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);

        $response->assertCreated();
        $asistenciaId = $response->json('id');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'asistencia.registrada',
            'causer_id' => $user->id,
            'subject_type' => Asistencia::class,
            'subject_id' => $asistenciaId,
        ]);
    }

    public function test_guardar_variables_socioeconomicas_registra_actividad(): void
    {
        $this->seedPermissions(['registrar_datos_academicos']);
        $user = $this->userWith(['registrar_datos_academicos']);
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        $response = $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/variables-socioeconomicas", [
            'composicion_familiar' => 'nuclear',
            'nivel_socioeconomico' => 'medio',
            'acceso_internet' => true,
            'distancia_colegio_km' => 1.5,
            'anio_escolar' => '2026',
        ]);

        $response->assertOk();
        $varId = $response->json('id');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'variables_socioeconomicas.guardadas',
            'causer_id' => $user->id,
            'subject_type' => VariableSocioeconomica::class,
            'subject_id' => $varId,
        ]);
    }

    public function test_procesar_riesgo_registra_actividad(): void
    {
        $this->seedPermissions(['procesar_riesgo']);
        $user = $this->userWith(['procesar_riesgo']);
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        $this->crearEvalBimResultadoRiesgo($estudiante, 12.0);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);
        $this->crearVariableSocioeconomicaRiesgo($estudiante);

        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(fn () => Http::response(['indice_riesgo' => 0.5], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo", [
            'bimestre' => '2',
        ])->assertCreated();

        $indice = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->latest('id')->first();
        $this->assertNotNull($indice);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'riesgo.procesado',
            'causer_id' => $user->id,
            'subject_type' => IndiceRiesgo::class,
            'subject_id' => $indice->id,
        ]);
    }

    public function test_generar_alerta_por_riesgo_alto_registra_actividad(): void
    {
        $this->seedPermissions(['procesar_riesgo']);
        $user = $this->userWith(['procesar_riesgo']);
        $estudiante = Estudiante::factory()->create(['anio_escolar' => '2026']);

        $this->crearEvalBimResultadoRiesgo($estudiante, 8.0);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);
        $this->crearVariableSocioeconomicaRiesgo($estudiante, [
            'nivel_socioeconomico' => 'bajo',
            'acceso_internet' => false,
            'distancia_colegio_km' => 5,
        ]);

        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(fn () => Http::response(['indice_riesgo' => 0.88], 200));

        $this->actingAs($user)->postJson("/api/estudiantes/{$estudiante->id}/procesar-riesgo")
            ->assertCreated();

        $alerta = Alerta::query()->where('estudiante_id', $estudiante->id)->first();
        $this->assertNotNull($alerta);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'alerta.generada',
            'causer_id' => $user->id,
            'subject_type' => Alerta::class,
            'subject_id' => $alerta->id,
        ]);
    }

    public function test_registrar_intervencion_registra_actividad(): void
    {
        $this->seedPermissions(['ver_alertas', 'registrar_intervencion']);
        $user = $this->userWith(['ver_alertas', 'registrar_intervencion']);
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
            'descripcion' => 'Entrevista.',
            'fecha' => '2026-05-01',
        ]);

        $response->assertCreated();
        $intervencionId = $response->json('id');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'intervencion.registrada',
            'causer_id' => $user->id,
            'subject_type' => Intervencion::class,
            'subject_id' => $intervencionId,
        ]);
    }

    public function test_cerrar_alerta_registra_actividad(): void
    {
        $this->seedPermissions(['ver_alertas', 'registrar_intervencion']);
        $user = $this->userWith(['ver_alertas', 'registrar_intervencion']);
        $estudiante = Estudiante::factory()->create();

        $indice = IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.8,
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
            'tipo' => 'academica',
            'descripcion' => 'Contacto',
            'fecha' => '2026-05-02',
        ]);

        $this->actingAs($user)->postJson("/api/alertas/{$alerta->id}/cerrar", [
            'resultado_cierre' => 'Caso cerrado con acuerdos.',
        ])->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'alerta.cerrada',
            'causer_id' => $user->id,
            'subject_type' => Alerta::class,
            'subject_id' => $alerta->id,
        ]);
    }

    public function test_exportar_pdf_dashboard_registra_actividad(): void
    {
        $this->seedPermissions(['ver_dashboard']);
        $user = $this->userWith(['ver_dashboard']);
        Estudiante::factory()->create();

        $this->actingAs($user)->get('/api/dashboard/export')->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'dashboard.pdf_exportado',
            'causer_id' => $user->id,
            'causer_type' => User::class,
            'subject_id' => null,
            'subject_type' => null,
        ]);
    }
}
