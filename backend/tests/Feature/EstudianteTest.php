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

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.codigo', 'EST001');
    }

    public function test_listado_estudiantes_permite_busqueda_por_texto_q(): void
    {
        Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'EST-ABC-001',
            'nombres' => 'Mariana',
            'apellidos' => 'Quispe',
        ]));
        Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'EST-XYZ-009',
            'nombres' => 'Carlos',
            'apellidos' => 'Ramos',
        ]));

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson('/api/estudiantes?q=quisp');

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.codigo', 'EST-ABC-001');
    }

    public function test_listado_estudiantes_soporta_paginacion(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Estudiante::factory()->create(self::estudiantePayloadValido([
                'codigo' => sprintf('PAG%03d', $i),
            ]));
        }

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson('/api/estudiantes?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 30)
            ->assertJsonPath('last_page', 3)
            ->assertJsonCount(10, 'data');
    }

    public function test_listado_estudiantes_all_devuelve_array_plano(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Estudiante::factory()->create(self::estudiantePayloadValido([
                'codigo' => sprintf('ALL%03d', $i),
            ]));
        }

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson('/api/estudiantes?all=1');

        $response->assertOk()->assertJsonCount(3);
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

    public function test_usuario_solo_registrar_datos_academicos_puede_listar_estudiantes(): void
    {
        Permission::firstOrCreate([
            'name' => 'registrar_datos_academicos',
            'guard_name' => 'web',
        ]);
        $this->crearPermisoGestionEstudiantes();

        $user = User::factory()->create();
        $user->givePermissionTo('registrar_datos_academicos');

        Estudiante::factory()->create(self::estudiantePayloadValido());

        $response = $this->actingAs($user)->getJson('/api/estudiantes');

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_usuario_sin_gestionar_estudiantes_recibe_403_al_crear(): void
    {
        Permission::firstOrCreate([
            'name' => 'registrar_datos_academicos',
            'guard_name' => 'web',
        ]);
        $this->crearPermisoGestionEstudiantes();

        $user = User::factory()->create();
        $user->givePermissionTo('registrar_datos_academicos');

        $response = $this->actingAs($user)->postJson(
            '/api/estudiantes',
            self::estudiantePayloadValido(['codigo' => 'NO-GEST-01'])
        );

        $response->assertForbidden();
    }

    public function test_usuario_sin_gestionar_estudiantes_recibe_403_al_editar(): void
    {
        Permission::firstOrCreate([
            'name' => 'registrar_datos_academicos',
            'guard_name' => 'web',
        ]);
        $this->crearPermisoGestionEstudiantes();

        $user = User::factory()->create();
        $user->givePermissionTo('registrar_datos_academicos');

        $estudiante = Estudiante::factory()->create(self::estudiantePayloadValido([
            'codigo' => 'EDIT403A',
        ]));

        $response = $this->actingAs($user)->putJson(
            "/api/estudiantes/{$estudiante->id}",
            array_merge(self::estudiantePayloadValido([
                'codigo' => 'EDIT403A',
            ]), [
                'nombres' => 'OtroNombre',
            ])
        );

        $response->assertForbidden();
    }
}
