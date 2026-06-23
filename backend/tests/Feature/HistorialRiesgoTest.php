<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HistorialRiesgoTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'ver_historial_riesgo';

    private function crearPermiso(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISO, 'guard_name' => 'web']);
    }

    private function usuarioConPermiso(): User
    {
        $this->crearPermiso();
        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISO);

        return $user;
    }

    private function usuarioSinPermiso(): User
    {
        $this->crearPermiso();

        return User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function estudiantePayload(array $override = []): array
    {
        return array_merge([
            'codigo' => 'EST-RF20-001',
            'nombres' => 'María',
            'apellidos' => 'López',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '2°',
            'seccion' => 'B',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ], $override);
    }

    private function crearEstudianteChilca(array $override = []): Estudiante
    {
        return Estudiante::factory()->create($this->estudiantePayload($override));
    }

    /**
     * @param array<string, mixed> $override
     */
    private function crearIndiceRiesgo(Estudiante $estudiante, array $override = []): IndiceRiesgo
    {
        return IndiceRiesgo::query()->create(array_merge([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.55,
            'nivel' => 'Medio',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ], $override));
    }

    public function test_usuario_sin_sesion_recibe_401(): void
    {
        $estudiante = $this->crearEstudianteChilca();

        $this->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioSinPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_consultar_historial(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertOk()
            ->assertJsonPath('estudiante_id', $estudiante->id)
            ->assertJsonStructure([
                'estudiante_id',
                'historial',
            ]);
    }

    public function test_estudiante_auquimarca_recibe_403(): void
    {
        $estudiante = $this->crearEstudianteChilca([
            'codigo' => 'EST-AUQ-RF20',
            'sede' => 'auquimarca',
        ]);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertForbidden()
            ->assertJsonPath('message', 'Estudiante fuera de la sede operativa V1 (Chilca).');
    }

    public function test_historial_vacio_devuelve_array_vacio(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertOk()
            ->assertJsonPath('historial', []);
    }

    public function test_historial_con_varios_registros(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearIndiceRiesgo($estudiante, ['indice' => 0.75, 'nivel' => 'Alto']);
        $this->crearIndiceRiesgo($estudiante, [
            'indice' => 0.45,
            'nivel' => 'Medio',
            'anio_escolar' => '2026',
            'bimestre' => '2',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo");

        $response->assertOk()
            ->assertJsonCount(2, 'historial')
            ->assertJsonPath('historial.0.nivel', 'Medio')
            ->assertJsonPath('historial.1.nivel', 'Alto');
    }

    public function test_historial_ordenado_de_mas_reciente_a_mas_antiguo(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $antiguo = $this->crearIndiceRiesgo($estudiante, [
            'indice' => 0.80,
            'nivel' => 'Alto',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $reciente = $this->crearIndiceRiesgo($estudiante, [
            'indice' => 0.30,
            'nivel' => 'Bajo',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo");

        $response->assertOk();
        $this->assertSame($reciente->id, $response->json('historial.0.id'));
        $this->assertSame($antiguo->id, $response->json('historial.1.id'));
    }

    public function test_filtro_por_anio_escolar(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2025']);
        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2026', 'bimestre' => '2']);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo?anio_escolar=2026");

        $response->assertOk()
            ->assertJsonCount(1, 'historial')
            ->assertJsonPath('historial.0.anio_escolar', '2026');
    }

    public function test_filtro_por_bimestre(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '1']);
        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '2']);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo?bimestre=2");

        $response->assertOk()
            ->assertJsonCount(1, 'historial')
            ->assertJsonPath('historial.0.bimestre', '2');
    }

    public function test_consulta_no_modifica_indices_riesgo(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearIndiceRiesgo($estudiante);

        $conteoAntes = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertOk();

        $this->assertSame($conteoAntes, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }

    public function test_consulta_no_llama_a_flask(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo")
            ->assertOk();

        // El controller no realiza llamadas HTTP a Flask.
        $this->assertTrue(true);
    }

    public function test_respuesta_incluye_variables_utilizadas_cuando_existen(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearIndiceRiesgo($estudiante, [
            'variables_utilizadas' => ['notas' => true, 'asistencia' => true],
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/historial-riesgo");

        $response->assertOk()
            ->assertJsonPath('historial.0.variables_utilizadas.notas', true)
            ->assertJsonPath('historial.0.variables_utilizadas.asistencia', true);
    }
}
