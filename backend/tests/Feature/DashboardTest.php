<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function permisoDashboard(): void
    {
        Permission::firstOrCreate([
            'name' => 'ver_dashboard',
            'guard_name' => 'web',
        ]);
    }

    private function usuarioConPermisoDashboard(): User
    {
        $this->permisoDashboard();
        $user = User::factory()->create();
        $user->givePermissionTo('ver_dashboard');

        return $user;
    }

    private function usuarioSinPermisoDashboard(): User
    {
        $this->permisoDashboard();

        return User::factory()->create();
    }

    public function test_visitante_sin_autenticacion_recibe_401(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_usuario_autenticado_sin_permiso_recibe_403(): void
    {
        $response = $this->actingAs($this->usuarioSinPermisoDashboard())
            ->getJson('/api/dashboard');

        $response->assertForbidden();
    }

    public function test_usuario_con_permiso_recibe_200_y_estructura_json(): void
    {
        $response = $this->actingAs($this->usuarioConPermisoDashboard())
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'total_estudiantes',
                'riesgos_por_nivel' => [
                    'alto',
                    'medio',
                    'bajo',
                ],
                'alertas_por_estado' => [
                    'pendiente',
                    'en_atencion',
                    'cerrada',
                ],
                'ultimos_riesgos',
            ]);
    }

    public function test_estado_vacio_devuelve_ceros_y_array_sin_error(): void
    {
        $response = $this->actingAs($this->usuarioConPermisoDashboard())
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('total_estudiantes', 0)
            ->assertJsonPath('riesgos_por_nivel.alto', 0)
            ->assertJsonPath('riesgos_por_nivel.medio', 0)
            ->assertJsonPath('riesgos_por_nivel.bajo', 0)
            ->assertJsonPath('alertas_por_estado.pendiente', 0)
            ->assertJsonPath('alertas_por_estado.en_atencion', 0)
            ->assertJsonPath('alertas_por_estado.cerrada', 0)
            ->assertJsonPath('ultimos_riesgos', []);
    }

    public function test_conteo_riesgos_usa_solo_ultimo_indice_por_estudiante(): void
    {
        $this->permisoDashboard();

        $e1 = Estudiante::factory()->create();
        IndiceRiesgo::query()->create([
            'estudiante_id' => $e1->id,
            'indice' => 0.2000,
            'nivel' => 'Bajo',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);
        IndiceRiesgo::query()->create([
            'estudiante_id' => $e1->id,
            'indice' => 0.8500,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '2',
        ]);

        $e2 = Estudiante::factory()->create();
        IndiceRiesgo::query()->create([
            'estudiante_id' => $e2->id,
            'indice' => 0.5000,
            'nivel' => 'Medio',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);

        Estudiante::factory()->create();

        $response = $this->actingAs($this->usuarioConPermisoDashboard())
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('total_estudiantes', 3)
            ->assertJsonPath('riesgos_por_nivel.alto', 1)
            ->assertJsonPath('riesgos_por_nivel.medio', 1)
            ->assertJsonPath('riesgos_por_nivel.bajo', 0);
    }

    public function test_conteos_de_alertas_por_estado(): void
    {
        $this->permisoDashboard();

        $e1 = Estudiante::factory()->create();
        $ir1 = IndiceRiesgo::query()->create([
            'estudiante_id' => $e1->id,
            'indice' => 0.8000,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);
        Alerta::query()->create([
            'estudiante_id' => $e1->id,
            'indice_riesgo_id' => $ir1->id,
            'estado' => 'pendiente',
            'recomendacion' => 'Seguimiento',
        ]);

        $e2 = Estudiante::factory()->create();
        $ir2 = IndiceRiesgo::query()->create([
            'estudiante_id' => $e2->id,
            'indice' => 0.7500,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);
        Alerta::query()->create([
            'estudiante_id' => $e2->id,
            'indice_riesgo_id' => $ir2->id,
            'estado' => 'en_atencion',
            'recomendacion' => 'Seguimiento',
        ]);

        $e3 = Estudiante::factory()->create();
        $ir3 = IndiceRiesgo::query()->create([
            'estudiante_id' => $e3->id,
            'indice' => 0.7200,
            'nivel' => 'Alto',
            'anio_escolar' => '2026',
            'bimestre' => '1',
        ]);
        $user = User::factory()->create();
        Alerta::query()->create([
            'estudiante_id' => $e3->id,
            'indice_riesgo_id' => $ir3->id,
            'estado' => 'cerrada',
            'recomendacion' => 'Seguimiento',
            'resultado_cierre' => 'Caso cerrado',
            'cerrada_por' => $user->id,
            'fecha_cierre' => now(),
        ]);

        $response = $this->actingAs($this->usuarioConPermisoDashboard())
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('alertas_por_estado.pendiente', 1)
            ->assertJsonPath('alertas_por_estado.en_atencion', 1)
            ->assertJsonPath('alertas_por_estado.cerrada', 1);
    }
}
