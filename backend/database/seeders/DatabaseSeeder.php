<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            DemoUsersSeeder::class,
            CurricularModuleSeeder::class,
            DemoEstudiantesCurricularesSeeder::class,
            DemoCurricularOperativoSeeder::class,
        ]);

        $adminUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );

        if ($adminUser->email_verified_at === null) {
            $adminUser->forceFill(['email_verified_at' => now()])->save();
        }

        $adminUser->syncRoles(['administrador']);
    }
}
