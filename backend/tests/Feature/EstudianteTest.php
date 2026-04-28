<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EstudianteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private static function estudiantePayloadValido(array $override = []): array
    {
        return array_merge([
            'codigo' => 'EST202601',
            'nombres' => 'María',
            'apellidos' => 'López',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '1°',
            'seccion' => 'B',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ], $override);
    }

    protected function crearPermisoGestionEstudiantes(): void
    {
        Permission::firstOrCreate([
            'name' => 'gestionar_estudiantes',
            'guard_name' => 'web',
        ]);
    }

    protected function usuarioConPermiso(): User
    {
        $this->crearPermisoGestionEstudiantes();

        $user = User::factory()->create();
        $user->givePermissionTo('gestionar_estudiantes');

        return $user;
    }

    protected function usuarioSinPermiso(): User
    {
        $this->crearPermisoGestionEstudiantes();

        return User::factory()->create();
    }

    public function test_usuario_con_permiso_puede_listar_estudiantes(): void
    {
        Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'EST001',
        ]));

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson('/api/estudiantes');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_usuario_sin_permiso_recibe_403_al_listar(): void
    {
        $response = $this->actingAs($this->usuarioSinPermiso())
            ->getJson('/api/estudiantes');

        $response->assertForbidden();
    }

    public function test_visitante_sin_sesion_recibe_respuesta_no_autorizada_al_listar(): void
    {
        $response = $this->getJson('/api/estudiantes');

        $response->assertUnauthorized();
    }

    public function test_crear_estudiante_valido(): void
    {
        $payload = self::estudiantePayloadValido(['codigo' => 'CREAR01']);

        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            $payload
        );

        $response->assertCreated()
            ->assertJsonFragment(['codigo' => $payload['codigo']]);

        $this->assertDatabaseHas('estudiantes', [
            'codigo' => $payload['codigo'],
            'grado' => $payload['grado'],
            'seccion' => $payload['seccion'],
            'nivel' => $payload['nivel'],
            'sede' => $payload['sede'],
            'anio_escolar' => $payload['anio_escolar'],
        ]);
    }

    public function test_rechazar_estudiante_con_codigo_duplicado(): void
    {
        Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'DUP999',
        ]));

        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            self::estudiantePayloadValido([
                'codigo' => 'DUP999',
                'apellidos' => 'Duplicado',
            ])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['codigo']);
    }

    public function test_rechazar_datos_invalidos_campos_obligatorios(): void
    {
        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo', 'nombres', 'apellidos', 'grado', 'seccion', 'nivel', 'sede', 'anio_escolar']);
    }

    public function test_actualizar_estudiante_correctamente(): void
    {
        $estudiante = Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'EDIT01',
            'nombres' => 'NombreAntiguo',
        ]));

        $response = $this->actingAs($this->usuarioConPermiso())->putJson(
            "/api/estudiantes/{$estudiante->id}",
            array_merge(self::estudiantePayloadValido([
                'codigo' => 'EDIT01',
            ]), [
                'nombres' => 'NombreNuevo',
            ])
        );

        $response->assertOk()->assertJsonFragment(['nombres' => 'NombreNuevo']);

        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'codigo' => 'EDIT01',
            'nombres' => 'NombreNuevo',
        ]);
    }
}
