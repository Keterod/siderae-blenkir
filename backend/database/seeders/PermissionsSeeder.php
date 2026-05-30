<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $legacyPermissions = [
            'ver_dashboard',
            'gestionar_usuarios',
            'gestionar_estudiantes',
            'gestionar_materias',
            'registrar_datos_academicos',
            'procesar_riesgo',
            'ver_alertas',
            'registrar_intervencion',
        ];

        $curricularPermissions = [
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
        ];

        $permissions = array_merge($legacyPermissions, $curricularPermissions);

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
                'ver_malla_curricular',
                'registrar_notas_semanales',
                'ver_notas_academicas',
                'registrar_asistencia_curricular',
                'ver_asistencia_curricular',
            ],
            'coordinador_academico' => [
                'ver_dashboard',
                'gestionar_estudiantes',
                'registrar_datos_academicos',
                'procesar_riesgo',
                'ver_alertas',
                'ver_malla_curricular',
                'gestionar_malla_curricular',
                'gestionar_temas_semanales',
                'configurar_pesos_evaluacion',
                'gestionar_asignaciones_docente',
                'ver_notas_academicas',
                'configurar_evaluacion_bimestral',
                'registrar_asistencia_curricular',
                'ver_asistencia_curricular',
                'gestionar_calendario_academico',
            ],
            'psicologo_tutor' => [
                'ver_alertas',
                'registrar_intervencion',
                'ver_notas_academicas',
                'ver_asistencia_curricular',
            ],
            'directivo' => [
                'ver_dashboard',
                'ver_alertas',
                'registrar_intervencion',
                'ver_malla_curricular',
                'ver_notas_academicas',
                'ver_asistencia_curricular',
            ],
        ];

        foreach ($rolePermissionMap as $roleName => $rolePermissions) {
            /** @var Role $role */
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
