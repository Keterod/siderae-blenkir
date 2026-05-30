<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\RiesgoCurricularFixtures;
use Tests\TestCase;

class DemoProcesarRiesgosCommandTest extends TestCase
{
    use RefreshDatabase;
    use RiesgoCurricularFixtures;

    /**
     * @return array{0: Estudiante, 1: User}
     */
    private function estudianteConDatosMinimos(string $codigo, array $override = []): array
    {
        $user = User::factory()->create();

        $estudiante = Estudiante::factory()->create(array_merge([
            'codigo' => $codigo,
            'sede' => 'chilca',
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'activo' => true,
        ], $override));

        $this->crearEvalBimResultadoRiesgo($estudiante, 12.5);
        $this->crearAsistenciasDiariasRiesgo($estudiante, $user);
        $this->crearVariableSocioeconomicaRiesgo($estudiante);

        return [$estudiante, $user];
    }

    public function test_comando_procesa_estudiantes_elegibles(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(['*' => Http::response(['indice_riesgo' => 0.55], 200)]);

        [$e1] = $this->estudianteConDatosMinimos('CMD-OK-01');
        [$e2] = $this->estudianteConDatosMinimos('CMD-OK-02');

        $this->artisan('demo:procesar-riesgos', [
            '--sede' => 'chilca',
            '--anio' => '2026',
            '--bimestre' => '1',
            '--confirmar-post-import' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('indices_riesgo', ['estudiante_id' => $e1->id, 'anio_escolar' => '2026', 'bimestre' => '1']);
        $this->assertDatabaseHas('indices_riesgo', ['estudiante_id' => $e2->id, 'anio_escolar' => '2026', 'bimestre' => '1']);
    }

    public function test_comando_omite_si_ya_existe_indice_y_no_force(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(['*' => Http::response(['indice_riesgo' => 0.25], 200)]);

        [$e1] = $this->estudianteConDatosMinimos('CMD-SKIP-01');
        [$e2] = $this->estudianteConDatosMinimos('CMD-SKIP-02');

        IndiceRiesgo::query()->create([
            'estudiante_id' => $e1->id,
            'indice' => 0.2,
            'nivel' => 'Bajo',
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'variables_utilizadas' => [],
            'modelos_scores' => null,
        ]);

        $this->artisan('demo:procesar-riesgos', [
            '--sede' => 'chilca',
            '--anio' => '2026',
            '--bimestre' => '1',
            '--confirmar-post-import' => true,
        ])->expectsOutputToContain('Omitidos por índice existente: 1')
            ->assertExitCode(0);

        $this->assertSame(1, IndiceRiesgo::query()->where('estudiante_id', $e1->id)->count());
        $this->assertSame(1, IndiceRiesgo::query()->where('estudiante_id', $e2->id)->count());
    }

    public function test_comando_no_se_cae_si_ml_falla_y_continua(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(['*' => Http::response('Service Unavailable', 503)]);

        [$e1] = $this->estudianteConDatosMinimos('CMD-FAIL-01');
        [$e2] = $this->estudianteConDatosMinimos('CMD-FAIL-02');

        $this->artisan('demo:procesar-riesgos', [
            '--sede' => 'chilca',
            '--anio' => '2026',
            '--bimestre' => '1',
            '--confirmar-post-import' => true,
        ])->expectsOutputToContain('Fallidos por ML/error:')
            ->assertExitCode(0);

        $this->assertSame(0, IndiceRiesgo::query()->whereIn('estudiante_id', [$e1->id, $e2->id])->count());
    }

    public function test_comando_sin_confirmacion_explicita_no_procesa(): void
    {
        config(['services.ml.url' => 'http://ml-test.local']);
        Http::fake(['*' => Http::response(['indice_riesgo' => 0.55], 200)]);

        [$e1] = $this->estudianteConDatosMinimos('CMD-NOCONF-01');

        $this->artisan('demo:procesar-riesgos', [
            '--sede' => 'chilca',
            '--anio' => '2026',
            '--bimestre' => '1',
        ])->expectsOutputToContain('Para ejecutar este procesamiento masivo, usa --confirmar-post-import.')
            ->assertExitCode(1);

        $this->assertSame(0, IndiceRiesgo::query()->where('estudiante_id', $e1->id)->count());
    }
}

