<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios de demostración por rol (entorno local / demo únicamente).
 * Requiere que existan roles y permisos (p. ej. RolesSeeder y PermissionsSeeder) antes de ejecutar.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $demos = [
            [
                'email' => 'admin@siderae.test',
                'name' => 'Administrador Demo',
                'role' => 'administrador',
            ],
            [
                'email' => 'docente@siderae.test',
                'name' => 'Docente Demo',
                'role' => 'docente',
            ],
            [
                'email' => 'docente2@siderae.test',
                'name' => 'Docente Demo 2',
                'role' => 'docente',
            ],
            [
                'email' => 'docente3@siderae.test',
                'name' => 'Docente Demo 3',
                'role' => 'docente',
            ],
            [
                'email' => 'coordinador@siderae.test',
                'name' => 'Coordinador Académico Demo',
                'role' => 'coordinador_academico',
            ],
            [
                'email' => 'psicologo@siderae.test',
                'name' => 'Psicólogo Tutor Demo',
                'role' => 'psicologo_tutor',
            ],
            [
                'email' => 'directivo@siderae.test',
                'name' => 'Directivo Demo',
                'role' => 'directivo',
            ],
        ];

        foreach ($demos as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'activo' => true,
                ]
            );

            $user->syncRoles([$row['role']]);
        }
    }
}
