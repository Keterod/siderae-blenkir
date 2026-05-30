<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GestionUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(PermissionsSeeder::class);
    }

    protected function usuarioAdministrador(): User
    {
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole('administrador');

        return $user;
    }

    protected function usuarioDocenteSinGestion(): User
    {
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole('docente');

        return $user;
    }

    #[Test]
    public function admin_crea_usuario_con_rol_docente(): void
    {
        $admin = $this->usuarioAdministrador();

        $response = $this->actingAs($admin)->postJson('/api/usuarios', [
            'name' => 'Nuevo Docente',
            'email' => 'nuevo.docente@siderae.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'rol' => 'docente',
        ]);

        $response->assertCreated()
            ->assertJsonPath('email', 'nuevo.docente@siderae.test')
            ->assertJsonPath('rol', 'docente')
            ->assertJsonPath('activo', true);

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo.docente@siderae.test',
            'activo' => true,
        ]);

        $creado = User::query()->where('email', 'nuevo.docente@siderae.test')->first();
        $this->assertTrue($creado->hasRole('docente'));

        $this->assertDatabaseHas('activity_log', [
            'description' => 'usuario.creado',
        ]);
    }

    #[Test]
    public function usuario_sin_permiso_recibe_403(): void
    {
        $docente = $this->usuarioDocenteSinGestion();

        $this->actingAs($docente)
            ->getJson('/api/usuarios')
            ->assertForbidden();
    }

    #[Test]
    public function listado_filtra_por_rol_y_activo(): void
    {
        $admin = $this->usuarioAdministrador();

        $docenteActivo = User::factory()->create(['name' => 'Docente Activo', 'activo' => true]);
        $docenteActivo->assignRole('docente');

        $docenteInactivo = User::factory()->create(['name' => 'Docente Inactivo', 'activo' => false]);
        $docenteInactivo->assignRole('docente');

        $coordinador = User::factory()->create(['name' => 'Coord Demo', 'activo' => true]);
        $coordinador->assignRole('coordinador_academico');

        $this->actingAs($admin)
            ->getJson('/api/usuarios?rol=docente&incluir_inactivos=1')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Docente Activo'])
            ->assertJsonFragment(['name' => 'Docente Inactivo'])
            ->assertJsonMissing(['name' => 'Coord Demo']);

        $this->actingAs($admin)
            ->getJson('/api/usuarios?rol=docente&activo=1')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Docente Activo'])
            ->assertJsonMissing(['name' => 'Docente Inactivo']);
    }

    #[Test]
    public function admin_desactiva_usuario(): void
    {
        $admin = $this->usuarioAdministrador();
        $otroAdmin = User::factory()->create(['activo' => true]);
        $otroAdmin->assignRole('administrador');

        $objetivo = User::factory()->create(['activo' => true]);
        $objetivo->assignRole('docente');

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$objetivo->id}/desactivar")
            ->assertOk()
            ->assertJsonPath('activo', false);

        $this->assertFalse($objetivo->fresh()->activo);
    }

    #[Test]
    public function usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $usuario = User::factory()->create([
            'email' => 'inactivo@siderae.test',
            'activo' => false,
        ]);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function usuario_desactivado_con_sesion_previa_queda_bloqueado_por_middleware(): void
    {
        $usuario = User::factory()->create(['activo' => true]);
        $usuario->givePermissionTo('ver_dashboard');

        $this->actingAs($usuario)
            ->getJson('/api/dashboard')
            ->assertOk();

        $usuario->activo = false;
        $usuario->save();

        $this->actingAs($usuario)
            ->getJson('/api/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Su cuenta está desactivada. Contacte al administrador del sistema.');
    }

    #[Test]
    public function no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = $this->usuarioAdministrador();

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$admin->id}/desactivar")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['usuario']);
    }

    #[Test]
    public function no_puede_desactivar_ultimo_administrador_activo(): void
    {
        $gestor = User::factory()->create(['activo' => true]);
        $gestor->assignRole('coordinador_academico');
        $gestor->givePermissionTo('gestionar_usuarios');

        $unicoAdmin = User::factory()->create(['activo' => true]);
        $unicoAdmin->assignRole('administrador');

        $this->actingAs($gestor)
            ->patchJson("/api/usuarios/{$unicoAdmin->id}/desactivar")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['usuario']);
    }

    #[Test]
    public function no_puede_cambiar_rol_del_ultimo_administrador_activo(): void
    {
        $admin = User::factory()->create(['activo' => true]);
        $admin->assignRole('administrador');

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$admin->id}", ['rol' => 'docente'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rol']);
    }

    #[Test]
    public function admin_no_puede_quitarse_rol_administrador_si_es_ultimo_activo(): void
    {
        $admin = User::factory()->create(['activo' => true]);
        $admin->assignRole('administrador');

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$admin->id}", ['rol' => 'coordinador_academico'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rol']);
    }

    #[Test]
    public function restablecer_contrasena_permite_login_con_nueva_clave(): void
    {
        $admin = $this->usuarioAdministrador();
        $objetivo = User::factory()->create([
            'email' => 'reset-login@siderae.test',
            'activo' => true,
        ]);
        $objetivo->assignRole('docente');

        $this->actingAs($admin)
            ->postJson("/api/usuarios/{$objetivo->id}/restablecer-contrasena", [
                'password' => 'NuevaClave1!',
                'password_confirmation' => 'NuevaClave1!',
            ])
            ->assertOk();

        $objetivo->refresh();
        $this->assertTrue(Hash::check('NuevaClave1!', $objetivo->password));

        $this->post('/logout')->assertNoContent();

        $this->assertTrue(Auth::guard('web')->attempt([
            'email' => $objetivo->email,
            'password' => 'NuevaClave1!',
        ]));
    }

    #[Test]
    public function docentes_curriculares_excluyen_docentes_inactivos(): void
    {
        Permission::firstOrCreate(['name' => 'gestionar_asignaciones_docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'coordinador_academico', 'guard_name' => 'web'])
            ->givePermissionTo('gestionar_asignaciones_docente');

        $coordinador = User::factory()->create(['activo' => true]);
        $coordinador->assignRole('coordinador_academico');

        $docenteActivo = User::factory()->create(['name' => 'Docente Visible', 'activo' => true]);
        $docenteActivo->assignRole('docente');

        $docenteInactivo = User::factory()->create(['name' => 'Docente Oculto', 'activo' => false]);
        $docenteInactivo->assignRole('docente');

        $this->actingAs($coordinador)
            ->getJson('/api/curricular/docentes')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Docente Visible'])
            ->assertJsonMissing(['name' => 'Docente Oculto']);
    }
}
