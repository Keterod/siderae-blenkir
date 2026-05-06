<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'ver_dashboard',
            'gestionar_estudiantes',
            'gestionar_materias',
            'registrar_datos_academicos',
            'procesar_riesgo',
            'ver_alertas',
            'registrar_intervencion',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissionMap = [
            'administrador' => $permissions,
            'docente' => [
                'ver_dashboard',
                'gestionar_estudiantes',
                'registrar_datos_academicos',
                'ver_alertas',
                'registrar_intervencion',
            ],
            'coordinador_academico' => [
                'ver_dashboard',
                'gestionar_estudiantes',
                'registrar_datos_academicos',
                'procesar_riesgo',
                'ver_alertas',
            ],
            'psicologo_tutor' => [
                'ver_alertas',
                'registrar_intervencion',
            ],
            'directivo' => [
                'ver_dashboard',
                'ver_alertas',
            ],
        ];

        foreach ($rolePermissionMap as $roleName => $rolePermissions) {
            /** @var Role $role */
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
