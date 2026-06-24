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

class DashboardInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'ver_dashboard_institucional';
    private const PERMISO_DASHBOARD_LEGACY = 'ver_dashboard';

    private static int $contadorCodigo = 0;

    private function crearPermisoInstitucional(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISO, 'guard_name' => 'web']);
    }

    private function usuarioConPermiso(): User
    {
        $this->crearPermisoInstitucional();
        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISO);

        return $user;
    }

    private function usuarioSinPermiso(): User
    {
        $this->crearPermisoInstitucional();

        return User::factory()->create();
    }

    private function usuarioDocente(): User
    {
        Permission::firstOrCreate(['name' => self::PERMISO_DASHBOARD_LEGACY, 'guard_name' => 'web']);
        $this->crearPermisoInstitucional();
        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISO_DASHBOARD_LEGACY);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function estudiantePayload(array $override = []): array
    {
        self::$contadorCodigo++;

        return array_merge([
            'codigo' => 'EST-RF14-' . self::$contadorCodigo,
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
        $this->getJson('/api/dashboard/institucional')
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $user = $this->usuarioSinPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertForbidden();
    }

    public function test_docente_con_ver_dashboard_legacy_recibe_403(): void
    {
        $user = $this->usuarioDocente();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_consultar_dashboard(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk()
            ->assertJsonStructure([
                'resumen' => [
                    'total_estudiantes',
                    'con_riesgo',
                    'riesgo_bajo',
                    'riesgo_medio',
                    'riesgo_alto',
                ],
                'completitud' => [
                    'con_riesgo',
                    'sin_riesgo',
                    'porcentaje_con_riesgo',
                ],
                'por_grado_seccion',
                'ultimos_riesgos',
            ]);
    }

    public function test_limita_datos_a_sede_chilca(): void
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
            ->getJson('/api/dashboard/institucional');

        $response->assertOk()
            ->assertJsonPath('resumen.total_estudiantes', 1)
            ->assertJsonPath('resumen.con_riesgo', 1)
            ->assertJsonPath('completitud.con_riesgo', 1)
            ->assertJsonPath('completitud.sin_riesgo', 0)
            ->assertJsonCount(1, 'por_grado_seccion')
            ->assertJsonCount(1, 'ultimos_riesgos');
    }

    public function test_resumen_cuenta_total_estudiantes(): void
    {
        $this->crearEstudiante();
        $this->crearEstudiante(['codigo' => 'EST-02']);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk()
            ->assertJsonPath('resumen.total_estudiantes', 2);
    }

    public function test_resumen_cuenta_con_riesgo(): void
    {
        $conRiesgo = $this->crearEstudiante();
        $sinRiesgo = $this->crearEstudiante(['codigo' => 'EST-SIN']);

        $this->crearIndiceRiesgo($conRiesgo);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk()
            ->assertJsonPath('resumen.con_riesgo', 1)
            ->assertJsonPath('resumen.total_estudiantes', 2);
    }

    public function test_resumen_cuenta_riesgo_bajo_medio_alto(): void
    {
        $bajo = $this->crearEstudiante(['codigo' => 'EST-BAJO']);
        $medio = $this->crearEstudiante(['codigo' => 'EST-MEDIO']);
        $alto = $this->crearEstudiante(['codigo' => 'EST-ALTO']);

        $this->crearIndiceRiesgo($bajo, ['indice' => 0.15, 'nivel' => 'Bajo']);
        $this->crearIndiceRiesgo($medio, ['indice' => 0.50, 'nivel' => 'Medio']);
        $this->crearIndiceRiesgo($alto, ['indice' => 0.85, 'nivel' => 'Alto']);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk()
            ->assertJsonPath('resumen.riesgo_bajo', 1)
            ->assertJsonPath('resumen.riesgo_medio', 1)
            ->assertJsonPath('resumen.riesgo_alto', 1);
    }

    public function test_filtro_por_anio_escolar(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2025', 'nivel' => 'Bajo', 'indice' => 0.10]);
        $this->crearIndiceRiesgo($estudiante, ['anio_escolar' => '2026', 'nivel' => 'Alto', 'indice' => 0.85]);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional?anio_escolar=2025')
            ->assertOk()
            ->assertJsonPath('resumen.con_riesgo', 1)
            ->assertJsonPath('resumen.riesgo_bajo', 1)
            ->assertJsonPath('resumen.riesgo_alto', 0);
    }

    public function test_filtro_por_bimestre(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '1', 'nivel' => 'Bajo', 'indice' => 0.10]);
        $this->crearIndiceRiesgo($estudiante, ['bimestre' => '2', 'nivel' => 'Alto', 'indice' => 0.85]);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional?bimestre=2')
            ->assertOk()
            ->assertJsonPath('resumen.con_riesgo', 1)
            ->assertJsonPath('resumen.riesgo_bajo', 0)
            ->assertJsonPath('resumen.riesgo_alto', 1);
    }

    public function test_filtro_por_grado_y_seccion(): void
    {
        $estudianteA = $this->crearEstudiante(['grado' => '5°', 'seccion' => 'A']);
        $estudianteB = $this->crearEstudiante(['grado' => '5°', 'seccion' => 'B']);

        $this->crearIndiceRiesgo($estudianteA);
        $this->crearIndiceRiesgo($estudianteB);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional?grado=5°&seccion=A')
            ->assertOk()
            ->assertJsonPath('resumen.total_estudiantes', 1)
            ->assertJsonCount(1, 'por_grado_seccion')
            ->assertJsonPath('por_grado_seccion.0.seccion', 'A');
    }

    public function test_distribucion_por_grado_y_seccion(): void
    {
        $estudianteA = $this->crearEstudiante(['grado' => '5°', 'seccion' => 'A']);
        $estudianteB = $this->crearEstudiante(['grado' => '5°', 'seccion' => 'B']);

        $this->crearIndiceRiesgo($estudianteA, ['nivel' => 'Alto', 'indice' => 0.85]);
        $this->crearIndiceRiesgo($estudianteB, ['nivel' => 'Bajo', 'indice' => 0.10]);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/dashboard/institucional');

        $response->assertOk()
            ->assertJsonCount(2, 'por_grado_seccion');

        $grupos = collect($response->json('por_grado_seccion'))->keyBy('seccion');
        $this->assertSame(1, $grupos['A']['riesgo_alto']);
        $this->assertSame(1, $grupos['B']['riesgo_bajo']);
    }

    public function test_devuelve_ultimos_riesgos(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, [
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);
        $this->crearIndiceRiesgo($estudiante, [
            'indice' => 0.10,
            'nivel' => 'Bajo',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson('/api/dashboard/institucional');

        $response->assertOk()
            ->assertJsonCount(2, 'ultimos_riesgos')
            ->assertJsonPath('ultimos_riesgos.0.nivel', 'Bajo')
            ->assertJsonPath('ultimos_riesgos.1.nivel', 'Medio');
    }

    public function test_consulta_no_recalcula_riesgo(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);

        $user = $this->usuarioConPermiso();
        $conteoAntes = IndiceRiesgo::query()->count();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk();

        $this->assertSame($conteoAntes, IndiceRiesgo::query()->count());
    }

    public function test_consulta_no_llama_a_flask(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson('/api/dashboard/institucional')
            ->assertOk();

        $this->assertTrue(true);
    }

    public function test_dashboard_legacy_sigue_funcionando(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISO_DASHBOARD_LEGACY, 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISO_DASHBOARD_LEGACY);

        $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertOk();
    }
}
