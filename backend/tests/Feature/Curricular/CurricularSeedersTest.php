<?php

namespace Tests\Feature\Curricular;

use App\Models\Curricular\Competencia;
use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\EquivalenciaGrado;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Models\Curricular\SemanaAcademica;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\MallaCurricularService;
use Database\Seeders\CurricularModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurricularSeedersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurricularModuleSeeder::class);
    }

    #[Test]
    public function equivalencias_grado_tiene_catorce_registros(): void
    {
        $this->assertSame(14, EquivalenciaGrado::query()->count());
    }

    #[Test]
    public function equivalencias_grado_incluye_tres_grados_inicial(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            $this->assertDatabaseHas('equivalencias_grado', [
                'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
                'grado_curricular' => $grado,
                'grado_estudiante_legacy' => $grado,
            ]);
        }
    }

    #[Test]
    public function plantilla_primaria_tiene_cursos_institucionales_en_todos_los_grados(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_PRIMARIA as $grado) {
            $plantilla = PlantillaCurricular::query()
                ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
                ->where('grado', $grado)
                ->first();

            $this->assertNotNull($plantilla);
            $this->assertTrue($plantilla->detalle_completo);
            $this->assertGreaterThanOrEqual(16, PlantillaCurso::query()->where('plantilla_curricular_id', $plantilla->id)->count());
        }
    }

    #[Test]
    public function plantilla_inicial_tiene_cursos_institucionales(): void
    {
        $totalCursos = CursoCatalogo::query()
            ->where('es_institucional', true)
            ->whereHas('area', fn ($q) => $q->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
            ->count();

        $this->assertGreaterThanOrEqual(9, $totalCursos);

        $plantilla = PlantillaCurricular::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
            ->where('grado', '3 años')
            ->first();

        $this->assertTrue($plantilla->detalle_completo);
        $this->assertGreaterThanOrEqual(9, PlantillaCurso::query()->where('plantilla_curricular_id', $plantilla->id)->count());
    }

    #[Test]
    public function plantilla_secundaria_tiene_cursos_institucionales(): void
    {
        $totalCursos = CursoCatalogo::query()
            ->where('es_institucional', true)
            ->whereHas('area', fn ($q) => $q->where('nivel', CatalogoNivelGrado::NIVEL_SECUNDARIA))
            ->count();

        $this->assertGreaterThanOrEqual(20, $totalCursos);

        $plantilla = PlantillaCurricular::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_SECUNDARIA)
            ->where('grado', '2do')
            ->first();

        $this->assertTrue($plantilla->detalle_completo);
        $this->assertGreaterThanOrEqual(20, PlantillaCurso::query()->where('plantilla_curricular_id', $plantilla->id)->count());
    }

    #[Test]
    public function comunicacion_primaria_tiene_varias_competencias(): void
    {
        $area = \App\Models\Curricular\Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Comunicación')
            ->first();

        $this->assertGreaterThanOrEqual(2, Competencia::query()->where('area_id', $area->id)->where('activo', true)->count());
    }

    #[Test]
    public function matematica_primaria_tiene_cuatro_competencias_oficiales(): void
    {
        $area = \App\Models\Curricular\Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Matemática')
            ->firstOrFail();

        $this->assertGreaterThanOrEqual(4, Competencia::query()->where('area_id', $area->id)->where('activo', true)->count());
    }

    #[Test]
    public function ciencia_tecnologia_primaria_tiene_tres_competencias_oficiales(): void
    {
        $area = \App\Models\Curricular\Area::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('nombre', 'Ciencia y Tecnología')
            ->firstOrFail();

        $this->assertGreaterThanOrEqual(3, Competencia::query()->where('area_id', $area->id)->where('activo', true)->count());
    }

    #[Test]
    public function indaga_tiene_cinco_capacidades(): void
    {
        $competencia = Competencia::query()
            ->where('nombre', 'Indaga mediante métodos científicos para construir conocimientos')
            ->whereHas('area', fn ($q) => $q->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA))
            ->firstOrFail();

        $this->assertGreaterThanOrEqual(5, $competencia->capacidades()->where('activo', true)->count());
    }

    #[Test]
    public function malla_no_duplica_cursos_al_provisionar_varias_veces(): void
    {
        $service = new MallaCurricularService;

        $service->obtenerOProvisionar('2027', CatalogoNivelGrado::NIVEL_PRIMARIA, '2do');
        $service->obtenerOProvisionar('2027', CatalogoNivelGrado::NIVEL_PRIMARIA, '2do');

        $malla = MallaCurricular::query()
            ->where('anio_escolar', '2027')
            ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
            ->where('grado', '2do')
            ->first();

        $this->assertSame(1, MallaCurricular::query()->where('anio_escolar', '2027')->where('grado', '2do')->count());
        $this->assertGreaterThanOrEqual(16, MallaCurso::query()->where('malla_curricular_id', $malla->id)->count());
    }

    #[Test]
    public function periodos_demo_2026_tienen_cuatro_semanas_por_bimestre(): void
    {
        $periodos = PeriodoAcademico::query()->where('anio_escolar', '2026')->get();
        $this->assertCount(4, $periodos);

        foreach ($periodos as $periodo) {
            $this->assertSame(4, SemanaAcademica::query()->where('periodo_academico_id', $periodo->id)->count());
        }
    }
}
