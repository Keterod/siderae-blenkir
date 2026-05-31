<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\SeccionAula;
use PHPUnit\Framework\Attributes\Test;

class SeccionesAulasTest extends CurricularApiTestCase
{
    #[Test]
    public function seeder_crea_secciones_inicial_primaria_y_secundaria(): void
    {
        $inicial = SeccionAula::query()
            ->where('nivel', 'inicial')
            ->where('grado', '3 años')
            ->where('activo', true)
            ->orderBy('orden')
            ->pluck('nombre')
            ->all();

        $this->assertSame(
            ['ARDILLITAS', 'ESTRELLITAS DE MAR', 'PULPITOS', 'TIBURONCITOS'],
            $inicial,
        );

        $primaria1ro = SeccionAula::query()
            ->where('nivel', 'primaria')
            ->where('grado', '1ro')
            ->where('activo', true)
            ->count();

        $this->assertSame(5, $primaria1ro);

        $primaria5to = SeccionAula::query()
            ->where('nivel', 'primaria')
            ->where('grado', '5to')
            ->where('activo', true)
            ->pluck('nombre')
            ->all();

        $this->assertSame(['AMISTAD', 'AMOR', 'BONDAD', 'RESPETO'], $primaria5to);

        $secundaria = SeccionAula::query()
            ->where('nivel', 'secundaria')
            ->where('grado', '3ro')
            ->where('activo', true)
            ->pluck('nombre')
            ->all();

        $this->assertSame(['BASICO', 'CICLADO', 'PRE U', 'SELECCION'], $secundaria);
    }

