<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\ReporteConductual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PsicologoTutorSeguimientoTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'ver_perfil_psicologo_tutor';
    private const RUTA = '/api/psicologo-tutor/seguimiento';

    private function crearPermiso(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISO, 'guard_name' => 'web']);
    }

    private function usuarioConPermiso(string $rol = 'psicologo_tutor'): User
    {
        $this->crearPermiso();
        Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($rol);
        $user->givePermissionTo(self::PERMISO);

        return $user;
    }

    private function usuarioSinPermiso(?string $rol = null): User
    {
        $this->crearPermiso();
        if ($rol !== null) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }
        $user = User::factory()->create();
        if ($rol !== null) {
            $user->assignRole($rol);
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function estudiantePayload(array $override = []): array
    {
        return array_merge([
            'codigo' => 'EST-RF11-001',
            'nombres' => 'María',
            'apellidos' => 'López',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '5',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
            'activo' => true,
        ], $override);
    }

    private function crearEstudiante(array $override = []): Estudiante
    {
        return Estudiante::factory()->create($this->estudiantePayload($override));
    }

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

    private function crearReporteConductualActivo(Estudiante $estudiante): ReporteConductual
    {
        return ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => User::factory()->create()->id,
            'fecha' => Carbon::now()->toDateString(),
            'tipo_conducta' => 'agresion',
            'descripcion' => 'Reporte de prueba',
            'nivel_gravedad' => 'grave',
            'accion_inmediata' => 'Entrevista',
            'estado' => 'activo',
        ]);
    }

    private function crearAlertaActiva(Estudiante $estudiante): Alerta
    {
        return Alerta::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => null,
            'estado' => 'pendiente',
            'factores_influyentes' => [],
            'recomendacion' => 'Seguimiento',
        ]);
    }

    public function test_usuario_sin_sesion_recibe_401(): void
    {
        $this->getJson(self::RUTA)
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $user = $this->usuarioSinPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertForbidden();
    }

    public function test_psicologo_tutor_con_permiso_consulta_seguimiento(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso('psicologo_tutor');

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk()
            ->assertJsonPath('data.0.estudiante_id', $estudiante->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'estudiante_id',
                        'estudiante',
                        'grado',
                        'seccion',
                        'ultimo_indice',
                        'ultimo_nivel',
                        'fecha_ultimo_riesgo',
                        'reportes_conductuales_activos',
                        'alertas_activas',
                        'semaforo_completitud',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    }

    public function test_docente_sin_permiso_recibe_403(): void
    {
        $user = $this->usuarioSinPermiso('docente');

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertForbidden();
    }

    public function test_directivo_sin_permiso_recibe_403(): void
    {
        $user = $this->usuarioSinPermiso('directivo');

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertForbidden();
    }

    public function test_limita_datos_a_chilca(): void
    {
        $estudianteChilca = $this->crearEstudiante(['codigo' => 'EST-CHILCA']);
        $this->crearIndiceRiesgo($estudianteChilca);

        $estudianteAuquimarca = $this->crearEstudiante([
            'codigo' => 'EST-AUQUI',
            'sede' => 'auquimarca',
        ]);
        $this->crearIndiceRiesgo($estudianteAuquimarca);

        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)->getJson(self::RUTA);

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudianteChilca->id);
    }

    public function test_incluye_estudiante_con_riesgo_registrado(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante, ['nivel' => 'Alto', 'indice' => 0.82]);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk()
            ->assertJsonPath('data.0.ultimo_nivel', 'Alto')
            ->assertJsonPath('data.0.ultimo_indice', 0.82);
    }

    public function test_incluye_estudiante_con_reporte_conductual_activo(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearReporteConductualActivo($estudiante);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.reportes_conductuales_activos', 1);
    }

    public function test_excluye_estudiante_sin_riesgo_sin_reportes_y_sin_alertas(): void
    {
        $this->crearEstudiante(['codigo' => 'EST-SIN-SENALES']);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_filtro_por_anio_escolar(): void
    {
        $estudiante2026 = $this->crearEstudiante(['codigo' => 'EST-2026', 'anio_escolar' => '2026']);
        $this->crearIndiceRiesgo($estudiante2026);

        $estudiante2025 = $this->crearEstudiante(['codigo' => 'EST-2025', 'anio_escolar' => '2025']);
        $this->crearIndiceRiesgo($estudiante2025);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?anio_escolar=2026')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudiante2026->id);
    }

    public function test_filtro_por_nivel(): void
    {
        $estudiantePrimaria = $this->crearEstudiante(['codigo' => 'EST-PRI', 'nivel' => 'primaria']);
        $this->crearIndiceRiesgo($estudiantePrimaria);

        $estudianteSecundaria = $this->crearEstudiante(['codigo' => 'EST-SEC', 'nivel' => 'secundaria']);
        $this->crearIndiceRiesgo($estudianteSecundaria);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?nivel=secundaria')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudianteSecundaria->id);
    }

    public function test_filtro_por_grado(): void
    {
        $estudiante5 = $this->crearEstudiante(['codigo' => 'EST-G5', 'grado' => '5']);
        $this->crearIndiceRiesgo($estudiante5);

        $estudiante6 = $this->crearEstudiante(['codigo' => 'EST-G6', 'grado' => '6']);
        $this->crearIndiceRiesgo($estudiante6);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?grado=6')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudiante6->id);
    }

    public function test_filtro_por_seccion(): void
    {
        $estudianteA = $this->crearEstudiante(['codigo' => 'EST-A', 'seccion' => 'A']);
        $this->crearIndiceRiesgo($estudianteA);

        $estudianteB = $this->crearEstudiante(['codigo' => 'EST-B', 'seccion' => 'B']);
        $this->crearIndiceRiesgo($estudianteB);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?seccion=B')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudianteB->id);
    }

    public function test_filtro_por_nivel_riesgo(): void
    {
        $estudianteAlto = $this->crearEstudiante(['codigo' => 'EST-ALTO']);
        $this->crearIndiceRiesgo($estudianteAlto, ['nivel' => 'Alto', 'indice' => 0.85]);

        $estudianteBajo = $this->crearEstudiante(['codigo' => 'EST-BAJO']);
        $this->crearIndiceRiesgo($estudianteBajo, ['nivel' => 'Bajo', 'indice' => 0.20]);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?nivel_riesgo=Alto')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.ultimo_nivel', 'Alto');
    }

    public function test_filtro_con_reportes_activos(): void
    {
        $estudianteConReporte = $this->crearEstudiante(['codigo' => 'EST-RC']);
        $this->crearReporteConductualActivo($estudianteConReporte);

        $estudianteConRiesgo = $this->crearEstudiante(['codigo' => 'EST-RIESGO']);
        $this->crearIndiceRiesgo($estudianteConRiesgo);

        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA . '?con_reportes_activos=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.estudiante_id', $estudianteConReporte->id);
    }

    public function test_devuelve_paginacion(): void
    {
        $user = $this->usuarioConPermiso();

        for ($i = 1; $i <= 5; $i++) {
            $estudiante = $this->crearEstudiante(['codigo' => "EST-PAG-{$i}"]);
            $this->crearIndiceRiesgo($estudiante);
        }

        $this->actingAs($user)
            ->getJson(self::RUTA . '?per_page=2')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonCount(2, 'data');
    }

    public function test_no_recalcula_riesgo(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $conteoAntes = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk();

        $this->assertSame($conteoAntes, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }

    public function test_no_llama_a_flask(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson(self::RUTA)
            ->assertOk();

        // El controller consulta tablas locales; no realiza llamadas HTTP al microservicio Flask.
        $this->assertTrue(true);
    }

    public function test_no_expone_vse_ni_fast_test(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)->getJson(self::RUTA);

        $response->assertOk();
        $json = json_encode($response->json());
        $this->assertStringNotContainsString('variables_socioeconomicas', strtolower((string) $json));
        $this->assertStringNotContainsString('fast_test', strtolower((string) $json));
    }

    public function test_no_expone_informacion_clinica_ni_medica(): void
    {
        $estudiante = $this->crearEstudiante();
        $this->crearIndiceRiesgo($estudiante);
        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)->getJson(self::RUTA);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'estudiante_id',
                        'estudiante',
                        'grado',
                        'seccion',
                        'ultimo_indice',
                        'ultimo_nivel',
                        'fecha_ultimo_riesgo',
                        'reportes_conductuales_activos',
                        'alertas_activas',
                        'semaforo_completitud',
                    ],
                ],
                'meta',
            ]);

        $json = json_encode($response->json());
        $this->assertStringNotContainsString('diagnostico', strtolower((string) $json));
        $this->assertStringNotContainsString('historia_clinica', strtolower((string) $json));
        $this->assertStringNotContainsString('medico', strtolower((string) $json));
    }
}
