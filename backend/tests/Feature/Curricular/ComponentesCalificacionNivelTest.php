<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Services\Curricular\ComponenteCalificacionNivelService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class ComponentesCalificacionNivelTest extends CurricularApiTestCase
{
    private const ANIO = '2026';

    #[Test]
    public function seeder_crea_defaults_por_nivel(): void
    {
        $inicial = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $this->assertCount(1, $inicial);
        $this->assertSame('cuaderno', $inicial->first()->codigo);
        $this->assertEqualsWithDelta(100.0, (float) $inicial->sum('peso'), 0.01);

        foreach (['primaria', 'secundaria'] as $nivel) {
            $componentes = ComponenteCalificacionNivel::query()
                ->where('anio_escolar', self::ANIO)
                ->where('nivel', $nivel)
                ->where('activo', true)
                ->get();

            $this->assertCount(3, $componentes);
            $this->assertEqualsWithDelta(100.0, (float) $componentes->sum('peso'), 0.01);
        }
    }

    #[Test]
    public function index_lista_componentes_por_nivel(): void
    {
        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/componentes-calificacion?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
            ]))
            ->assertOk();

        $this->assertGreaterThanOrEqual(3, count($response->json()));
        $this->assertSame('cuaderno', $response->json('0.codigo'));
    }

    #[Test]
    public function por_nivel_incluye_validacion_suma(): void
    {
        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/componentes-calificacion/por-nivel/inicial?anio_escolar='.self::ANIO)
            ->assertOk()
            ->assertJsonPath('validacion.valido', true)
            ->assertJsonPath('validacion.suma', 100)
            ->assertJsonCount(1, 'componentes');
    }

    #[Test]
    public function puede_crear_componente_inactivo_sin_validar_suma(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'nombre' => 'Observación',
                'activo' => false,
            ])
            ->assertCreated()
            ->assertJsonPath('activo', false)
            ->assertJsonPath('codigo', 'observacion');
    }

    #[Test]
    public function rechaza_crear_activo_si_suma_supera_cien(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'nombre' => 'Observación extra',
                'peso' => 40,
                'activo' => true,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['peso']);

        $this->assertDatabaseMissing('componentes_calificacion_nivel', [
            'anio_escolar' => self::ANIO,
            'nivel' => 'inicial',
            'codigo' => 'observacion_extra',
        ]);
    }

    #[Test]
    public function permite_bajar_unico_activo_de_100_a_80(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $response = $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$cuaderno->id}", ['peso' => 80])
            ->assertOk();

        $this->assertEqualsWithDelta(80.0, (float) $response->json('peso'), 0.01);
        $this->assertEqualsWithDelta(80.0, (float) $cuaderno->fresh()->peso, 0.01);
    }

    #[Test]
    public function por_nivel_indica_configuracion_incompleta_cuando_suma_80(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$cuaderno->id}", ['peso' => 80])
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/componentes-calificacion/por-nivel/inicial?anio_escolar='.self::ANIO)
            ->assertOk()
            ->assertJsonPath('validacion.valido', false)
            ->assertJsonPath('validacion.completo', false)
            ->assertJsonPath('validacion.suma', 80)
            ->assertJsonPath('validacion.faltante', 20)
            ->assertJsonPath('validacion.excede', 0);
    }

    #[Test]
    public function permite_completar_configuracion_con_segundo_componente_de_20(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$cuaderno->id}", ['peso' => 80])
            ->assertOk();

        $response = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'nombre' => 'Observación',
                'peso' => 20,
                'activo' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('activo', true);

        $this->assertEqualsWithDelta(20.0, (float) $response->json('peso'), 0.01);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/componentes-calificacion/por-nivel/inicial?anio_escolar='.self::ANIO)
            ->assertOk()
            ->assertJsonPath('validacion.valido', true)
            ->assertJsonPath('validacion.suma', 100);
    }

    #[Test]
    public function rechaza_peso_si_suma_supera_100(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'nombre' => 'Libro transitorio',
                'peso' => 30,
                'activo' => false,
            ])
            ->assertCreated();

        $libro = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'libro_transitorio')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$libro->id}/reactivar", ['peso' => 30])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['peso']);
    }

    #[Test]
    public function validar_suma_activos_exige_configuracion_completa(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$cuaderno->id}", ['peso' => 80])
            ->assertOk();

        $this->expectException(ValidationException::class);

        (new ComponenteCalificacionNivelService)->validarSumaActivos(self::ANIO, 'inicial');
    }

    #[Test]
    public function puede_crear_y_ajustar_pesos_para_suma_cien(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'nombre' => 'Observación',
                'peso' => 40,
                'activo' => false,
            ])
            ->assertCreated();

        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $observacion = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'observacion')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion/reordenar', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
                'ordenes' => [
                    [
                        'id' => $cuaderno->id,
                        'orden' => 1,
                        'peso' => 60,
                        'activo' => true,
                    ],
                    [
                        'id' => $observacion->id,
                        'orden' => 2,
                        'peso' => 40,
                        'activo' => true,
                    ],
                ],
            ])
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/componentes-calificacion/validar-suma?'.http_build_query([
                'anio_escolar' => self::ANIO,
                'nivel' => 'inicial',
            ]))
            ->assertOk()
            ->assertJsonPath('valido', true)
            ->assertJsonPath('suma', 100);
    }

    #[Test]
    public function rechaza_duplicado_codigo_y_nombre(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'nombre' => 'Duplicado',
                'codigo' => 'cuaderno',
                'peso' => 0,
                'activo' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['codigo']);

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion', [
                'anio_escolar' => self::ANIO,
                'nivel' => 'primaria',
                'nombre' => 'Cuaderno',
                'peso' => 0,
                'activo' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    #[Test]
    public function desactivar_redistribuye_pesos_restantes(): void
    {
        $libro = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'primaria')
            ->where('codigo', 'libro')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$libro->id}/desactivar")
            ->assertOk()
            ->assertJsonPath('componente.activo', false)
            ->assertJsonPath('validacion.valido', true)
            ->assertJsonPath('validacion.suma', 100);

        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'primaria')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();
        $tarea = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'primaria')
            ->where('codigo', 'tarea')
            ->firstOrFail();

        $this->assertEqualsWithDelta(50.0, (float) $cuaderno->fresh()->peso, 0.01);
        $this->assertEqualsWithDelta(50.0, (float) $tarea->fresh()->peso, 0.01);
    }

    #[Test]
    public function reactivar_requiere_peso_mayor_a_cero(): void
    {
        $libro = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'primaria')
            ->where('codigo', 'libro')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$libro->id}/desactivar")
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$libro->id}/reactivar", ['peso' => 0])
            ->assertStatus(422);
    }

    #[Test]
    public function no_permite_desactivar_ultimo_componente_activo(): void
    {
        $cuaderno = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', self::ANIO)
            ->where('nivel', 'inicial')
            ->where('codigo', 'cuaderno')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/componentes-calificacion/{$cuaderno->id}/desactivar")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activo']);
    }

    #[Test]
    public function asegurar_defaults_es_idempotente(): void
    {
        $antes = ComponenteCalificacionNivel::query()->where('anio_escolar', self::ANIO)->count();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/componentes-calificacion/asegurar-defaults', [
                'anio_escolar' => self::ANIO,
            ])
            ->assertOk()
            ->assertJsonPath('creados_por_nivel.inicial', 0)
            ->assertJsonPath('creados_por_nivel.primaria', 0);

        $this->assertSame($antes, ComponenteCalificacionNivel::query()->where('anio_escolar', self::ANIO)->count());
    }

    #[Test]
    public function docente_no_puede_gestionar_componentes(): void
    {
        $this->actingAs($this->docente())
            ->getJson('/api/curricular/componentes-calificacion?anio_escolar='.self::ANIO.'&nivel=primaria')
            ->assertForbidden();
    }

    #[Test]
    public function servicio_asegurar_defaults_crea_anio_nuevo(): void
    {
        $creados = (new ComponenteCalificacionNivelService)->asegurarDefaults('2027');

        $this->assertSame(1, $creados['inicial']);
        $this->assertSame(3, $creados['primaria']);
        $this->assertSame(3, $creados['secundaria']);
    }
}