    #[Test]
    public function coordinador_puede_listar_secciones(): void
    {
        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
            ]))
            ->assertOk();

        $this->assertGreaterThanOrEqual(4, count($response->json()));
        $this->assertSame('ARDILLITAS', $response->json('0.nombre'));
    }

    #[Test]
    public function administrador_puede_listar_secciones(): void
    {
        $this->actingAs($this->administrador())
            ->getJson('/api/curricular/secciones-aulas?nivel=primaria&grado=1ro')
            ->assertOk()
            ->assertJsonCount(5);
    }

    #[Test]
    public function docente_puede_listar_pero_no_gestionar_secciones(): void
    {
        $this->actingAs($this->docente())
            ->getJson('/api/curricular/secciones-aulas?nivel=primaria&grado=1ro')
            ->assertOk()
            ->assertJsonCount(5);

        $this->actingAs($this->docente())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '1ro',
                'nombre' => 'NUEVA',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function puede_crear_seccion(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '1ro',
                'nombre' => 'SOLIDARIDAD',
                'orden' => 10,
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'SOLIDARIDAD')
            ->assertJsonPath('activo', true)
            ->assertJsonPath('codigo', 'solidaridad');

        $this->assertDatabaseHas('secciones_aulas', [
            'nivel' => 'primaria',
            'grado' => '1ro',
            'nombre' => 'SOLIDARIDAD',
            'activo' => true,
        ]);
    }

    #[Test]
    public function crear_seccion_sin_codigo_genera_codigo_desde_nombre(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '2do',
                'nombre' => 'Estrellas Brillantes',
                'orden' => 7,
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'Estrellas Brillantes')
            ->assertJsonPath('codigo', 'estrellas_brillantes')
            ->assertJsonPath('orden', 7);

        $this->assertDatabaseHas('secciones_aulas', [
            'nivel' => 'primaria',
            'grado' => '2do',
            'nombre' => 'Estrellas Brillantes',
            'codigo' => 'estrellas_brillantes',
        ]);
    }

    #[Test]
    public function listado_incluye_codigo_interno_en_respuesta_json(): void
    {
        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
            ]))
            ->assertOk();

        $primera = $response->json('0');
        $this->assertSame('ARDILLITAS', $primera['nombre']);
        $this->assertSame('ardillitas', $primera['codigo']);
    }

    #[Test]
    public function rechaza_duplicado_mismo_nivel_grado_y_nombre(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'inicial',
                'grado' => '3 años',
                'nombre' => 'ARDILLITAS',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    #[Test]
    public function permite_mismo_nombre_en_diferente_grado(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '2do',
                'nombre' => 'AULA TRANSVERSAL',
            ])
            ->assertCreated();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '3ro',
                'nombre' => 'AULA TRANSVERSAL',
            ])
            ->assertCreated();
    }

    #[Test]
    public function puede_editar_seccion(): void
    {
        $seccion = SeccionAula::query()
            ->where('nivel', 'inicial')
            ->where('grado', '3 años')
            ->where('nombre', 'PULPITOS')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$seccion->id}", [
                'nombre' => 'PULPITOS AZULES',
                'orden' => 99,
            ])
            ->assertOk()
            ->assertJsonPath('nombre', 'PULPITOS AZULES')
            ->assertJsonPath('codigo', 'pulpitos_azules')
            ->assertJsonPath('orden', 99);
    }

    #[Test]
    public function editar_nombre_sin_codigo_actualiza_codigo_desde_nombre(): void
    {
        $seccion = SeccionAula::query()
            ->where('nivel', 'primaria')
            ->where('grado', '3ro')
            ->where('nombre', 'AMISTAD')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$seccion->id}", [
                'nombre' => 'Amistad Unida',
            ])
            ->assertOk()
            ->assertJsonPath('nombre', 'Amistad Unida')
            ->assertJsonPath('codigo', 'amistad_unida');

        $this->assertDatabaseHas('secciones_aulas', [
            'id' => $seccion->id,
            'nombre' => 'Amistad Unida',
            'codigo' => 'amistad_unida',
        ]);
    }

    #[Test]
    public function puede_desactivar_y_reactivar_seccion(): void
    {
        $seccion = SeccionAula::query()
            ->where('nivel', 'inicial')
            ->where('grado', '3 años')
            ->where('nombre', 'TIBURONCITOS')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$seccion->id}/desactivar")
            ->assertOk()
            ->assertJsonPath('activo', false);

        $this->assertDatabaseHas('secciones_aulas', [
            'id' => $seccion->id,
            'activo' => false,
        ]);

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$seccion->id}/reactivar")
            ->assertOk()
            ->assertJsonPath('activo', true);
    }

    #[Test]
    public function no_permite_reactivar_si_genera_duplicado_activo(): void
    {
        SeccionAula::query()
            ->where('nivel', 'primaria')
            ->where('grado', '1ro')
            ->where('nombre', 'AMISTAD')
            ->firstOrFail();

        $inactiva = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/secciones-aulas', [
                'nivel' => 'primaria',
                'grado' => '1ro',
                'nombre' => 'TEMPORAL REACTIVAR',
                'activo' => false,
            ])
            ->assertCreated()
            ->json('id');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$inactiva}", [
                'nombre' => 'AMISTAD',
            ])
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$inactiva}/reactivar")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);

        $this->assertDatabaseHas('secciones_aulas', [
            'id' => $inactiva,
            'activo' => false,
        ]);
    }

    #[Test]
    public function acepta_incluir_inactivas_como_cero_o_string_false(): void
    {
        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
                'incluir_inactivas' => 0,
            ]))
            ->assertOk()
            ->assertJsonCount(4);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
                'incluir_inactivas' => 'false',
            ]))
            ->assertOk()
            ->assertJsonCount(4);
    }

    #[Test]
    public function listado_inicial_3_anos_incluye_secciones_sembradas(): void
    {
        $response = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
            ]))
            ->assertOk();

        $nombres = collect($response->json())->pluck('nombre')->all();

        $this->assertSame(
            ['ARDILLITAS', 'ESTRELLITAS DE MAR', 'PULPITOS', 'TIBURONCITOS'],
            $nombres,
        );
    }

    #[Test]
    public function filtros_por_nivel_grado_y_activo(): void
    {
        $seccion = SeccionAula::query()
            ->where('nivel', 'secundaria')
            ->where('grado', '2do')
            ->where('nombre', 'BASICO')
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/secciones-aulas/{$seccion->id}/desactivar")
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'secundaria',
                'grado' => '2do',
                'activo' => false,
                'incluir_inactivas' => true,
            ]))
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'BASICO', 'activo' => false]);

        $activas = $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'secundaria',
                'grado' => '2do',
                'activo' => true,
            ]))
            ->assertOk()
            ->json();

        $this->assertFalse(collect($activas)->contains(fn ($fila) => $fila['nombre'] === 'BASICO'));
    }

    #[Test]
    public function filtra_por_busqueda_q(): void
    {
        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/secciones-aulas?'.http_build_query([
                'nivel' => 'inicial',
                'grado' => '3 años',
                'q' => 'ESTRELL',
            ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.nombre', 'ESTRELLITAS DE MAR');
    }
}
