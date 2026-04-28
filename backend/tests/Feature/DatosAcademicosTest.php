<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DatosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    private function permisoDatosAcademicos(): void
    {
        Permission::firstOrCreate([
            'name' => 'registrar_datos_academicos',
            'guard_name' => 'web',
        ]);
    }

    private function usuarioPermitido(): User
    {
        $this->permisoDatosAcademicos();
        $user = User::factory()->create();

        $user->givePermissionTo('registrar_datos_academicos');

        return $user;
    }

    private function usuarioSinPermiso(): User
    {
        $this->permisoDatosAcademicos();

        return User::factory()->create();
    }

    private function estudianteBase(): Estudiante
    {
        return Estudiante::factory()->create([
            'codigo' => 'EST-X-001',
            'anio_escolar' => '2026',
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
        ]);
    }

    public function test_usuario_con_permiso_registra_nota_valida(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '1',
                'curso' => 'Matemática',
                'nota' => 14.5,
                'nota_conducta' => 17,
            ]
        );

        $response->assertCreated()->assertJsonPath('curso', 'Matemática');

        $this->assertDatabaseHas('notas', [
            'estudiante_id' => $estudiante->id,
            'bimestre' => '1',
            'curso' => 'Matemática',
        ]);
    }

    public function test_usuario_con_permiso_lista_notas_del_estudiante(): void
    {
        $estudiante = $this->estudianteBase();

        $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '2',
                'curso' => 'Comunicación',
                'nota' => 15,
                'nota_conducta' => null,
            ]
        )->assertSuccessful();

        $response = $this->actingAs($this->usuarioPermitido())->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_rechazo_nota_invalida_fuera_de_rango(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '1',
                'curso' => 'Historia',
                'nota' => 21,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['nota']);
    }

    public function test_usuario_con_permiso_registra_asistencia_valida(): void
    {
        $estudiante = $this->estudianteBase();
        $user = $this->usuarioPermitido();

        $response = $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/asistencias",
            [
                'semana_inicio' => '2026-04-14',
                'estado' => 'presente',
                'anio_escolar' => '2026',
                'bimestre' => '1',
            ]
        );

        $response->assertCreated()
            ->assertJsonFragment([
                'estado' => 'presente',
                'registrado_por' => $user->id,
            ]);

        $this->assertDatabaseHas('asistencias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'presente',
            'registrado_por' => $user->id,
        ]);
    }

    public function test_usuario_con_permiso_lista_asistencias(): void
    {
        $estudiante = $this->estudianteBase();
        $actor = $this->usuarioPermitido();

        $this->actingAs($actor)->postJson(
            "/api/estudiantes/{$estudiante->id}/asistencias",
            [
                'semana_inicio' => '2026-04-07',
                'estado' => 'tardanza',
                'anio_escolar' => '2026',
                'bimestre' => '1',
            ]
        )->assertSuccessful();

        $response = $this->actingAs($actor)->getJson(
            "/api/estudiantes/{$estudiante->id}/asistencias"
        );

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_usuario_con_permiso_registra_variables_socioeconomicas(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/variables-socioeconomicas",
            [
                'composicion_familiar' => 'nuclear',
                'nivel_socioeconomico' => 'medio',
                'acceso_internet' => true,
                'distancia_colegio_km' => 2.5,
                'anio_escolar' => '2026',
            ]
        );

        $response->assertSuccessful()->assertJsonFragment([
            'composicion_familiar' => 'nuclear',
            'anio_escolar' => '2026',
        ]);

        $this->assertDatabaseHas('variables_socioeconomicas', [
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => '2026',
            'composicion_familiar' => 'nuclear',
        ]);
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioSinPermiso())->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertForbidden();
    }

    public function test_visitante_sin_sesion_recibe_no_autorizada(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertUnauthorized();
    }
}
