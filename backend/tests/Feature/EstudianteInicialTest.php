<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Nota;
use App\Models\User;
use App\Models\VariableSocioeconomica;
use App\Services\Curricular\CatalogoNivelGrado;
use Database\Seeders\DemoEstudiantesCurricularesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EstudianteInicialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private static function payloadBase(array $override = []): array
    {
        return array_merge([
            'codigo' => 'INI-TEST-001',
            'nombres' => 'Sofía',
            'apellidos' => 'Ramos',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '3 años',
            'seccion' => 'A',
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ], $override);
    }

    private function usuarioConPermiso(): User
    {
        Permission::firstOrCreate([
            'name' => 'gestionar_estudiantes',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo('gestionar_estudiantes');

        return $user;
    }

    #[Test]
    public function migracion_permite_persistir_estudiante_con_nivel_inicial(): void
    {
        $estudiante = Estudiante::query()->create(self::payloadBase([
            'codigo' => 'INI-MIG-001',
        ]));

        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'nivel' => 'inicial',
            'grado' => '3 años',
        ]);
    }

    #[Test]
    public function api_acepta_crear_estudiante_inicial_con_grado_3_anos(): void
    {
        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            self::payloadBase(['codigo' => 'INI-API-001'])
        );

        $response->assertCreated()
            ->assertJsonPath('nivel', 'inicial')
            ->assertJsonPath('grado', '3 años');
    }

    #[Test]
    public function api_rechaza_inicial_con_grado_1_grado(): void
    {
        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            self::payloadBase([
                'codigo' => 'INI-BAD-001',
                'grado' => '1°',
            ])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['grado']);
    }

    #[Test]
    public function api_rechaza_primaria_con_grado_3_anos(): void
    {
        $response = $this->actingAs($this->usuarioConPermiso())->postJson(
            '/api/estudiantes',
            self::payloadBase([
                'codigo' => 'PRI-BAD-001',
                'nivel' => 'primaria',
                'grado' => '3 años',
            ])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['grado']);
    }

    #[Test]
    public function estudiantes_inicial_demo_no_tienen_notas_asistencias_ni_vse(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $ids = Estudiante::query()->where('nivel', 'inicial')->pluck('id');

        $this->assertSame(84, $ids->count());
        $this->assertSame(0, Nota::query()->whereIn('estudiante_id', $ids)->count());
        $this->assertSame(0, Asistencia::query()->whereIn('estudiante_id', $ids)->count());
        $this->assertSame(0, VariableSocioeconomica::query()->whereIn('estudiante_id', $ids)->count());
    }
}
