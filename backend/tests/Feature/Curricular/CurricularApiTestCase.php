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
            'configurar_evaluacion_bimestral',
            'registrar_asistencia_curricular',
            'ver_asistencia_curricular',
            'gestionar_calendario_academico',
            'gestionar_competencias_capacidades',
        ];

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (['docente', 'coordinador_academico', 'administrador', 'directivo'] as $roleName) {
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
            'gestionar_competencias_capacidades',
            'configurar_pesos_evaluacion',
            'gestionar_asignaciones_docente',
            'ver_notas_academicas',
            'configurar_evaluacion_bimestral',
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

    protected function administrador(): User
    {
        $user = $this->userWithPermissions([
            'ver_malla_curricular',
            'gestionar_malla_curricular',
            'gestionar_temas_semanales',
            'gestionar_competencias_capacidades',
            'configurar_pesos_evaluacion',
            'gestionar_asignaciones_docente',
            'registrar_notas_semanales',
            'ver_notas_academicas',
        ]);
        $user->assignRole('administrador');

        return $user;
    }

    protected function directivo(): User
    {
        $user = $this->userWithPermissions([
            'ver_malla_curricular',
            'ver_notas_academicas',
        ]);
        $user->assignRole('directivo');

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
