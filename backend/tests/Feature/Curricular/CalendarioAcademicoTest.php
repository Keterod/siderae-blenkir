<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\AnioEscolar;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class CalendarioAcademicoTest extends CurricularApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'gestionar_calendario_academico', 'guard_name' => 'web']);
    }

    protected function usuarioCalendario(): User
    {
        return $this->userWithPermissions(['gestionar_calendario_academico', 'ver_malla_curricular']);
    }

    #[Test]
    public function puede_crear_anio_escolar_y_generar_bimestres(): void
    {
        $response = $this->actingAs($this->usuarioCalendario())->postJson('/api/curricular/anios-escolares', [
            'anio' => '2027',
            'nombre' => 'Año escolar 2027',
            'generar_bimestres' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('anio', '2027')
            ->assertJsonCount(4, 'periodos');

        $this->assertDatabaseHas('anios_escolares', ['anio' => '2027', 'estado' => 'inactivo', 'es_activo' => false]);
        $this->assertSame(4, PeriodoAcademico::query()->where('anio_escolar', '2027')->count());
    }

    #[Test]
    public function activar_anio_garantiza_unico_activo(): void
    {
        $user = $this->usuarioCalendario();

        $this->actingAs($user)->postJson('/api/curricular/anios-escolares', [
            'anio' => '2027',
            'nombre' => 'Año 2027',
            'generar_bimestres' => true,
        ])->assertCreated();

        $anio2026 = AnioEscolar::query()->where('anio', '2026')->firstOrFail();
        $anio2027 = AnioEscolar::query()->where('anio', '2027')->firstOrFail();

        $this->actingAs($user)->postJson("/api/curricular/anios-escolares/{$anio2027->id}/activar")
            ->assertOk()
            ->assertJsonPath('es_activo', true);

        $anio2026->refresh();
        $anio2027->refresh();

        $this->assertFalse($anio2026->es_activo);
        $this->assertTrue($anio2027->es_activo);
        $this->assertSame(1, AnioEscolar::query()->where('es_activo', true)->count());
    }

    #[Test]
    public function marcar_bimestre_vigente_garantiza_unico_por_anio(): void
    {
        $user = $this->usuarioCalendario();
        $anio = AnioEscolar::query()->where('anio', '2026')->firstOrFail();

        $periodo1 = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $periodo2 = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '2')->firstOrFail();

        $this->actingAs($user)->postJson("/api/curricular/periodos-academicos/{$periodo2->id}/marcar-vigente")
            ->assertOk()
            ->assertJsonPath('es_vigente', true);

        $periodo1->refresh();
        $periodo2->refresh();

        $this->assertFalse($periodo1->es_vigente);
        $this->assertTrue($periodo2->es_vigente);
        $this->assertSame(1, PeriodoAcademico::query()->where('anio_escolar', '2026')->where('es_vigente', true)->count());
    }

    #[Test]
    public function generar_semanas_crea_semanas_planificadas(): void
    {
        $user = $this->usuarioCalendario();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '3')->firstOrFail();
        SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->delete();

        $this->actingAs($user)->postJson("/api/curricular/periodos-academicos/{$periodo->id}/generar-semanas")
            ->assertOk()
            ->assertJsonCount(4, 'semanas');
    }

    #[Test]
    public function cerrar_bimestre_actualiza_estado(): void
    {
        $user = $this->usuarioCalendario();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '4')->firstOrFail();

        $this->actingAs($user)->postJson("/api/curricular/periodos-academicos/{$periodo->id}/cerrar")
            ->assertOk()
            ->assertJsonPath('estado', 'cerrado')
            ->assertJsonPath('es_vigente', false);
    }

    #[Test]
    public function rechaza_rango_fechas_invalido_en_bimestre(): void
    {
        $user = $this->usuarioCalendario();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '2')->firstOrFail();

        $this->actingAs($user)->patchJson("/api/curricular/periodos-academicos/{$periodo->id}", [
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-05-01',
        ])->assertUnprocessable();
    }

    #[Test]
    public function rechaza_solapamiento_de_bimestres(): void
    {
        $user = $this->usuarioCalendario();
        $periodo1 = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $periodo2 = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '2')->firstOrFail();

        $this->actingAs($user)->patchJson("/api/curricular/periodos-academicos/{$periodo1->id}", [
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-04-30',
        ])->assertOk();

        $this->actingAs($user)->patchJson("/api/curricular/periodos-academicos/{$periodo2->id}", [
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-05-31',
        ])->assertUnprocessable();
    }

    #[Test]
    public function docente_sin_permiso_no_puede_gestionar_calendario(): void
    {
        $this->actingAs($this->docente())->getJson('/api/curricular/anios-escolares')
            ->assertForbidden();
    }

    #[Test]
    public function usuario_autenticado_puede_consultar_anio_activo(): void
    {
        $this->actingAs($this->docente())->getJson('/api/curricular/anios-escolares/activo')
            ->assertOk()
            ->assertJsonPath('anio_escolar.anio', '2026')
            ->assertJsonPath('periodo_vigente.bimestre', '1');
    }

    #[Test]
    public function seeder_demo_crea_anio_2026_activo_con_bimestres_y_semanas(): void
    {
        $anio = AnioEscolar::query()->where('anio', '2026')->first();
        $this->assertNotNull($anio);
        $this->assertTrue($anio->es_activo);

        $periodos = PeriodoAcademico::query()->where('anio_escolar', '2026')->get();
        $this->assertCount(4, $periodos);

        foreach ($periodos as $periodo) {
            $this->assertSame(4, SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->count());
            $this->assertSame($anio->id, $periodo->anio_escolar_id);
        }
    }
}
