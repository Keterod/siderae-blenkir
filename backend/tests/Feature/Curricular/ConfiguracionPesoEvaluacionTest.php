<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Models\Curricular\MallaCurso;
use App\Services\Curricular\PesoEvaluacionResolver;
use PHPUnit\Framework\Attributes\Test;

class ConfiguracionPesoEvaluacionTest extends CurricularApiTestCase
{
    #[Test]
    public function resolver_devuelve_configuracion_global_si_no_hay_otra(): void
    {
        [$mallaCurso] = $this->prepararMallaCurso();

        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/pesos/resolver?malla_curso_id='.$mallaCurso->id)
            ->assertOk();

        $response->assertJsonPath('scope_aplicado', PesoEvaluacionResolver::SCOPE_GLOBAL);
        $response->assertJsonPath('es_por_defecto', false);
        $response->assertJsonPath('pesos.cuaderno', 33.33);
        $response->assertJsonPath('pesos.libro', 33.33);
        $response->assertJsonPath('pesos.tarea', 33.34);
        $this->assertNotNull($response->json('configuracion.id'));
    }

    #[Test]
    public function resolver_prioriza_curso_sobre_area_nivel_grado_y_global(): void
    {
        [$mallaCurso] = $this->prepararMallaCurso();
        $malla = $mallaCurso->mallaCurricular;

        $this->crearPeso([
            'nivel' => $malla->nivel,
            'grado' => $malla->grado,
            'peso_cuaderno' => 20,
            'peso_libro' => 20,
            'peso_tarea' => 60,
        ]);
        $this->crearPeso([
            'area_id' => $mallaCurso->area_id,
            'peso_cuaderno' => 30,
            'peso_libro' => 30,
            'peso_tarea' => 40,
        ]);
        $this->crearPeso([
            'area_id' => $mallaCurso->area_id,
            'curso_catalogo_id' => $mallaCurso->curso_catalogo_id,
            'peso_cuaderno' => 40,
            'peso_libro' => 40,
            'peso_tarea' => 20,
        ]);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/pesos/resolver?malla_curso_id='.$mallaCurso->id)
            ->assertOk()
            ->assertJsonPath('scope_aplicado', PesoEvaluacionResolver::SCOPE_CURSO)
            ->assertJsonPath('pesos.cuaderno', 40)
            ->assertJsonPath('pesos.libro', 40)
            ->assertJsonPath('pesos.tarea', 20);
    }

    #[Test]
    public function resolver_prioriza_area_sobre_nivel_grado_y_global(): void
    {
        [$mallaCurso] = $this->prepararMallaCurso();
        $malla = $mallaCurso->mallaCurricular;

        $this->crearPeso([
            'nivel' => $malla->nivel,
            'grado' => $malla->grado,
            'peso_cuaderno' => 20,
            'peso_libro' => 20,
            'peso_tarea' => 60,
        ]);
        $this->crearPeso([
            'area_id' => $mallaCurso->area_id,
            'peso_cuaderno' => 35,
            'peso_libro' => 35,
            'peso_tarea' => 30,
        ]);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/pesos/resolver?malla_curso_id='.$mallaCurso->id)
            ->assertOk()
            ->assertJsonPath('scope_aplicado', PesoEvaluacionResolver::SCOPE_AREA)
            ->assertJsonPath('pesos.cuaderno', 35);
    }

    #[Test]
    public function resolver_prioriza_nivel_grado_sobre_global(): void
    {
        [$mallaCurso] = $this->prepararMallaCurso();
        $malla = $mallaCurso->mallaCurricular;

        $this->crearPeso([
            'nivel' => $malla->nivel,
            'grado' => $malla->grado,
            'peso_cuaderno' => 25,
            'peso_libro' => 25,
            'peso_tarea' => 50,
        ]);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/pesos/resolver?malla_curso_id='.$mallaCurso->id)
            ->assertOk()
            ->assertJsonPath('scope_aplicado', PesoEvaluacionResolver::SCOPE_NIVEL_GRADO)
            ->assertJsonPath('pesos.cuaderno', 25);
    }

    #[Test]
    public function store_rechaza_duplicado_activo_del_mismo_scope(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/pesos', [
                'peso_cuaderno' => 50,
                'peso_libro' => 30,
                'peso_tarea' => 20,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['scope']);
    }

    #[Test]
    public function patch_actualiza_pesos_validos(): void
    {
        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->whereNull('nivel')
            ->whereNull('grado')
            ->whereNull('area_id')
            ->whereNull('curso_catalogo_id')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson('/api/curricular/pesos/'.$config->id, [
                'peso_cuaderno' => 40,
                'peso_libro' => 40,
                'peso_tarea' => 20,
            ])
            ->assertOk()
            ->assertJsonPath('peso_cuaderno', '40.00')
            ->assertJsonPath('peso_libro', '40.00')
            ->assertJsonPath('peso_tarea', '20.00');
    }

    #[Test]
    public function rechaza_suma_diferente_de_cien(): void
    {
        [$mallaCurso] = $this->prepararMallaCurso();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/pesos', [
                'area_id' => $mallaCurso->area_id,
                'peso_cuaderno' => 40,
                'peso_libro' => 40,
                'peso_tarea' => 10,
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function desactivar_permite_crear_nueva_configuracion_del_mismo_scope(): void
    {
        $config = ConfiguracionPesoEvaluacion::query()
            ->where('activo', true)
            ->whereNull('nivel')
            ->whereNull('grado')
            ->whereNull('area_id')
            ->whereNull('curso_catalogo_id')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson('/api/curricular/pesos/'.$config->id.'/desactivar')
            ->assertOk()
            ->assertJsonPath('activo', false);

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/pesos', [
                'peso_cuaderno' => 50,
                'peso_libro' => 30,
                'peso_tarea' => 20,
            ])
            ->assertCreated()
            ->assertJsonPath('peso_cuaderno', '50.00');
    }

    /**
     * @return array{0: MallaCurso}
     */
    private function prepararMallaCurso(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->with('mallaCurricular')->firstOrFail();

        return [$mallaCurso];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function crearPeso(array $overrides): ConfiguracionPesoEvaluacion
    {
        return ConfiguracionPesoEvaluacion::query()->create([
            'nivel' => null,
            'grado' => null,
            'area_id' => null,
            'curso_catalogo_id' => null,
            'peso_cuaderno' => 33.33,
            'peso_libro' => 33.33,
            'peso_tarea' => 33.34,
            'activo' => true,
            ...$overrides,
        ]);
    }
}
