<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([\Database\Seeders\RolesSeeder::class, \Database\Seeders\PermissionsSeeder::class]);
    }

    private function administrador(): User
    {
        $user = User::factory()->create();

        $user->assignRole('administrador');

        return $user;
    }

    private function payloadValida(): array
    {
        return [
            'nombre' => 'Matemática',
            'nivel' => 'primaria',
            'grado' => '1°',
            'anio_escolar' => '2026',
            'sede' => 'chilca',
        ];
    }

    public function test_admin_puede_listar_materias(): void
    {
        Materia::query()->create($this->payloadValida());

        $response = $this->actingAs($this->administrador())->getJson('/api/materias');

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_admin_puede_filtrar_por_campos_query(): void
    {
        Materia::query()->create($this->payloadValida());

        Materia::query()->create([
            'nombre' => 'Comunicación',
            'nivel' => 'secundaria',
            'grado' => '5°',
            'anio_escolar' => '2026',
            'sede' => 'chilca',
        ]);

        $response = $this->actingAs($this->administrador())->getJson('/api/materias?' . http_build_query([
            'nivel' => 'primaria',
            'grado' => '1°',
            'anio_escolar' => '2026',
            'sede' => 'chilca',
            'activo' => true,
        ]));

        $response->assertSuccessful()->assertJsonCount(1)->assertJsonPath('0.nombre', 'Matemática');
    }

    public function test_admin_crea_materia_valida(): void
    {
        $response = $this->actingAs($this->administrador())->postJson('/api/materias', $this->payloadValida());

        $response->assertCreated()->assertJsonFragment(['nombre' => 'Matemática']);

        $this->assertDatabaseHas('materias', [
            'nombre' => 'Matemática',
            'grado' => '1°',
            'activo' => true,
        ]);
    }

    public function test_admin_no_duplica_combinacion_unica(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)->postJson('/api/materias', $this->payloadValida())->assertCreated();

        $response = $this->actingAs($admin)->postJson('/api/materias', $this->payloadValida());

        $response->assertStatus(422);
    }

    public function test_admin_actualiza_materia(): void
    {
        $admin = $this->administrador();

        $creado = $this->actingAs($admin)->postJson('/api/materias', $this->payloadValida())->assertCreated()->json();

        $id = $creado['id'];

        $response = $this->actingAs($admin)->patchJson("/api/materias/{$id}", [
            'nombre' => 'Matemática aplicada',
        ]);

        $response->assertSuccessful()->assertJsonPath('nombre', 'Matemática aplicada');

        $this->assertDatabaseHas('materias', [
            'id' => $id,
            'nombre' => 'Matemática aplicada',
        ]);
    }

    public function test_admin_desactiva_materia(): void
    {
        $admin = $this->administrador();
        $creado = $this->actingAs($admin)->postJson('/api/materias', $this->payloadValida())->assertCreated()->json();
        $id = $creado['id'];

        $response = $this->actingAs($admin)->patchJson("/api/materias/{$id}/desactivar");

        $response->assertSuccessful()->assertJsonPath('activo', false);
    }

    public function test_admin_reactiva_materia(): void
    {
        $admin = $this->administrador();
        $mat = Materia::query()->create(array_merge($this->payloadValida(), ['activo' => false]));

        $response = $this->actingAs($admin)->patchJson("/api/materias/{$mat->id}/activar");

        $response->assertSuccessful()->assertJsonPath('activo', true);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'materia.activada',
            'subject_type' => Materia::class,
            'subject_id' => $mat->id,
            'causer_id' => $admin->id,
        ]);
    }

    public function test_usuario_sin_permiso_lectura_materias_recibe_403(): void
    {
        Materia::query()->create($this->payloadValida());

        $directivo = User::factory()->create();
        $directivo->assignRole('directivo');

        $response = $this->actingAs($directivo)->getJson('/api/materias');

        $response->assertForbidden();
    }

    public function test_docente_puede_listar_materias_en_lectura(): void
    {
        Materia::query()->create($this->payloadValida());

        $docente = User::factory()->create();
        $docente->assignRole('docente');

        $response = $this->actingAs($docente)->getJson('/api/materias');

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_visitante_lista_sin_auth(): void
    {
        $response = $this->getJson('/api/materias');

        $response->assertUnauthorized();
    }

    public function test_audit_creacion_actualizacion_esta_en_activity_log(): void
    {
        $admin = $this->administrador();

        $creado = $this->actingAs($admin)->postJson('/api/materias', $this->payloadValida())->assertCreated()->json();
        $id = $creado['id'];

        $this->actingAs($admin)->patchJson("/api/materias/{$id}", [
            'grado' => '2°',
        ])->assertSuccessful();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'materia.creada',
            'subject_type' => Materia::class,
            'subject_id' => $id,
            'causer_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'materia.actualizada',
            'subject_type' => Materia::class,
            'subject_id' => $id,
        ]);
    }

    public function test_audit_desactivacion_log(): void
    {
        $admin = $this->administrador();
        $mat = Materia::query()->create($this->payloadValida());

        $this->actingAs($admin)->patchJson("/api/materias/{$mat->id}/desactivar")->assertSuccessful();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'materia.desactivada',
            'subject_type' => Materia::class,
            'subject_id' => $mat->id,
        ]);
    }
}
