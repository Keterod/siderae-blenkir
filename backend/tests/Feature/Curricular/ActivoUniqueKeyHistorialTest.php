<?php

namespace Tests\Feature\Curricular;

use App\Exceptions\Curricular\AsignacionDocenteDuplicadaException;
use App\Exceptions\Curricular\TemaSemanalDuplicadoException;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Models\User;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\DocenteCursoAulaValidator;
use App\Services\Curricular\TemaSemanalValidator;
use Database\Seeders\CurricularModuleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivoUniqueKeyHistorialTest extends TestCase
{
    use RefreshDatabase;

    private MallaCurso $mallaCurso;

    private PeriodoAcademico $periodo;

    private SemanaAcademica $semana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurricularModuleSeeder::class);
        $this->mallaCurso = $this->crearMallaCursoDemo();
        $this->periodo = PeriodoAcademico::query()
            ->where('anio_escolar', '2026')
            ->where('bimestre', '1')
            ->firstOrFail();
        $this->semana = SemanaAcademica::query()
            ->where('periodo_academico_id', $this->periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();
    }

    #[Test]
    public function permite_asignacion_inactiva_y_luego_activa_misma_combinacion(): void
    {
        $docente1 = User::factory()->create();
        $docente2 = User::factory()->create();

        $inactiva = $this->crearAsignacionDocente($docente1->id, activo: false);
        $activa = $this->crearAsignacionDocente($docente2->id, activo: true);

        $this->assertFalse($inactiva->activo);
        $this->assertNull($inactiva->activo_unique_key);
        $this->assertTrue($activa->activo);
        $this->assertSame(1, $activa->activo_unique_key);
        $this->assertNotSame($inactiva->id, $activa->id);
    }

    #[Test]
    public function rechaza_dos_asignaciones_activas_misma_combinacion_por_validador(): void
    {
        $this->crearAsignacionDocente(User::factory()->create()->id, activo: true);

        $this->expectException(AsignacionDocenteDuplicadaException::class);

        app(DocenteCursoAulaValidator::class)->validarAsignacionUnicaActiva($this->datosAsignacion(User::factory()->create()->id));
    }

    #[Test]
    public function rechaza_dos_asignaciones_activas_misma_combinacion_por_bd(): void
    {
        $this->crearAsignacionDocenteSinValidacion(User::factory()->create()->id, activo: true);

        $this->expectException(QueryException::class);

        $this->crearAsignacionDocenteSinValidacion(User::factory()->create()->id, activo: true);
    }

    #[Test]
    public function permite_varios_criterios_activos_mismo_curso_bimestre_semana(): void
    {
        $this->crearTemaSemanal(activo: true, titulo: 'Las plantas y sus partes');
        $this->crearTemaSemanal(activo: true, titulo: 'La raíz');

        $this->assertSame(2, TemaSemanal::query()->where('activo', true)->count());
    }

    #[Test]
    public function permite_criterio_sin_semana_referencial(): void
    {
        $tema = TemaSemanal::query()->create([
            'malla_curso_id' => $this->mallaCurso->id,
            'periodo_academico_id' => $this->periodo->id,
            'semana_academica_id' => null,
            'titulo' => 'Fotosíntesis',
            'activo' => true,
        ]);

        $this->assertNull($tema->semana_academica_id);
    }

    #[Test]
    public function rechaza_duplicado_exacto_por_validador(): void
    {
        $this->crearTemaSemanal(activo: true, titulo: 'Criterio único');

        $this->expectException(TemaSemanalDuplicadoException::class);

        app(TemaSemanalValidator::class)->validarDuplicadoExacto([
            'malla_curso_id' => $this->mallaCurso->id,
            'periodo_academico_id' => $this->periodo->id,
            'titulo' => 'Criterio único',
            'competencia_ids' => [],
            'capacidad_ids' => [],
        ]);
    }

    #[Test]
    public function al_desactivar_asignacion_activo_unique_key_pasa_a_null(): void
    {
        $asignacion = $this->crearAsignacionDocente(User::factory()->create()->id, activo: true);
        $this->assertSame(1, $asignacion->activo_unique_key);

        $asignacion->update(['activo' => false]);
        $asignacion->refresh();

        $this->assertFalse($asignacion->activo);
        $this->assertNull($asignacion->activo_unique_key);
    }

    private function crearMallaCursoDemo(): MallaCurso
    {
        $plantilla = PlantillaCurricular::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('grado', '2do')
            ->firstOrFail();

        $plantillaCurso = PlantillaCurso::query()
            ->where('plantilla_curricular_id', $plantilla->id)
            ->firstOrFail();

        $malla = MallaCurricular::query()->create([
            'anio_escolar' => '2026',
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '2do',
            'estado' => 'activa',
            'plantilla_curricular_id' => $plantilla->id,
        ]);

        return MallaCurso::query()->create([
            'malla_curricular_id' => $malla->id,
            'area_id' => $plantillaCurso->area_id,
            'curso_catalogo_id' => $plantillaCurso->curso_catalogo_id,
            'orden' => 1,
            'activo' => true,
        ]);
    }

    /**
     * @return array{anio_escolar: string, nivel: string, grado: string, seccion: string, sede: string, malla_curso_id: int, user_id: int}
     */
    private function datosAsignacion(int $userId): array
    {
        return [
            'anio_escolar' => '2026',
            'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
            'grado' => '2do',
            'seccion' => 'A',
            'sede' => 'chilca',
            'malla_curso_id' => $this->mallaCurso->id,
            'user_id' => $userId,
        ];
    }

    private function crearAsignacionDocente(int $userId, bool $activo): DocenteCursoAula
    {
        $datos = $this->datosAsignacion($userId);
        $datos['activo'] = $activo;

        if ($activo) {
            app(DocenteCursoAulaValidator::class)->validarAsignacionUnicaActiva($datos);
        }

        return DocenteCursoAula::query()->create($datos);
    }

    private function crearAsignacionDocenteSinValidacion(int $userId, bool $activo): DocenteCursoAula
    {
        return DocenteCursoAula::query()->create(array_merge(
            $this->datosAsignacion($userId),
            ['activo' => $activo]
        ));
    }

    /**
     * @return array{malla_curso_id: int, periodo_academico_id: int, semana_academica_id: int}
     */
    private function datosTema(): array
    {
        return [
            'malla_curso_id' => $this->mallaCurso->id,
            'periodo_academico_id' => $this->periodo->id,
            'semana_academica_id' => $this->semana->id,
        ];
    }

    private function crearTemaSemanal(bool $activo, string $titulo = 'Tema demo'): TemaSemanal
    {
        return TemaSemanal::query()->create(array_merge($this->datosTema(), [
            'titulo' => $titulo,
            'activo' => $activo,
        ]));
    }

    private function crearTemaSemanalSinValidacion(bool $activo, string $titulo = 'Tema demo'): TemaSemanal
    {
        return $this->crearTemaSemanal($activo, $titulo);
    }
}
