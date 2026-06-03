<?php

namespace Tests\Feature\Seeders;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\MallaCurricularService;
use Database\Seeders\Curricular\CriteriosEvaluacionInicialSeeder;
use Database\Seeders\CurricularModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CriteriosEvaluacionInicialSeederTest extends TestCase
{
    use RefreshDatabase;

    private const ANIO_ESCOLAR = '2026';

    private const BIMESTRE = '2';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurricularModuleSeeder::class);
        $this->provisionarMallasInicial2026();
    }

    #[Test]
    public function catalogo_fuente_tiene_217_criterios_sin_aprestamiento(): void
    {
        $catalogo = require base_path('Untitled-1.php');

        $this->assertCount(217, $catalogo);

        foreach ($catalogo as $item) {
            $texto = strtolower((string) $item['criterio'].' '.(string) ($item['descripcion'] ?? ''));
            $this->assertStringNotContainsString('aprestamiento', $texto);
        }
    }

    #[Test]
    public function seeder_es_idempotente_y_no_crea_catalogos_ni_asigna_semana(): void
    {
        $conteosAntes = $this->conteosCatalogoBase();

        $this->seed(CriteriosEvaluacionInicialSeeder::class);
        $totalPrimeraEjecucion = $this->conteoCriteriosInicialBimestreDos();

        $this->assertSame(217, $totalPrimeraEjecucion);
        $this->assertSame(72, $this->conteoPorGrado('3 años'));
        $this->assertSame(72, $this->conteoPorGrado('4 años'));
        $this->assertSame(73, $this->conteoPorGrado('5 años'));

        $this->seed(CriteriosEvaluacionInicialSeeder::class);
        $totalSegundaEjecucion = $this->conteoCriteriosInicialBimestreDos();

        $this->assertSame(217, $totalSegundaEjecucion);
        $this->assertSame($conteosAntes, $this->conteosCatalogoBase());

        $this->assertSame(
            0,
            TemaSemanal::query()
                ->whereHas('periodoAcademico', fn ($q) => $q
                    ->where('anio_escolar', self::ANIO_ESCOLAR)
                    ->where('bimestre', self::BIMESTRE))
                ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                    ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
                ->whereNotNull('semana_academica_id')
                ->count()
        );

        $this->assertTrue(
            TemaSemanal::query()
                ->whereHas('periodoAcademico', fn ($q) => $q
                    ->where('anio_escolar', self::ANIO_ESCOLAR)
                    ->where('bimestre', self::BIMESTRE))
                ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                    ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
                ->whereNull('semana_academica_id')
                ->whereDoesntHave('competencias')
                ->doesntExist()
        );

        $this->assertTrue(
            TemaSemanal::query()
                ->whereHas('periodoAcademico', fn ($q) => $q
                    ->where('anio_escolar', self::ANIO_ESCOLAR)
                    ->where('bimestre', self::BIMESTRE))
                ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                    ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
                ->whereNull('semana_academica_id')
                ->whereDoesntHave('capacidades')
                ->doesntExist()
        );
    }

    private function provisionarMallasInicial2026(): void
    {
        $service = new MallaCurricularService;

        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            $service->obtenerOProvisionar(self::ANIO_ESCOLAR, CatalogoNivelGrado::NIVEL_INICIAL, $grado);
        }
    }

    /**
     * @return array{areas: int, cursos: int, competencias: int, capacidades: int}
     */
    private function conteosCatalogoBase(): array
    {
        return [
            'areas' => Area::query()->count(),
            'cursos' => CursoCatalogo::query()->count(),
            'competencias' => Competencia::query()->count(),
            'capacidades' => Capacidad::query()->count(),
        ];
    }

    private function conteoCriteriosInicialBimestreDos(): int
    {
        return TemaSemanal::query()
            ->where('activo', true)
            ->whereNull('semana_academica_id')
            ->whereHas('periodoAcademico', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('bimestre', self::BIMESTRE))
            ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('anio_escolar', self::ANIO_ESCOLAR))
            ->count();
    }

    private function conteoPorGrado(string $grado): int
    {
        return TemaSemanal::query()
            ->where('activo', true)
            ->whereNull('semana_academica_id')
            ->whereHas('periodoAcademico', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('bimestre', self::BIMESTRE))
            ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('grado', $grado))
            ->count();
    }
}
