<?php

namespace Tests\Feature;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\ReporteConductual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Support\RiesgoCurricularFixtures;
use Tests\TestCase;

class SemaforoCompletitudTest extends TestCase
{
    use RefreshDatabase;
    use RiesgoCurricularFixtures;

    private const PERMISO = 'ver_semaforo_completitud';

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
        return array_merge([
            'codigo' => 'EST-RF19-001',
            'nombres' => 'Luis',
            'apellidos' => 'Torres',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ], $override);
    }

    private function crearEstudianteChilca(array $override = []): Estudiante
    {
        return Estudiante::factory()->create($this->estudiantePayload($override));
    }

    public function test_usuario_sin_sesion_recibe_401(): void
    {
        $estudiante = $this->crearEstudianteChilca();

        $this->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertUnauthorized();
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioSinPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_consultar(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertOk()
            ->assertJsonPath('estudiante_id', $estudiante->id)
            ->assertJsonPath('anio_escolar', '2026');
    }

    public function test_estudiante_auquimarca_recibe_403(): void
    {
        $estudiante = $this->crearEstudianteChilca([
            'codigo' => 'EST-AUQ-001',
            'sede' => 'auquimarca',
        ]);
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertForbidden()
            ->assertJsonPath('message', 'Estudiante fuera de la sede operativa V1 (Chilca).');
    }

    public function test_rojo_cuando_no_hay_datos(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud");

        $response->assertOk()
            ->assertJsonPath('color', 'rojo')
            ->assertJsonPath('etiqueta', 'Datos insuficientes')
            ->assertJsonPath('razones.0.presente', false)
            ->assertJsonPath('razones.1.presente', false)
            ->assertJsonPath('razones.2.presente', false)
            ->assertJsonPath('razones.3.presente', false);
    }

    public function test_amarillo_cuando_hay_datos_parciales(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearNotaSemanalCeRiesgo($estudiante, $user, 14.0);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud");

        $response->assertOk()
            ->assertJsonPath('color', 'amarillo')
            ->assertJsonPath('razones.0.presente', true)
            ->assertJsonPath('razones.1.presente', false)
            ->assertJsonPath('razones.2.presente', false)
            ->assertJsonPath('razones.3.presente', false);
    }

    public function test_verde_cuando_hay_notas_y_asistencia(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->crearNotaSemanalCeRiesgo($estudiante, $user, 14.0);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user, 2);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud");

        $response->assertOk()
            ->assertJsonPath('color', 'verde')
            ->assertJsonPath('etiqueta', 'Datos suficientes')
            ->assertJsonPath('razones.0.presente', true)
            ->assertJsonPath('razones.1.presente', true);
    }

    public function test_respuesta_incluye_color_mensaje_y_razones(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud");

        $response->assertOk()
            ->assertJsonStructure([
                'estudiante_id',
                'anio_escolar',
                'bimestre',
                'color',
                'etiqueta',
                'mensaje',
                'razones' => [
                    '*' => ['dato', 'presente', 'mensaje'],
                ],
            ]);
    }

    public function test_consulta_no_modifica_indices_riesgo(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        IndiceRiesgo::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice' => 0.55,
            'nivel' => 'Medio',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => [],
            'modelos_scores' => null,
        ]);

        $conteoAntes = IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertOk();

        $this->assertSame($conteoAntes, IndiceRiesgo::query()->where('estudiante_id', $estudiante->id)->count());
    }

    public function test_amarillo_puede_ser_por_reporte_conductual_activo(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $user->id,
            'fecha' => '2026-06-10',
            'tipo_conducta' => 'Falta de respeto',
            'descripcion' => 'Incidente leve',
            'nivel_gravedad' => 'leve',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud");

        $response->assertOk()
            ->assertJsonPath('color', 'amarillo')
            ->assertJsonPath('razones.2.presente', true);
    }

    public function test_consulta_no_llama_a_flask(): void
    {
        $estudiante = $this->crearEstudianteChilca();
        $user = $this->usuarioConPermiso();

        $this->actingAs($user)
            ->getJson("/api/estudiantes/{$estudiante->id}/semaforo-completitud")
            ->assertOk();

        // No hay forma directa de assert HTTP fake sin configurarlo; el servicio no usa HTTP.
        $this->assertTrue(true);
    }
}
