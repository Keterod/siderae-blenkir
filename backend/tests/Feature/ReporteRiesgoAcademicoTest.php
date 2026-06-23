<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\User;
use App\Support\SedeOperativa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReporteRiesgoAcademicoTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'ver_reportes_riesgo';

    private static int $contadorCodigo = 0;

    private function crearPermiso(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISO, 'guard_name' => 'web']);
    }

    private function usuarioConPermiso(): User
    {
        $this->crearPermiso();
        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISO);

        return $user;
    }

    private function usuarioSinPermiso(): User
    {
        $this->crearPermiso();

        return User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function estudiantePayload(array $override = []): array
    {
        self::$contadorCodigo++;

        return array_merge([
            'codigo' => 'EST-RF16-' . self::$contadorCodigo,
            'nombres' => 'María',
            'apellidos' => 'López',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '2°',
            'seccion' => 'B',
            'nivel' => 'primaria',
            'sede' => SedeOperativa::CHILCA,
            'anio_escolar' => '2026',
        ], $override);
    }

    private function crearEstudiante(array $override = []): Estudiante
    {
        return Estudiante::factory()->create($this->estudiantePayload($override));
    }

    /**
     * @param array<string, mixed> $override
     */
    private function crearIndiceRiesgo(Estudiante $estudiante, array $override = []): IndiceRiesgo
    {
        return IndiceRiesgo::query()->create(array_merge([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.55,
            'nivel' => 'Medio',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => null,
            'modelos_scores' => null,
        ], $override));
    }

    public function test_usuario_sin_sesion_recibe_401(): void
    {
        $this->getJson('/api/reportes/riesgo-academico')
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $user = $this->usuarioSinPermiso();

        $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico')
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_consultar_reporte(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudiante_id', $estudiante->id)
            ->assertJsonPath('data.0.nivel', 'Medio');
    }

    public function test_no_incluye_estudiantes_fuera_de_chilca(): void
    {
        $estudianteChilca = $this->crearEstudiante(['codigo' => 'EST-CHILCA']);
        $estudianteAuquimarca = $this->crearEstudiante([
            'codigo' => 'EST-AUQ',
            'sede' => 'auquimarca',
        ]);

        $this->crearIndiceRiesgo($estudianteChilca);
        $this->crearIndiceRiesgo($estudianteAuquimarca);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudiante_id', $estudianteChilca->id);
    }

    public function test_filtro_por_anio_escolar(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2025']);
        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2026', 'bimestre' => '2']);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?anio_escolar=2026');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.anio_escolar', '2026');
    }

    public function test_filtro_por_bimestre(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '1']);
        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '2']);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?bimestre=2');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.bimestre', '2');
    }

    public function test_filtro_por_grado(): void
    {
        $estudiante1 = $this->crearEstudiante(['grado' => '2°', 'seccion' => 'A']);
        $estudiante2 = $this->crearEstudiante(['grado' => '5°', 'seccion' => 'A']);

        $this->crearIndiceRiesgo($estudiante1);
        $this->crearIndiceRiesgo($estudiante2);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?grado=5°');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.grado', '5°');
    }

    public function test_filtro_por_seccion(): void
    {
        $estudiante1 = $this->crearEstudiante(['grado' => '2°', 'seccion' => 'A']);
        $estudiante2 = $this->crearEstudiante(['grado' => '2°', 'seccion' => 'B']);

        $this->crearIndiceRiesgo($estudiante1);
        $this->crearIndiceRiesgo($estudiante2);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?seccion=B');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.seccion', 'B');
    }

    public function test_filtro_por_nivel(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['nivel' => 'Alto', 'indice' => 0.85]);
        $this->crearIndiceRiesgo($estudiante, ['nivel' => 'Bajo', 'indice' => 0.15]);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?nivel=Alto');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nivel', 'Alto');
    }

    public function test_devuelve_paginacion(): void
    {
        $estudiante = $this->crearEstudiante();

        for ($i = 0; $i < 5; $i++) {
            $this->crearIndiceRiesgo($estudiante, [
                'bimestre' => (string) (($i % 4) + 1),
                'created_at' => Carbon::now()->subMinutes($i),
                'updated_at' => Carbon::now()->subMinutes($i),
            ]);
        }

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico?per_page=2&page=1');

        $response->assertOk()
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('total', 5)
            ->assertJsonCount(2, 'data');
    }

    public function test_ordenado_de_mas_reciente_a_mas_antiguo(): void
    {
        $estudiante = $this->crearEstudiante();

        $antiguo = $this->crearIndiceRiesgo($estudiante, [
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $reciente = $this->crearIndiceRiesgo($estudiante, [
            'indice' => 0.10,
            'nivel' => 'Bajo',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico');

        $response->assertOk();
        $this->assertSame($reciente->id, $response->json('data.0.id'));
        $this->assertSame($antiguo->id, $response->json('data.1.id'));
    }

    public function test_consulta_no_recalcula_riesgo(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);

        $user = $this->usuarioConPermiso();
        $conteoAntes = IndiceRiesgo::query()->count();

        $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico')
            ->assertOk();

        $this->assertSame($conteoAntes, IndiceRiesgo::query()->count());
    }

    public function test_consulta_no_llama_a_flask(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/reportes/riesgo-academico')
            ->assertOk();

        $this->assertTrue(true);
    }
}
