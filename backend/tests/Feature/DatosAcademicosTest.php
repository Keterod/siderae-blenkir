<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DatosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    private function permisoDatosAcademicos(): void
    {
        Permission::firstOrCreate([
            'name' => 'registrar_datos_academicos',
            'guard_name' => 'web',
        ]);
    }

    private function usuarioPermitido(): User
    {
        $this->permisoDatosAcademicos();
        $user = User::factory()->create();

        $user->givePermissionTo('registrar_datos_academicos');

        return $user;
    }

    private function usuarioSinPermiso(): User
    {
        $this->permisoDatosAcademicos();

        return User::factory()->create();
    }

    private function estudianteBase(): Estudiante
    {
        return Estudiante::factory()->create([
            'codigo' => 'EST-X-001',
            'anio_escolar' => '2026',
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
        ]);
    }

    public function test_usuario_con_permiso_registra_nota_valida(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '1',
                'curso' => 'Matemática',
                'nota' => 14.5,
                'nota_conducta' => 17,
            ]
        );

        $response->assertCreated()->assertJsonPath('curso', 'Matemática');

        $this->assertDatabaseHas('notas', [
            'estudiante_id' => $estudiante->id,
            'bimestre' => '1',
            'curso' => 'Matemática',
            'materia_id' => null,
        ]);
    }

    public function test_usuario_con_permiso_lista_notas_del_estudiante(): void
    {
        $estudiante = $this->estudianteBase();

        $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '2',
                'curso' => 'Comunicación',
                'nota' => 15,
                'nota_conducta' => null,
            ]
        )->assertSuccessful();

        $response = $this->actingAs($this->usuarioPermitido())->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_rechazo_nota_invalida_fuera_de_rango(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '1',
                'curso' => 'Historia',
                'nota' => 21,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['nota']);
    }

    public function test_usuario_con_permiso_registra_asistencia_valida(): void
    {
        $estudiante = $this->estudianteBase();
        $user = $this->usuarioPermitido();

        $response = $this->actingAs($user)->postJson(
            "/api/estudiantes/{$estudiante->id}/asistencias",
            [
                'semana_inicio' => '2026-04-14',
                'estado' => 'presente',
                'anio_escolar' => '2026',
                'bimestre' => '1',
            ]
        );

        $response->assertCreated()
            ->assertJsonFragment([
                'estado' => 'presente',
                'registrado_por' => $user->id,
            ]);

        $this->assertDatabaseHas('asistencias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'presente',
            'registrado_por' => $user->id,
        ]);
    }

    public function test_usuario_con_permiso_lista_asistencias(): void
    {
        $estudiante = $this->estudianteBase();
        $actor = $this->usuarioPermitido();

        $this->actingAs($actor)->postJson(
            "/api/estudiantes/{$estudiante->id}/asistencias",
            [
                'semana_inicio' => '2026-04-07',
                'estado' => 'tardanza',
                'anio_escolar' => '2026',
                'bimestre' => '1',
            ]
        )->assertSuccessful();

        $response = $this->actingAs($actor)->getJson(
            "/api/estudiantes/{$estudiante->id}/asistencias"
        );

        $response->assertSuccessful()->assertJsonCount(1);
    }

    public function test_usuario_con_permiso_registra_variables_socioeconomicas(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/variables-socioeconomicas",
            [
                'composicion_familiar' => 'nuclear',
                'nivel_socioeconomico' => 'medio',
                'acceso_internet' => true,
                'distancia_colegio_km' => 2.5,
                'anio_escolar' => '2026',
            ]
        );

        $response->assertSuccessful()->assertJsonFragment([
            'composicion_familiar' => 'nuclear',
            'anio_escolar' => '2026',
        ]);

        $this->assertDatabaseHas('variables_socioeconomicas', [
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => '2026',
            'composicion_familiar' => 'nuclear',
        ]);
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->actingAs($this->usuarioSinPermiso())->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertForbidden();
    }

    public function test_visitante_sin_sesion_recibe_no_autorizada(): void
    {
        $estudiante = $this->estudianteBase();

        $response = $this->getJson(
            "/api/estudiantes/{$estudiante->id}/notas"
        );

        $response->assertUnauthorized();
    }

    public function test_nota_con_materia_guarda_curso_deriva_de_nombre_materia(): void
    {
        $estudiante = $this->estudianteBase();

        $materia = Materia::query()->create([
            'nombre' => 'Comunicación',
            'nivel' => $estudiante->nivel,
            'grado' => $estudiante->grado,
            'anio_escolar' => $estudiante->anio_escolar,
            'sede' => $estudiante->sede,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '1',
                'materia_id' => $materia->id,
                'nota' => 16,
                'nota_conducta' => null,
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('curso', 'Comunicación')
            ->assertJsonPath('materia_id', $materia->id);

        $this->assertDatabaseHas('notas', [
            'estudiante_id' => $estudiante->id,
            'curso' => 'Comunicación',
            'materia_id' => $materia->id,
        ]);
    }

    public function test_nota_con_materia_fuera_del_contexto_rechazo(): void
    {
        $estudiante = $this->estudianteBase();

        $otraGrado = Materia::query()->create([
            'nombre' => 'Arte',
            'nivel' => 'primaria',
            'grado' => '6°',
            'anio_escolar' => $estudiante->anio_escolar,
            'sede' => $estudiante->sede,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->usuarioPermitido())->postJson(
            "/api/estudiantes/{$estudiante->id}/notas",
            [
                'anio_escolar' => '2026',
                'bimestre' => '3',
                'materia_id' => $otraGrado->id,
                'nota' => 11,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['materia_id']);
    }

    /** @return array{nombre: string, nivel: string, grado: string, anio_escolar: string, sede: string, seccion: string} */
    private function contextoAcademicoBase(): array
    {
        return [
            'anio_escolar' => '2026',
            'grado' => '1°',
            'seccion' => 'A',
            'nivel' => 'primaria',
            'sede' => 'chilca',
            'nombre' => 'Comunicación',
        ];
    }

    public function test_usuario_con_permiso_registra_notas_en_lote(): void
    {
        $ctx = $this->contextoAcademicoBase();

        $e1 = Estudiante::factory()->create([
            'codigo' => 'LOTE-N-01',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);
        $e2 = Estudiante::factory()->create([
            'codigo' => 'LOTE-N-02',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);

        $materia = Materia::query()->create([
            'nombre' => $ctx['nombre'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'anio_escolar' => $ctx['anio_escolar'],
            'sede' => $ctx['sede'],
            'activo' => true,
        ]);

        $actor = $this->usuarioPermitido();

        $response = $this->actingAs($actor)->postJson('/api/notas/lote', [
            'materia_id' => $materia->id,
            'anio_escolar' => $ctx['anio_escolar'],
            'bimestre' => '1',
            'sede' => $ctx['sede'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'filas' => [
                ['estudiante_id' => $e1->id, 'nota' => 12],
                ['estudiante_id' => $e2->id, 'nota' => 15.5],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('total', 2);

        $this->assertDatabaseHas('notas', [
            'estudiante_id' => $e1->id,
            'materia_id' => $materia->id,
            'curso' => 'Comunicación',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'nota.lote_registrado',
            'causer_id' => $actor->id,
        ]);
    }

    public function test_lote_notas_nota_fuera_de_rango_retorna_422(): void
    {
        $ctx = $this->contextoAcademicoBase();

        $e1 = Estudiante::factory()->create([
            'codigo' => 'LOTE-N-R01',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);

        $materia = Materia::query()->create([
            'nombre' => $ctx['nombre'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'anio_escolar' => $ctx['anio_escolar'],
            'sede' => $ctx['sede'],
            'activo' => true,
        ]);

        $response = $this->actingAs($this->usuarioPermitido())->postJson('/api/notas/lote', [
            'materia_id' => $materia->id,
            'anio_escolar' => $ctx['anio_escolar'],
            'bimestre' => '2',
            'sede' => $ctx['sede'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'filas' => [
                ['estudiante_id' => $e1->id, 'nota' => 22],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['filas.0.nota']);
    }

    public function test_usuario_con_permiso_registra_asistencias_en_lote(): void
    {
        $ctx = $this->contextoAcademicoBase();

        $e1 = Estudiante::factory()->create([
            'codigo' => 'LOTE-A-01',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);
        $e2 = Estudiante::factory()->create([
            'codigo' => 'LOTE-A-02',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);

        $actor = $this->usuarioPermitido();

        $response = $this->actingAs($actor)->postJson('/api/asistencias/lote', [
            'semana_inicio' => '2026-04-14',
            'anio_escolar' => $ctx['anio_escolar'],
            'bimestre' => '1',
            'sede' => $ctx['sede'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'filas' => [
                ['estudiante_id' => $e1->id, 'estado' => 'presente'],
                ['estudiante_id' => $e2->id, 'estado' => 'falta'],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('total', 2)
            ->assertJsonPath('creadas.0.estado', 'presente');

        $this->assertDatabaseHas('asistencias', [
            'estudiante_id' => $e1->id,
            'estado' => 'presente',
            'registrado_por' => $actor->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'asistencia.lote_registrado',
            'causer_id' => $actor->id,
        ]);
    }

    public function test_lote_asistencia_estado_invalido_retorna_422(): void
    {
        $ctx = $this->contextoAcademicoBase();

        $e1 = Estudiante::factory()->create([
            'codigo' => 'LOTE-A-BAD',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);

        $response = $this->actingAs($this->usuarioPermitido())->postJson('/api/asistencias/lote', [
            'semana_inicio' => '2026-04-21',
            'anio_escolar' => $ctx['anio_escolar'],
            'bimestre' => '3',
            'sede' => $ctx['sede'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'filas' => [
                ['estudiante_id' => $e1->id, 'estado' => 'ausente'],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['filas.0.estado']);
    }

    public function test_lote_notas_visitante_sin_sesion_recibe_401(): void
    {
        $response = $this->postJson('/api/notas/lote', [
            'materia_id' => 1,
            'anio_escolar' => '2026',
            'bimestre' => '1',
            'sede' => 'chilca',
            'nivel' => 'primaria',
            'grado' => '1°',
            'seccion' => 'A',
            'filas' => [],
        ]);

        $response->assertUnauthorized();
    }

    public function test_lote_notas_usuario_sin_permiso_recibe_403(): void
    {
        $ctx = $this->contextoAcademicoBase();

        $e1 = Estudiante::factory()->create([
            'codigo' => 'LOTE-403',
            'anio_escolar' => $ctx['anio_escolar'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'nivel' => $ctx['nivel'],
            'sede' => $ctx['sede'],
        ]);

        $materia = Materia::query()->create([
            'nombre' => $ctx['nombre'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'anio_escolar' => $ctx['anio_escolar'],
            'sede' => $ctx['sede'],
            'activo' => true,
        ]);

        $response = $this->actingAs($this->usuarioSinPermiso())->postJson('/api/notas/lote', [
            'materia_id' => $materia->id,
            'anio_escolar' => $ctx['anio_escolar'],
            'bimestre' => '1',
            'sede' => $ctx['sede'],
            'nivel' => $ctx['nivel'],
            'grado' => $ctx['grado'],
            'seccion' => $ctx['seccion'],
            'filas' => [
                ['estudiante_id' => $e1->id, 'nota' => 10],
            ],
        ]);

        $response->assertForbidden();
    }
}
