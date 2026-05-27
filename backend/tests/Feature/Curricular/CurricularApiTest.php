<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\CursoCatalogo;
use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\NotaSemanalBulkService;
use PHPUnit\Framework\Attributes\Test;

class CurricularApiTest extends CurricularApiTestCase
{
    #[Test]
    public function mallas_requiere_autenticacion(): void
    {
        $this->getJson('/api/curricular/mallas')->assertUnauthorized();
    }

    #[Test]
    public function mallas_requiere_permiso(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/curricular/mallas')
            ->assertForbidden();
    }

    #[Test]
    public function puede_cargar_plantilla_primaria_segundo(): void
    {
        $response = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/mallas/cargar-plantilla', [
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
            ]);

        $response->assertCreated();
        $this->assertGreaterThanOrEqual(16, MallaCurso::query()->count());
    }

    #[Test]
    public function no_duplica_malla_anio_nivel_grado(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->postJson('/api/curricular/mallas/cargar-plantilla', [
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/curricular/mallas/cargar-plantilla', [
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
        ])->assertStatus(422);
    }

    #[Test]
    public function crea_tema_con_competencia_y_capacidad_validas(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();

        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/temas', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'semana_academica_id' => $semana->id,
                'titulo' => 'Tema 1',
                'competencia_ids' => [$competencia->id],
                'capacidad_ids' => [$capacidad->id],
            ])
            ->assertCreated();
    }

    #[Test]
    public function rechaza_tema_sin_competencia(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();
        $capacidad = Capacidad::query()->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/temas', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'semana_academica_id' => $semana->id,
                'titulo' => 'Tema inválido',
                'competencia_ids' => [],
                'capacidad_ids' => [$capacidad->id],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function rechaza_tema_sin_capacidad(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/temas', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'semana_academica_id' => $semana->id,
                'titulo' => 'Tema inválido',
                'competencia_ids' => [$competencia->id],
                'capacidad_ids' => [],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function rechaza_tema_duplicado_activo(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();
        $payload = [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ];

        $user = $this->coordinador();
        $this->actingAs($user)->postJson('/api/curricular/temas', $payload)->assertCreated();
        $this->actingAs($user)->postJson('/api/curricular/temas', $payload)->assertStatus(422);
    }

    #[Test]
    public function permite_varios_criterios_mismo_curso_bimestre_semana(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();
        $base = [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ];

        $user = $this->coordinador();
        $this->actingAs($user)->postJson('/api/curricular/temas', array_merge($base, ['titulo' => 'Las plantas y sus partes']))->assertCreated();
        $this->actingAs($user)->postJson('/api/curricular/temas', array_merge($base, ['titulo' => 'La raíz']))->assertCreated();

        $this->assertSame(2, TemaSemanal::query()->where('malla_curso_id', $mallaCurso->id)->where('activo', true)->count());
    }

    #[Test]
    public function permite_criterio_sin_semana_referencial(): void
    {
        [$mallaCurso, $periodo] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $response = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'titulo' => 'Fotosíntesis',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ]);

        $response->assertCreated();
        $this->assertNull(TemaSemanal::query()->find($response->json('id'))->semana_academica_id);
    }

    #[Test]
    public function desactiva_tema_y_permite_nuevo_activo(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $user = $this->coordinador();
        $tema = $this->actingAs($user)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema anterior',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->assertCreated()->json('id');

        $this->actingAs($user)->patchJson("/api/curricular/temas/{$tema}/desactivar")->assertOk();

        $this->actingAs($user)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema nuevo',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->assertCreated();
    }

    #[Test]
    public function configura_pesos_validos(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/pesos', [
                'peso_cuaderno' => 50,
                'peso_libro' => 30,
                'peso_tarea' => 20,
            ])
            ->assertCreated();
    }

    #[Test]
    public function rechaza_pesos_suma_distinta_de_cien(): void
    {
        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/pesos', [
                'peso_cuaderno' => 40,
                'peso_libro' => 40,
                'peso_tarea' => 10,
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function crea_asignacion_docente_valida(): void
    {
        [$mallaCurso] = $this->prepararMallaYTemaSlot();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente', [
                'user_id' => $docente->id,
                'malla_curso_id' => $mallaCurso->id,
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
            ])
            ->assertCreated();
    }

    #[Test]
    public function rechaza_doble_asignacion_activa(): void
    {
        [$mallaCurso] = $this->prepararMallaYTemaSlot();
        $docente1 = $this->usuarioDocenteAsignable();
        $docente2 = $this->usuarioDocenteAsignable();
        $payload = [
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ];

        $user = $this->coordinador();
        $this->actingAs($user)->postJson('/api/curricular/asignaciones-docente', array_merge($payload, ['user_id' => $docente1->id]))->assertCreated();
        $this->actingAs($user)->postJson('/api/curricular/asignaciones-docente', array_merge($payload, ['user_id' => $docente2->id]))->assertStatus(422);
    }

    #[Test]
    public function desactiva_asignacion_y_permite_nueva_activa(): void
    {
        [$mallaCurso] = $this->prepararMallaYTemaSlot();
        $docente1 = $this->usuarioDocenteAsignable();
        $docente2 = $this->usuarioDocenteAsignable();
        $payload = [
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ];

        $user = $this->coordinador();
        $id = $this->actingAs($user)->postJson('/api/curricular/asignaciones-docente', array_merge($payload, ['user_id' => $docente1->id]))->json('id');
        $this->actingAs($user)->patchJson("/api/curricular/asignaciones-docente/{$id}/desactivar")->assertOk();

        $this->actingAs($user)->postJson('/api/curricular/asignaciones-docente', array_merge($payload, ['user_id' => $docente2->id]))->assertCreated();
    }

    #[Test]
    public function lista_docentes_con_rol_docente(): void
    {
        $docenteA = $this->usuarioDocenteAsignable('Ana Docente');
        $docenteB = $this->usuarioDocenteAsignable('Bruno Docente');
        User::factory()->create(['name' => 'Sin Rol']);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/docentes?anio_escolar=2026&nivel=primaria&sede=chilca')
            ->assertOk()
            ->assertJsonFragment(['id' => $docenteA->id, 'name' => 'Ana Docente'])
            ->assertJsonFragment(['id' => $docenteB->id, 'name' => 'Bruno Docente'])
            ->assertJsonMissing(['name' => 'Sin Rol']);
    }

    #[Test]
    public function busca_docentes_por_nombre_o_correo(): void
    {
        $docente = User::factory()->create([
            'name' => 'Carolina Rivas',
            'email' => 'carolina.rivas@siderae.test',
        ]);
        $docente->assignRole('docente');

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/docentes?search=Carolina')
            ->assertOk()
            ->assertJsonFragment(['email' => 'carolina.rivas@siderae.test']);

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/docentes?search=carolina.rivas')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Carolina Rivas']);
    }

    #[Test]
    public function asigna_varios_cursos_a_docente_en_bloque(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $payload = [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
        ];

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', $payload)
            ->assertOk()
            ->assertJsonCount(2, 'asignaciones');

        $this->assertDatabaseHas('docente_curso_aulas', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso1->id,
            'activo' => true,
        ]);
        $this->assertDatabaseHas('docente_curso_aulas', [
            'user_id' => $docente->id,
            'malla_curso_id' => $mallaCurso2->id,
            'activo' => true,
        ]);
    }

    #[Test]
    public function bulk_mantiene_asignacion_existente_del_mismo_docente(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $coordinador = $this->coordinador();

        $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente/bulk', [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id],
        ])->assertOk();

        $primera = DocenteCursoAula::query()->where('user_id', $docente->id)->where('malla_curso_id', $mallaCurso1->id)->firstOrFail();

        $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente/bulk', [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
        ])->assertOk();

        $this->assertSame($primera->id, DocenteCursoAula::query()
            ->where('user_id', $docente->id)
            ->where('malla_curso_id', $mallaCurso1->id)
            ->where('activo', true)
            ->value('id'));
    }

    #[Test]
    public function bulk_desmarca_curso_propio_y_lo_desactiva(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $coordinador = $this->coordinador();
        $base = [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ];

        $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, [
            'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
        ]))->assertOk();

        $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, [
            'malla_curso_ids' => [$mallaCurso2->id],
        ]))->assertOk();

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
    public function bulk_no_permite_asignar_curso_activo_de_otro_docente(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente1 = $this->usuarioDocenteAsignable('Docente Uno');
        $docente2 = $this->usuarioDocenteAsignable('Docente Dos');
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

        $response = $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/asignaciones-docente/bulk', array_merge($base, ['docente_id' => $docente2->id]));

        $response->assertStatus(422);
        $this->assertStringContainsString('Docente Uno', (string) $response->json('message'));
    }

    #[Test]
    public function bulk_no_permite_asignar_curso_inactivo(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $mallaCurso2->update(['activo' => false]);
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
            ->assertStatus(422)
            ->assertJsonValidationErrors(['malla_curso_ids']);
    }

    #[Test]
    public function usuario_docente_no_puede_asignar(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->docente())
            ->postJson('/api/curricular/asignaciones-docente/bulk', [
                'docente_id' => $docente->id,
                'anio_escolar' => '2026',
                'nivel' => 'primaria',
                'grado' => '2do',
                'seccion' => 'A',
                'sede' => 'chilca',
                'malla_curso_ids' => [$mallaCurso1->id],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function coordinador_puede_asignar_en_bloque(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

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
            ->assertOk();
    }

    #[Test]
    public function devuelve_resumen_de_asignaciones_del_docente(): void
    {
        [$mallaCurso1, $mallaCurso2] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();
        $coordinador = $this->coordinador();

        $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente/bulk', [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id, $mallaCurso2->id],
        ])->assertOk();

        $this->actingAs($coordinador)
            ->getJson("/api/curricular/asignaciones-docente/docente/{$docente->id}?anio_escolar=2026&nivel=primaria&sede=chilca")
            ->assertOk()
            ->assertJsonPath('docente.id', $docente->id)
            ->assertJsonCount(1, 'resumen')
            ->assertJsonPath('resumen.0.grado', '2do')
            ->assertJsonPath('resumen.0.seccion', 'A')
            ->assertJsonCount(2, 'resumen.0.cursos');
    }

    #[Test]
    public function filtra_asignaciones_por_contexto(): void
    {
        [$mallaCurso1] = $this->prepararDosCursosMalla();
        $docente = $this->usuarioDocenteAsignable();

        $this->actingAs($this->coordinador())->postJson('/api/curricular/asignaciones-docente/bulk', [
            'docente_id' => $docente->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_ids' => [$mallaCurso1->id],
        ])->assertOk();

        $this->actingAs($this->coordinador())
            ->getJson('/api/curricular/asignaciones-docente?anio_escolar=2026&nivel=primaria&sede=chilca&grado=2do&seccion=A&activo=true')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.user_id', $docente->id);
    }

    #[Test]
    public function registra_notas_con_clt_y_calcula_ce(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 15,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '15.00');
    }

    #[Test]
    public function registra_notas_solo_cuaderno(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id, 'nota_cuaderno' => 18],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '18.00');
    }

    #[Test]
    public function rechaza_notas_sin_clt(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id],
                ],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function rechaza_nota_fuera_de_rango(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id, 'nota_cuaderno' => 25],
                ],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function coordinador_no_puede_registrar_bulk_notas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id, 'nota_cuaderno' => 14],
                ],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function docente_no_registra_en_curso_no_asignado(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $otroDocente = $this->docente();

        $this->actingAs($otroDocente)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id, 'nota_cuaderno' => 14],
                ],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function administrador_no_puede_bulk_en_asignacion_de_otro_docente(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($this->administrador())
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    ['estudiante_id' => $estudiante->id, 'nota_cuaderno' => 14],
                ],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function consulta_global_coordinador_ve_notas_readonly(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;
        $qs = $this->queryStringConsultaGlobal($asignacion, $periodoId);

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/notas-semanales/formulario?{$qs}")
            ->assertOk()
            ->assertJsonPath('consulta_global', true)
            ->assertJsonPath('readonly', true)
            ->assertJsonPath('asignacion', null);
    }

    #[Test]
    public function consulta_global_directivo_ok(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;
        $qs = $this->queryStringConsultaGlobal($asignacion, $periodoId);

        $this->actingAs($this->directivo())
            ->getJson("/api/curricular/notas-semanales/formulario?{$qs}")
            ->assertOk()
            ->assertJsonPath('consulta_global', true)
            ->assertJsonPath('readonly', true);
    }

    #[Test]
    public function consulta_global_docente_recibe_403(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;
        $qs = $this->queryStringConsultaGlobal($asignacion, $periodoId);

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?{$qs}")
            ->assertForbidden();
    }

    #[Test]
    public function consulta_global_sin_ver_notas_academicas_recibe_403(): void
    {
        $u = $this->userWithPermissions([
            'ver_malla_curricular',
            'gestionar_malla_curricular',
            'gestionar_temas_semanales',
            'configurar_pesos_evaluacion',
            'gestionar_asignaciones_docente',
        ]);

        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;
        $qs = $this->queryStringConsultaGlobal($asignacion, $periodoId);

        $this->actingAs($u)
            ->getJson("/api/curricular/notas-semanales/formulario?{$qs}")
            ->assertForbidden();
    }

    #[Test]
    public function usuario_solo_ver_notas_sin_gestionar_no_lista_contextos_consulta(): void
    {
        $u = $this->userWithPermissions(['ver_notas_academicas']);

        $this->actingAs($u)
            ->getJson('/api/curricular/notas-semanales/contextos-aula')
            ->assertForbidden();
    }

    #[Test]
    public function administrador_lista_contextos_consulta(): void
    {
        $this->prepararFlujoNotas();

        $this->actingAs($this->administrador())
            ->getJson('/api/curricular/notas-semanales/contextos-aula')
            ->assertOk()
            ->assertJsonCount(1);
    }

    #[Test]
    public function formulario_visita_terceros_readonly_cuando_coord(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}")
            ->assertOk()
            ->assertJsonPath('consulta_global', false)
            ->assertJsonPath('readonly', true);
    }

    #[Test]
    public function formulario_propia_asignacion_readonly_false_para_docente(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}")
            ->assertOk()
            ->assertJsonPath('readonly', false)
            ->assertJsonPath('consulta_global', false);
    }

    #[Test]
    public function formulario_devuelve_todos_los_criterios_activos_sin_notas(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}&estudiante_id={$estudiante->id}")
            ->assertOk()
            ->assertJsonCount(2, 'criterios')
            ->assertJsonPath('notas_por_criterio', []);
    }

    #[Test]
    public function formulario_incluye_notas_existentes(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 15,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated();

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}&estudiante_id={$estudiante->id}")
            ->assertOk()
            ->assertJsonPath("notas_por_criterio.{$tema->id}.nota_cuaderno", '15.00')
            ->assertJsonPath("notas_por_criterio.{$tema->id}.ce_calculado", '15.50');
    }

    #[Test]
    public function guardar_notas_semanales_recalcula_promedio_criterios_eval_bim(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 16,
                        'nota_tarea' => 18,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('eval_bim_resultados', [
            'estudiante_id' => $estudiante->id,
            'malla_curso_id' => $asignacion->malla_curso_id,
            'periodo_academico_id' => $periodoId,
            'sede' => $asignacion->sede,
            'grado' => $asignacion->grado,
            'seccion' => $asignacion->seccion,
            'promedio_criterios' => 16.0,
        ]);

        $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]))
            ->assertOk()
            ->assertJsonPath("resultados_por_estudiante.{$estudiante->id}.promedio_criterios", 16);
    }

    #[Test]
    public function guardar_notas_solo_un_criterio_con_ce_promedia_solo_criterios_con_ce(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 12,
                        'nota_libro' => 14,
                    ],
                ],
            ])
            ->assertCreated();

        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('malla_curso_id', $asignacion->malla_curso_id)
            ->where('periodo_academico_id', $periodoId)
            ->first();

        $this->assertNotNull($resultado);
        $this->assertEqualsWithDelta(13.0, (float) $resultado->promedio_criterios, 0.01);
        $this->assertSame(EvalBimEstadoCalculo::Pendiente, $resultado->estado_calculo);
    }

    #[Test]
    public function sin_ce_guardado_formulario_eval_bim_promedio_criterios_null(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/evaluacion-bimestral/formulario?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]))
            ->assertOk()
            ->assertJsonPath("resultados_por_estudiante.{$estudiante->id}", null);
    }

    #[Test]
    public function bulk_por_estudiante_guarda_solo_criterios_con_nota(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    [
                        'tema_semanal_id' => $tema->id,
                        'nota_cuaderno' => 14,
                    ],
                    [
                        'tema_semanal_id' => $tema2->id,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonCount(1, 'notas');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'nota_cuaderno' => '14.00',
        ]);
        $this->assertDatabaseMissing('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema2->id,
        ]);
    }

    #[Test]
    public function formulario_mantiene_criterios_sin_nota_despues_de_guardar(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 12],
                ],
            ])
            ->assertCreated();

        $response = $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}&estudiante_id={$estudiante->id}");

        $response->assertOk()->assertJsonCount(2, 'criterios');
        $ids = collect($response->json('criterios'))->pluck('id')->all();
        $this->assertContains($tema->id, $ids);
        $this->assertContains($tema2->id, $ids);
        $this->assertArrayNotHasKey((string) $tema2->id, $response->json('notas_por_criterio') ?? []);
    }

    #[Test]
    public function formulario_sin_estudiante_id_devuelve_todos_los_estudiantes_del_aula(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $estudiante2 = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}")
            ->assertOk()
            ->assertJsonCount(2, 'estudiantes')
            ->assertJsonPath('notas_por_criterio', [])
            ->assertJsonPath('notas_por_estudiante_criterio', []);
    }

    #[Test]
    public function formulario_sin_estudiante_id_incluye_notas_de_varios_estudiantes(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();
        $periodoId = $tema->periodo_academico_id;

        $estudiante2 = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 15],
                        ],
                    ],
                    [
                        'estudiante_id' => $estudiante2->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema2->id, 'nota_libro' => 14],
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $this->actingAs($asignacion->user)
            ->getJson("/api/curricular/notas-semanales/formulario?asignacion_docente_id={$asignacion->id}&periodo_academico_id={$periodoId}")
            ->assertOk()
            ->assertJsonPath("notas_por_estudiante_criterio.{$estudiante->id}.{$tema->id}.nota_cuaderno", '15.00')
            ->assertJsonPath("notas_por_estudiante_criterio.{$estudiante2->id}.{$tema2->id}.nota_libro", '14.00');
    }

    #[Test]
    public function bulk_multiestudiante_guarda_y_actualiza_notas(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $estudiante2 = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 14, 'nota_tarea' => 16],
                        ],
                    ],
                    [
                        'estudiante_id' => $estudiante2->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id, 'nota_libro' => 13],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'notas');

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'ce_calculado' => '15.00',
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 18],
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'nota_cuaderno' => '18.00',
            'ce_calculado' => '18.00',
        ]);
    }

    #[Test]
    public function bulk_multiestudiante_no_crea_notas_vacias_ni_borra_existentes(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 12],
                ],
            ])
            ->assertCreated();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id],
                            ['tema_semanal_id' => $tema2->id, 'nota_cuaderno' => 11],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonFragment(['advertencias' => [NotaSemanalBulkService::ADVERTENCIA_ELIMINAR_NOTA]]);

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'nota_cuaderno' => '12.00',
        ]);
        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema2->id,
            'nota_cuaderno' => '11.00',
        ]);
    }

    #[Test]
    public function bulk_multiestudiante_rechaza_estudiante_fuera_de_seccion(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $ajeno = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'B',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $ajeno->id,
                        'registros' => [
                            ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 10],
                        ],
                    ],
                ],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function bulk_multiestudiante_rechaza_criterio_de_otro_curso(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        [, $mallaCurso2] = $this->prepararDosCursosMalla();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $semana = SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->where('numero_semana', 1)->firstOrFail();
        $competencia = Competencia::query()->where('area_id', $mallaCurso2->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $temaAjenoId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso2->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Criterio ajeno',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            ['tema_semanal_id' => $temaAjenoId, 'nota_cuaderno' => 10],
                        ],
                    ],
                ],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function bulk_multiestudiante_calcula_ce_por_estudiante_y_criterio(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'registros_por_estudiante' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'registros' => [
                            [
                                'tema_semanal_id' => $tema->id,
                                'nota_cuaderno' => 15,
                                'nota_tarea' => 16,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('notas.0.ce_calculado', '15.50');
    }

    #[Test]
    public function criterios_sin_ce_no_afectan_promedio(): void
    {
        [$asignacion, $tema, $estudiante, $tema2] = $this->prepararFlujoNotasConDosCriterios();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 10, 'nota_tarea' => 14],
                ],
            ])
            ->assertCreated();

        $resumen = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/estudiantes/{$estudiante->id}/resumen-academico")
            ->assertOk()
            ->json();

        $this->assertCount(1, $resumen['ce_por_tema']);
        $this->assertSame(12.0, (float) $resumen['promedios_por_curso'][0]['promedio_ce']);
    }

    #[Test]
    public function docente_solo_ve_sus_cursos_asignados(): void
    {
        [$asignacion] = array_slice($this->prepararFlujoNotas(), 0, 1);

        $this->actingAs($asignacion->user)
            ->getJson('/api/curricular/docente/aulas-cursos?anio_escolar=2026')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $asignacion->id);
    }

    #[Test]
    public function bulk_advierte_si_intenta_borrar_nota_sin_accion_especifica(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 15],
                ],
            ])
            ->assertCreated();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'estudiante_id' => $estudiante->id,
                'registros' => [
                    ['tema_semanal_id' => $tema->id],
                    ['tema_semanal_id' => $tema->id, 'nota_cuaderno' => 16],
                ],
            ])
            ->assertCreated()
            ->assertJsonFragment(['advertencias' => [NotaSemanalBulkService::ADVERTENCIA_ELIMINAR_NOTA]]);

        $this->assertDatabaseHas('notas_semanales', [
            'estudiante_id' => $estudiante->id,
            'tema_semanal_id' => $tema->id,
            'nota_cuaderno' => '16.00',
        ]);
    }

    #[Test]
    public function resumen_academico_devuelve_promedios(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'nota_cuaderno' => 14,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated();

        $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/estudiantes/{$estudiante->id}/resumen-academico")
            ->assertOk()
            ->assertJsonStructure([
                'estudiante_id',
                'ce_por_tema',
                'promedios_por_curso',
                'promedios_por_area',
                'promedios_bimestrales',
            ]);
    }

    #[Test]
    public function malla_grado_provisiona_cursos_institucionales_por_nivel(): void
    {
        $user = $this->coordinador();

        $this->actingAs($user)->getJson('/api/curricular/mallas/grado?anio_escolar=2028&nivel=inicial&grado=3%20a%C3%B1os')
            ->assertOk()
            ->assertJsonPath('nivel', 'inicial');

        $inicialCursos = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q->where('anio_escolar', '2028')->where('nivel', 'inicial'))
            ->count();
        $this->assertGreaterThanOrEqual(9, $inicialCursos);

        $this->actingAs($user)->getJson('/api/curricular/mallas/grado?anio_escolar=2028&nivel=secundaria&grado=2do')
            ->assertOk();

        $secundariaCursos = MallaCurso::query()
            ->whereHas('mallaCurricular', fn ($q) => $q->where('anio_escolar', '2028')->where('nivel', 'secundaria'))
            ->count();
        $this->assertGreaterThanOrEqual(20, $secundariaCursos);
    }

    #[Test]
    public function malla_grado_es_idempotente(): void
    {
        $user = $this->coordinador();
        $url = '/api/curricular/mallas/grado?anio_escolar=2029&nivel=primaria&grado=2do';

        $this->actingAs($user)->getJson($url)->assertOk();
        $this->actingAs($user)->getJson($url)->assertOk();

        $this->assertSame(1, MallaCurricular::query()->where('anio_escolar', '2029')->count());
    }

    #[Test]
    public function puede_agregar_curso_malla_desde_catalogo_por_id(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $malla = $mallaCurso->mallaCurricular;
        $payload = [
            'area_id' => $mallaCurso->area_id,
            'curso_catalogo_id' => $mallaCurso->curso_catalogo_id,
        ];
        $mallaCurso->delete();

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", $payload)
            ->assertCreated()
            ->assertJsonPath('curso_catalogo_id', $payload['curso_catalogo_id'])
            ->assertJsonPath('activo', true);
    }

    #[Test]
    public function rechaza_agregar_curso_duplicado_activo_en_malla(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->where('activo', true)->firstOrFail();
        $malla = $mallaCurso->mallaCurricular;

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $mallaCurso->area_id,
                'curso_catalogo_id' => $mallaCurso->curso_catalogo_id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['curso_catalogo_id']);
    }

    #[Test]
    public function rechaza_agregar_curso_inactivo_y_permite_reactivar(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->where('activo', true)->firstOrFail();
        $malla = $mallaCurso->mallaCurricular;

        $this->actingAs($user)
            ->patchJson("/api/curricular/mallas/{$malla->id}/cursos/{$mallaCurso->id}/desactivar")
            ->assertOk()
            ->assertJsonPath('activo', false);

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $mallaCurso->area_id,
                'curso_catalogo_id' => $mallaCurso->curso_catalogo_id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['curso_catalogo_id']);

        $this->actingAs($user)
            ->patchJson("/api/curricular/mallas/{$malla->id}/cursos/{$mallaCurso->id}/reactivar")
            ->assertOk()
            ->assertJsonPath('activo', true);
    }

    #[Test]
    public function crea_curso_nuevo_por_nombre_y_lo_agrega_a_la_malla(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $area = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Matemática')
            ->firstOrFail();
        $malla = MallaCurricular::query()->where('anio_escolar', '2026')->firstOrFail();

        $this->assertFalse(
            CursoCatalogo::query()->where('area_id', $area->id)->where('nombre', 'Estadística')->exists()
        );

        $response = $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $area->id,
                'nombre' => '  Estadística  ',
            ])
            ->assertCreated()
            ->assertJsonPath('activo', true)
            ->assertJsonPath('message', 'Curso creado y agregado correctamente.');

        $catalogoId = $response->json('curso_catalogo_id');
        $this->assertTrue(
            CursoCatalogo::query()->whereKey($catalogoId)->where('nombre', 'Estadística')->exists()
        );
    }

    #[Test]
    public function reutiliza_curso_catalogo_existente_si_nombre_coincide(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->where('activo', true)->firstOrFail();
        $malla = $mallaCurso->mallaCurricular;
        $catalogo = CursoCatalogo::query()->findOrFail($mallaCurso->curso_catalogo_id);
        $mallaCurso->delete();

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $mallaCurso->area_id,
                'nombre' => mb_strtolower($catalogo->nombre),
            ])
            ->assertCreated()
            ->assertJsonPath('curso_catalogo_id', $catalogo->id)
            ->assertJsonPath('message', 'Ya existe un curso con ese nombre en el catálogo; se agregó el existente.');
    }

    #[Test]
    public function rechaza_agregar_curso_con_nombre_y_curso_catalogo_id(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $malla = $mallaCurso->mallaCurricular;

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $mallaCurso->area_id,
                'curso_catalogo_id' => $mallaCurso->curso_catalogo_id,
                'nombre' => 'Otro curso',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['curso_catalogo_id']);
    }

    #[Test]
    public function rechaza_agregar_curso_sin_nombre_ni_curso_catalogo_id(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $malla = MallaCurricular::query()->where('anio_escolar', '2026')->firstOrFail();
        $area = Area::query()->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)->firstOrFail();

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $area->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['curso_catalogo_id']);
    }

    #[Test]
    public function rechaza_curso_catalogo_de_otra_area(): void
    {
        $user = $this->coordinador();
        $this->actingAs($user)->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $malla = MallaCurricular::query()->where('anio_escolar', '2026')->firstOrFail();
        $areaMatematica = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Matemática')
            ->firstOrFail();
        $areaComunicacion = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Comunicación')
            ->firstOrFail();
        $cursoComunicacion = CursoCatalogo::query()->where('area_id', $areaComunicacion->id)->firstOrFail();

        $this->actingAs($user)
            ->postJson("/api/curricular/mallas/{$malla->id}/cursos", [
                'area_id' => $areaMatematica->id,
                'curso_catalogo_id' => $cursoComunicacion->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['curso_catalogo_id']);
    }

    #[Test]
    public function competencias_por_area_devuelve_varias_opciones(): void
    {
        $area = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Comunicación')
            ->firstOrFail();

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/areas/{$area->id}/competencias");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    #[Test]
    public function matematica_tiene_varias_competencias_por_area(): void
    {
        $area = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Matemática')
            ->firstOrFail();

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/areas/{$area->id}/competencias");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(4, count($response->json()));
    }

    #[Test]
    public function ciencia_tecnologia_tiene_varias_competencias_por_area(): void
    {
        $area = Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Ciencia y Tecnología')
            ->firstOrFail();

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/areas/{$area->id}/competencias");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    #[Test]
    public function capacidades_por_competencia_devuelve_lista(): void
    {
        $competencia = Competencia::query()
            ->where('nombre', 'Indaga mediante métodos científicos para construir conocimientos')
            ->whereHas('area', fn ($q) => $q->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA))
            ->firstOrFail();

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/competencias/{$competencia->id}/capacidades");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(5, count($response->json()));
    }

    #[Test]
    public function get_temas_incluye_competencias_capacidades_y_relaciones(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema resumen API',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->assertCreated();

        $response = $this->actingAs($this->coordinador())
            ->getJson("/api/curricular/temas?malla_curso_id={$mallaCurso->id}");

        $response->assertOk();
        $payload = $response->json();
        $this->assertNotEmpty($payload);
        $tema = $payload[0];
        $this->assertArrayHasKey('competencias', $tema);
        $this->assertArrayHasKey('capacidades', $tema);
        $this->assertNotEmpty($tema['competencias']);
        $this->assertNotEmpty($tema['capacidades']);
        $this->assertNotNull($tema['malla_curso']['curso_catalogo'] ?? $tema['malla_curso']['cursoCatalogo'] ?? null);
        $this->assertNotNull($tema['semana_academica'] ?? $tema['semanaAcademica'] ?? null);
    }

    #[Test]
    public function desactivar_tema_no_lo_borra(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $temaId = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema a desactivar',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $this->actingAs($this->coordinador())
            ->patchJson("/api/curricular/temas/{$temaId}/desactivar")
            ->assertOk();

        $this->assertNotNull(TemaSemanal::query()->find($temaId));
        $this->assertFalse(TemaSemanal::query()->find($temaId)->activo);
    }

    #[Test]
    public function rechaza_capacidad_de_competencia_no_seleccionada(): void
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlotComunicacion();

        $competencias = Competencia::query()->where('area_id', $mallaCurso->area_id)->orderBy('id')->get();
        $this->assertGreaterThanOrEqual(2, $competencias->count());

        $capacidadOtra = Capacidad::query()
            ->where('competencia_id', $competencias[1]->id)
            ->firstOrFail();

        $this->actingAs($this->coordinador())
            ->postJson('/api/curricular/temas', [
                'malla_curso_id' => $mallaCurso->id,
                'periodo_academico_id' => $periodo->id,
                'semana_academica_id' => $semana->id,
                'titulo' => 'Tema inválido capacidad',
                'competencia_ids' => [$competencias[0]->id],
                'capacidad_ids' => [$capacidadOtra->id],
            ])
            ->assertStatus(422);
    }

    private function queryStringConsultaGlobal(DocenteCursoAula $asignacion, int $periodoAcademicoId): string
    {
        return http_build_query([
            'consulta_global' => '1',
            'anio_escolar' => $asignacion->anio_escolar,
            'nivel' => $asignacion->nivel,
            'sede' => $asignacion->sede,
            'grado' => $asignacion->grado,
            'seccion' => $asignacion->seccion,
            'malla_curso_id' => $asignacion->malla_curso_id,
            'periodo_academico_id' => $periodoAcademicoId,
        ]);
    }

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

    /**
     * @return array{0: MallaCurso, 1: PeriodoAcademico, 2: SemanaAcademica}
     */
    private function prepararMallaYTemaSlot(): array
    {
        $this->actingAs($this->coordinador())->getJson(
            '/api/curricular/mallas/grado?anio_escolar=2026&nivel=primaria&grado=2do'
        )->assertOk();

        $mallaCurso = MallaCurso::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $semana = SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->where('numero_semana', 1)->firstOrFail();

        return [$mallaCurso, $periodo, $semana];
    }

    /**
     * Curso de Comunicación (varias competencias por área).
     *
     * @return array{0: MallaCurso, 1: PeriodoAcademico, 2: SemanaAcademica}
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

        $mallaCurso = MallaCurso::query()
            ->where('area_id', $areaComunicacion->id)
            ->firstOrFail();

        $periodo = PeriodoAcademico::query()->where('anio_escolar', '2026')->where('bimestre', '1')->firstOrFail();
        $semana = SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->where('numero_semana', 1)->firstOrFail();

        return [$mallaCurso, $periodo, $semana];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: TemaSemanal, 2: Estudiante}
     */
    private function prepararFlujoNotas(): array
    {
        [$mallaCurso, $periodo, $semana] = $this->prepararMallaYTemaSlot();
        $competencia = Competencia::query()->where('area_id', $mallaCurso->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $coordinador = $this->coordinador();
        $temaId = $this->actingAs($coordinador)->postJson('/api/curricular/temas', [
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodo->id,
            'semana_academica_id' => $semana->id,
            'titulo' => 'Tema notas',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $docenteUser = $this->docente();
        $asignacionId = $this->actingAs($coordinador)->postJson('/api/curricular/asignaciones-docente', [
            'user_id' => $docenteUser->id,
            'malla_curso_id' => $mallaCurso->id,
            'anio_escolar' => '2026',
            'nivel' => 'primaria',
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
        ])->json('id');

        $estudiante = Estudiante::factory()->create([
            'grado' => '2°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'anio_escolar' => '2026',
        ]);

        $asignacion = DocenteCursoAula::query()->findOrFail($asignacionId);
        $asignacion->setRelation('user', $docenteUser);
        $tema = TemaSemanal::query()->findOrFail($temaId);

        return [$asignacion, $tema, $estudiante];
    }

    /**
     * @return array{0: DocenteCursoAula, 1: TemaSemanal, 2: Estudiante, 3: TemaSemanal}
     */
    private function prepararFlujoNotasConDosCriterios(): array
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $competencia = Competencia::query()->where('area_id', MallaCurso::query()->findOrFail($tema->malla_curso_id)->area_id)->firstOrFail();
        $capacidad = Capacidad::query()->where('competencia_id', $competencia->id)->skip(1)->first()
            ?? Capacidad::query()->where('competencia_id', $competencia->id)->firstOrFail();

        $tema2Id = $this->actingAs($this->coordinador())->postJson('/api/curricular/temas', [
            'malla_curso_id' => $tema->malla_curso_id,
            'periodo_academico_id' => $tema->periodo_academico_id,
            'semana_academica_id' => $tema->semana_academica_id,
            'titulo' => 'Segundo criterio sin nota',
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ])->json('id');

        $tema2 = TemaSemanal::query()->findOrFail($tema2Id);

        return [$asignacion, $tema, $estudiante, $tema2];
    }

    #[Test]
    public function docente_puede_descargar_plantilla_excel_de_curso_asignado(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $response = $this->actingAs($asignacion->user)
            ->get('/api/curricular/notas-semanales/plantilla-excel?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type'),
        );

        $sheet = $this->leerHojaPlantillaExcel($response);
        $this->assertStringContainsString(
            trim("{$estudiante->apellidos} {$estudiante->nombres}"),
            (string) $sheet->getCell('B10')->getValue(),
        );
        $this->assertStringContainsString('REGISTRO AUXILIAR', (string) $sheet->getCell('A1')->getValue());
    }

    #[Test]
    public function docente_no_puede_descargar_plantilla_de_curso_no_asignado(): void
    {
        [$asignacion] = $this->prepararFlujoNotas();
        $otroDocente = $this->docente();
        $periodoId = TemaSemanal::query()->where('malla_curso_id', $asignacion->malla_curso_id)->value('periodo_academico_id');

        $this->actingAs($otroDocente)
            ->get("/api/curricular/notas-semanales/plantilla-excel?".http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function coordinador_puede_descargar_plantilla_excel(): void
    {
        [$asignacion] = $this->prepararFlujoNotas();
        $periodoId = TemaSemanal::query()->where('malla_curso_id', $asignacion->malla_curso_id)->value('periodo_academico_id');

        $response = $this->actingAs($this->coordinador())
            ->get('/api/curricular/notas-semanales/plantilla-excel?'.http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type'),
        );
    }

    #[Test]
    public function plantilla_excel_incluye_criterios(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $response = $this->actingAs($asignacion->user)
            ->get("/api/curricular/notas-semanales/plantilla-excel?".http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
            ]));

        $sheet = $this->leerHojaPlantillaExcel($response);
        $this->assertSame('Tema notas', (string) $sheet->getCell('C8')->getValue());
    }

    #[Test]
    public function plantilla_vacia_no_incluye_notas_pero_si_formula_ce(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 15,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated();

        $response = $this->actingAs($asignacion->user)
            ->get("/api/curricular/notas-semanales/plantilla-excel?".http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'incluir_notas' => '0',
            ]));

        $sheet = $this->leerHojaPlantillaExcel($response);
        $this->assertNull($sheet->getCell('C10')->getValue());
        $ceValue = $sheet->getCell('F10')->getValue();
        $this->assertIsString($ceValue);
        $this->assertStringStartsWith('=IFERROR', $ceValue);

        $this->assertFormulaBimestral($sheet->getCell('G10')->getValue(), 'Promedio de criterio');
        $this->assertFormulaBimestral($sheet->getCell('L10')->getValue(), 'Promedio ETA');
        $this->assertFormulaBimestral($sheet->getCell('N10')->getValue(), 'Nivel numérico');
        $literalValue = $sheet->getCell('O10')->getValue();
        $this->assertIsString($literalValue);
        $this->assertStringStartsWith('=IF(', $literalValue);

        $this->assertPlantillaSinErrorDiv0($sheet);
    }

    #[Test]
    public function plantilla_vacia_formulas_bimestrales_usan_pesos_configurados(): void
    {
        [$asignacion, $tema] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $response = $this->actingAs($asignacion->user)
            ->get("/api/curricular/notas-semanales/plantilla-excel?".http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'incluir_notas' => '0',
            ]));

        $sheet = $this->leerHojaPlantillaExcel($response);
        $nivelFormula = (string) $sheet->getCell('N10')->getValue();
        $this->assertStringContainsString('*0.25', $nivelFormula);
        $this->assertStringContainsString('G10', $nivelFormula);
        $this->assertStringContainsString('H10', $nivelFormula);
        $this->assertStringContainsString('L10', $nivelFormula);
        $this->assertStringContainsString('M10', $nivelFormula);
    }

    #[Test]
    public function plantilla_con_notas_incluye_valores_registrados(): void
    {
        [$asignacion, $tema, $estudiante] = $this->prepararFlujoNotas();
        $periodoId = $tema->periodo_academico_id;

        $this->actingAs($asignacion->user)
            ->postJson('/api/curricular/notas-semanales/bulk', [
                'asignacion_docente_id' => $asignacion->id,
                'tema_semanal_id' => $tema->id,
                'notas' => [
                    [
                        'estudiante_id' => $estudiante->id,
                        'nota_cuaderno' => 14,
                        'nota_libro' => 15,
                        'nota_tarea' => 16,
                    ],
                ],
            ])
            ->assertCreated();

        $response = $this->actingAs($asignacion->user)
            ->get("/api/curricular/notas-semanales/plantilla-excel?".http_build_query([
                'asignacion_docente_id' => $asignacion->id,
                'periodo_academico_id' => $periodoId,
                'incluir_notas' => '1',
            ]));

        $sheet = $this->leerHojaPlantillaExcel($response);
        $this->assertEquals(14, $sheet->getCell('C10')->getValue());
        $this->assertEquals(15, $sheet->getCell('D10')->getValue());
        $this->assertEquals(16, $sheet->getCell('E10')->getValue());
        $this->assertEquals(15, $sheet->getCell('F10')->getValue());
    }

    private function leerHojaPlantillaExcel(\Illuminate\Testing\TestResponse $response): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $response->assertOk();
        $tmp = tempnam(sys_get_temp_dir(), 'plantilla_xlsx_');
        file_put_contents($tmp, $response->streamedContent());
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        return $sheet;
    }

    private function assertFormulaBimestral(mixed $value, string $contexto): void
    {
        $this->assertIsString($value, "Se esperaba fórmula en {$contexto}");
        $this->assertStringStartsWith('=IFERROR', $value, "Fórmula inválida en {$contexto}");
    }

    private function assertPlantillaSinErrorDiv0(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        foreach ($sheet->getRowIterator(10, 10) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $valor = $cell->getValue();
                if (is_string($valor)) {
                    $this->assertStringNotContainsString('#DIV/0!', $valor);
                }
            }
        }
    }
}
