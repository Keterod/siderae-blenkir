<?php

namespace Tests\Feature\Curricular;

use App\Models\User;
use Database\Seeders\CurricularModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class CurricularApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurricularModuleSeeder::class);
        $this->seedCurricularPermissions();
    }

    protected function seedCurricularPermissions(): void
    {
        $names = [
            'ver_malla_curricular',
            'gestionar_malla_curricular',
            'gestionar_temas_semanales',
            'configurar_pesos_evaluacion',
            'gestionar_asignaciones_docente',
            'registrar_notas_semanales',
            'ver_notas_academicas',
        ];

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (['docente', 'coordinador_academico', 'administrador'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
    }

    protected function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    protected function coordinador(): User
    {
        return $this->userWithPermissions([
            'ver_malla_curricular',
            'gestionar_malla_curricular',
            'gestionar_temas_semanales',
            'configurar_pesos_evaluacion',
            'gestionar_asignaciones_docente',
            'ver_notas_academicas',
        ]);
    }

    protected function docente(): User
    {
        $user = $this->userWithPermissions([
            'ver_malla_curricular',
            'registrar_notas_semanales',
            'ver_notas_academicas',
        ]);
        $user->assignRole('docente');

        return $user;
    }

    protected function usuarioDocenteAsignable(?string $nombre = null): User
    {
        $user = User::factory()->create([
            'name' => $nombre ?? fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->assignRole('docente');

        return $user;
    }
}
