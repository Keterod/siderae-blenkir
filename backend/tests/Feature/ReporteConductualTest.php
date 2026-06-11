<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\ReporteConductual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReporteConductualTest extends TestCase
{
    use RefreshDatabase;

    private function crearPermisosConductuales(): void
    {
        foreach (['ver_reportes_conductuales', 'registrar_reportes_conductuales'] as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function estudiantePayload(array $override = []): array
    {
        return array_merge([
            'codigo' => 'EST-RF04-001',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ], $override);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportePayload(array $override = []): array
    {
        return array_merge([
            'fecha' => '2026-06-10',
            'tipo_conducta' => 'Agresión verbal',
            'nivel_gravedad' => 'moderado',
            'descripcion' => 'Incidente en aula durante clase de matemáticas.',
            'accion_inmediata' => 'Conversación con el estudiante.',
        ], $override);
    }

    private function usuarioConPermisoVer(): User
    {
        $this->crearPermisosConductuales();
        $user = User::factory()->create();
        $user->givePermissionTo('ver_reportes_conductuales');

        return $user;
    }

    private function usuarioConPermisoRegistrar(): User
    {
        $this->crearPermisosConductuales();
        $user = User::factory()->create();
        $user->givePermissionTo(['ver_reportes_conductuales', 'registrar_reportes_conductuales']);

        return $user;
    }

    private function usuarioDirectivoSoloLectura(): User
    {
        $this->crearPermisosConductuales();
        $user = User::factory()->create();
        $user->givePermissionTo('ver_reportes_conductuales');

        return $user;
    }

    public function test_usuario_sin_sesion_recibe_401_al_listar_o_crear(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());

        $this->getJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales")
            ->assertUnauthorized();

        $this->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", $this->reportePayload())
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_registro_recibe_403_al_crear(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $user = $this->usuarioConPermisoVer();

        $this->actingAs($user)
            ->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", $this->reportePayload())
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_registro_puede_crear_reporte(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $user = $this->usuarioConPermisoRegistrar();

        $response = $this->actingAs($user)
            ->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", $this->reportePayload());

        $response->assertCreated()
            ->assertJsonPath('estudiante_id', $estudiante->id)
            ->assertJsonPath('registrado_por.id', $user->id)
            ->assertJsonPath('estado', 'activo')
            ->assertJsonPath('nivel_gravedad', 'moderado');

        $this->assertDatabaseHas('reportes_conductuales', [
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $user->id,
            'estado' => 'activo',
            'tipo_conducta' => 'Agresión verbal',
        ]);
    }

    public function test_validacion_falla_con_422_si_faltan_campos_requeridos(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $user = $this->usuarioConPermisoRegistrar();

        $this->actingAs($user)
            ->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", [
                'fecha' => '2026-06-10',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tipo_conducta', 'nivel_gravedad', 'descripcion']);
    }

    public function test_usuario_con_permiso_ver_lista_solo_reportes_activos(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $registrador = $this->usuarioConPermisoRegistrar();
        $lector = $this->usuarioConPermisoVer();

        $activo = ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $registrador->id,
            'fecha' => '2026-06-01',
            'tipo_conducta' => 'Falta de respeto',
            'descripcion' => 'Activo',
            'nivel_gravedad' => 'leve',
            'estado' => 'activo',
        ]);

        ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $registrador->id,
            'fecha' => '2026-05-01',
            'tipo_conducta' => 'Anulado',
            'descripcion' => 'Anulado',
            'nivel_gravedad' => 'grave',
            'estado' => 'anulado',
        ]);

        $response = $this->actingAs($lector)
            ->getJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $activo->id)
            ->assertJsonPath('0.estado', 'activo');
    }

    public function test_anular_cambia_estado_sin_borrar_fisicamente(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $user = $this->usuarioConPermisoRegistrar();

        $reporte = ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $user->id,
            'fecha' => '2026-06-05',
            'tipo_conducta' => 'Conducta disruptiva',
            'descripcion' => 'Por anular',
            'nivel_gravedad' => 'moderado',
            'estado' => 'activo',
        ]);

        $this->actingAs($user)
            ->patchJson("/api/reportes-conductuales/{$reporte->id}/anular")
            ->assertOk()
            ->assertJsonPath('estado', 'anulado');

        $this->assertDatabaseHas('reportes_conductuales', [
            'id' => $reporte->id,
            'estado' => 'anulado',
        ]);
    }

    public function test_directivo_con_solo_lectura_no_puede_crear_ni_anular(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload());
        $directivo = $this->usuarioDirectivoSoloLectura();
        $registrador = $this->usuarioConPermisoRegistrar();

        $reporte = ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $registrador->id,
            'fecha' => '2026-06-05',
            'tipo_conducta' => 'Conducta disruptiva',
            'descripcion' => 'Existente',
            'nivel_gravedad' => 'leve',
            'estado' => 'activo',
        ]);

        $this->actingAs($directivo)
            ->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", $this->reportePayload())
            ->assertForbidden();

        $this->actingAs($directivo)
            ->patchJson("/api/reportes-conductuales/{$reporte->id}/anular")
            ->assertForbidden();
    }

    public function test_estudiante_auquimarca_rechazado_en_listado_y_creacion(): void
    {
        $estudiante = Estudiante::factory()->create($this->estudiantePayload([
            'codigo' => 'EST-AUQ-001',
            'sede' => 'auquimarca',
        ]));
        $user = $this->usuarioConPermisoRegistrar();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales")
            ->assertForbidden()
            ->assertJsonPath('message', 'Estudiante fuera de la sede operativa V1 (Chilca).');

        $this->actingAs($user)
            ->postJson("/api/estudiantes/{$estudiante->id}/reportes-conductuales", $this->reportePayload())
            ->assertForbidden();
    }
}
