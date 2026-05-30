<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\CatalogoNivelGrado;
use PHPUnit\Framework\Attributes\Test;

class CompetenciaCapacidadCrudTest extends CurricularApiTestCase
{
    #[Test]
    public function coordinador_puede_crear_competencia(): void
    {
        $area = $this->areaComunicacionPrimaria();

        $this->actingAs($this->coordinador())
            ->postJson("/api/curricular/areas/{$area->id}/competencias", [
                'nombre' => 'Competencia institucional prueba',
                'descripcion' => 'Descripción de prueba',
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'Competencia institucional prueba')
            ->assertJsonPath('activo', true);
    }

    #[Test]
    public function administrador_puede_crear_competencia(): void
    {
        $area = $this->areaComunicacionPrimaria();

        $this->actingAs($this->administrador())
            ->postJson("/api/curricular/areas/{$area->id}/competencias", [
                'nombre' => 'Competencia admin prueba',
            ])
            ->assertCreated();
    }

    #[Test]
    public function docente_no_puede_crear_competencia(): void
    {
        $area = $this->areaComunicacionPrimaria();

        $this->actingAs($this->docente())
            ->postJson("/api/curricular/areas/{$area->id}/competencias", [
                'nombre' => 'Intento docente',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function rechaza_duplicado_competencia_misma_area(): void
    {
        $area = $this->areaComunicacionPrimaria();
        $user = $this->coordinador();

        $this->actingAs($user)->postJson("/api/curricular/areas/{$area->id}/competencias", [
            'nombre' => 'Duplicada competencia',
        ])->assertCreated();

        $this->actingAs($user)->postJson("/api/curricular/areas/{$area->id}/competencias", [
            'nombre' => '  duplicada   competencia  ',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    #[Test]
    public function crea_y_rechaza_duplicado_capacidad(): void
    {
        $competencia = $this->competenciaComunicacion();
        $user = $this->coordinador();

        $this->actingAs($user)->postJson("/api/curricular/competencias/{$competencia->id}/capacidades", [
            'nombre' => 'Capacidad institucional X',
        ])->assertCreated();

        $this->actingAs($user)->postJson("/api/curricular/competencias/{$competencia->id}/capacidades", [
            'nombre' => 'capacidad institucional x',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    #[Test]
    public function desactiva_capacidad_sin_uso(): void
    {
        $competencia = $this->competenciaComunicacion();
        $response = $this->actingAs($this->coordinador())
            ->postJson("/api/curricular/competencias/{$competencia->id}/capacidades", [
                'nombre' => 'Cap sin uso',
            ])
            ->assertCreated();

        $capacidadId = $response->json('id');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/capacidades/{$capacidadId}/desactivar")
            ->assertOk()
            ->assertJsonPath('activo', false);
    }

    #[Test]
    public function bloquea_desactivar_capacidad_con_tema_activo(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio bloqueo capacidad',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->assertCreated();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/capacidades/{$capacidad->id}/desactivar")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['capacidad']);
    }

    #[Test]
    public function bloquea_desactivar_competencia_con_tema_activo(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio bloqueo competencia',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->assertCreated();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/competencias/{$competencia->id}/desactivar")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['competencia']);
    }

    #[Test]
    public function reactiva_competencia_y_capacidad(): void
    {
        $competencia = $this->competenciaComunicacion();
        $capResponse = $this->actingAs($this->coordinador())
            ->postJson("/api/curricular/competencias/{$competencia->id}/capacidades", [
                'nombre' => 'Cap reactivar',
            ])
            ->assertCreated();
        $capacidadId = $capResponse->json('id');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/capacidades/{$capacidadId}/desactivar")
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/capacidades/{$capacidadId}/reactivar")
            ->assertOk()
            ->assertJsonPath('activo', true);

        $nueva = $this->actingAs($this->coordinador())
            ->postJson("/api/curricular/areas/{$competencia->area_id}/competencias", [
                'nombre' => 'Comp reactivar test',
            ])
            ->assertCreated()
            ->json('id');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/competencias/{$nueva}/desactivar")
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/competencias/{$nueva}/reactivar")
            ->assertOk()
            ->assertJsonPath('activo', true);
    }

    #[Test]
    public function get_competencias_default_solo_activas(): void
    {
        $area = $this->areaComunicacionPrimaria();
        $competencia = Competencia::query()->where('area_id', $area->id)->where('activo', true)->firstOrFail();
        $competencia->update(['activo' => false]);

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/areas/{$area->id}/competencias");

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertFalse($ids->contains($competencia->id));
    }

    #[Test]
    public function get_activo_all_requiere_permiso_gestion(): void
    {
        $area = $this->areaComunicacionPrimaria();

        $this->actingAs($this->docente())
            ->getJson("/api/curricular/areas/{$area->id}/competencias?activo=all")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activo']);

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/areas/{$area->id}/competencias?activo=all&conteo_uso=1")
            ->assertOk();
    }

    private function areaComunicacionPrimaria(): Area
    {
        return Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Comunicación')
            ->firstOrFail();
    }

    private function competenciaComunicacion(): Competencia
    {
        $area = $this->areaComunicacionPrimaria();

        return Competencia::query()->where('area_id', $area->id)->where('activo', true)->firstOrFail();
    }

    /**
     * @return array{0: \App\Models\Curricular\MallaCurso, 1: PeriodoAcademico, 2: SemanaAcademica}
     */
    private function prepararMallaYTemaSlotComunicacion(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $areaComunicacion = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Comunicación')
            ->firstOrFail();

        $mallaCurso = \App\Models\Curricular\MallaCurso::query()
            ->where('area_id', $areaComunicacion->id)
            ->where('activo', true)
            ->firstOrFail();

        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $semana = SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();

        return [$mallaCurso, $periodo, $semana];
    }
}
