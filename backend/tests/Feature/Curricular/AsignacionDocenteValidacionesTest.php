<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\AnioEscolar;
use App\Models\Curricular\MallaCurso;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class AsignacionDocenteValidacionesTest extends CurricularApiTestCase
{
    /**
     * @return array{0: MallaCurso, 1: MallaCurso}
     */
    private function prepararDosCursosMalla(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $cursos = MallaCurso::query()->where('activo', true)->orderBy('id')->take(2)->get();
        if ($cursos->count() < 2) {
            $this->fail('Se requieren al menos dos cursos activos en la malla de prueba.');
        }

        return [$cursos[0], $cursos[1]];
    }

    #[Test]
    public function bulk_rechaza_docente_inactivo(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $docente->activo = false;
        $docente->save();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_ids' => [$mallaCurso1->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['docente_id']);
    }

    #[Test]
    public function bulk_rechaza_anio_distinto_al_activo(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => '2025',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_ids' => [$mallaCurso1->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio_escolar']);
    }

    #[Test]
    public function bulk_rechaza_si_no_hay_anio_escolar_activo(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        AnioEscolar::query()->update(['es_activo' => false, 'estado' => 'inactivo']);

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_ids' => [$mallaCurso1->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio_escolar']);
    }

    #[Test]
    public function store_rechaza_anio_distinto_al_activo(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente', [
                'user_id' => $docente->id,
                'anio_escolar' => '2025',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_id' => $mallaCurso1->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio_escolar']);
    }

    #[Test]
    public function listado_docentes_excluye_docentes_inactivos(): void
    {
        $activo = $this->usuarioDocenteAsignable('Docente Activo Asignacion');
        $inactivo = User::factory()->create([
            'name' => 'Docente Inactivo Asignacion',
            'activo' => false,
        ]);
        $inactivo->assignRole('docente');

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/docentes?anio_escolar=2026')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Docente Activo Asignacion'])
            ->assertJsonMissing(['name' => 'Docente Inactivo Asignacion']);
    }

    #[Test]
    public function bulk_sigue_asignando_varios_cursos_con_anio_activo(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'asignaciones');
    }

    #[Test]
    public function bulk_desmarca_sigue_desactivando_asignaciones_propias(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $base = [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ];

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, [
                'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
            ]))
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, [
                'malla_curso_ids' => [$mallaCurso2->id],
            ]))
            ->assertOk();

        $this->assertDatabaseHas('docente_curso_aulas', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso1->id,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('docente_curso_aulas', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso2->id,
            'activo' => true,
        ]);
    }

    #[Test]
    public function bulk_sigue_bloqueando_conflicto_con_otro_docente_activo(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente1 = $this->usuarioDocenteAsignable('Docente Conflicto Uno');
        $docente2 = $this->usuarioDocenteAsignable('Docente Conflicto Dos');
        $base = [
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id],
        ];

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, ['docente_id' => $docente1->id]))
            ->assertOk();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, ['docente_id' => $docente2->id]))
            ->assertStatus(422);
    }
}
