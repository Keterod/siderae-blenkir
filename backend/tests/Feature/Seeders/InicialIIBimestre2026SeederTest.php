<?php

namespace Tests\Feature\Seeders;

use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use Database\Seeders\Curricular\InicialIIBimestre2026Seeder;
use Database\Seeders\CurricularModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InicialIIBimestre2026SeederTest extends TestCase
{
    use RefreshDatabase;

    private const ANIO_ESCOLAR = '2026';

    private const BIMESTRE = '2';

    private const TOTAL_CRITERIOS = 289;

    private const CURSOS_ACTIVOS_POR_MALLA = 10;

    /** @var list<array{0: string, 1: string}> area, curso */
    private const PARES_CANONICOS_ACTIVOS = [
        ['Matemática', 'Aritmética'],
        ['Matemática', 'Geometría'],
        ['Matemática', 'Razonamiento Matemático'],
        ['Comunicación', 'Comunicación'],
        ['Comunicación', 'Razonamiento Verbal'],
        ['Comunicación', 'Aprestamiento'],
        ['Ciencia y Tecnología', 'Ciencia y Tecnología'],
        ['Personal Social', 'Personal Social'],
        ['Inglés', 'Inglés'],
        ['Educación Física', 'Educación Física'],
    ];

    /** @var list<array{0: string, 1: string}> area, curso legacy */
    private const PARES_LEGACY_INACTIVOS = [
        ['Matemática', 'Raz. Matemático'],
        ['Comunicación', 'Raz. Verbal'],
        ['Psicomotricidad', 'Educación Física'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurricularModuleSeeder::class);
    }

    #[Test]
    public function dataset_fuente_tiene_289_criterios(): void
    {
        $data = require base_path('database/seeders/data/inicial_ii_bimestre_2026.php');

        $this->assertCount(self::TOTAL_CRITERIOS, $data['criterios']);
    }

    #[Test]
    public function seeder_crea_estructura_inicial_ii_bimestre_2026(): void
    {
        $asignacionesAntes = DB::table('docente_curso_aulas')->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)->count();

        $this->seed(InicialIIBimestre2026Seeder::class);

        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            $malla = MallaCurricular::query()
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('grado', $grado)
                ->first();

            $this->assertNotNull($malla, "Falta malla para {$grado}");

            $activos = MallaCurso::query()
                ->where('malla_curricular_id', $malla->id)
                ->where('activo', true)
                ->count();

            $this->assertSame(
                self::CURSOS_ACTIVOS_POR_MALLA,
                $activos,
                "Malla {$grado} debe tener exactamente 10 cursos activos",
            );

            foreach (self::PARES_CANONICOS_ACTIVOS as [$area, $curso]) {
                $this->assertTrue(
                    MallaCurso::query()
                        ->where('malla_curricular_id', $malla->id)
                        ->where('activo', true)
                        ->whereHas('area', fn ($q) => $q
                            ->where('nombre', $area)
                            ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
                        ->whereHas('cursoCatalogo', fn ($q) => $q->where('nombre', $curso))
                        ->exists(),
                    "Malla {$grado} debe tener activo {$area} / {$curso}",
                );
            }

            foreach (self::PARES_LEGACY_INACTIVOS as [$area, $curso]) {
                $this->assertFalse(
                    MallaCurso::query()
                        ->where('malla_curricular_id', $malla->id)
                        ->where('activo', true)
                        ->whereHas('area', fn ($q) => $q
                            ->where('nombre', $area)
                            ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL))
                        ->whereHas('cursoCatalogo', fn ($q) => $q->where('nombre', $curso))
                        ->exists(),
                    "Malla {$grado} no debe tener activo legacy {$area} / {$curso}",
                );
            }
        }

        $aprestamiento = CursoCatalogo::query()
            ->where('nombre', 'Aprestamiento')
            ->whereHas('area', fn ($q) => $q
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('nombre', 'Comunicación'))
            ->first();

        $this->assertNotNull($aprestamiento);

        $educacionFisica = CursoCatalogo::query()
            ->where('nombre', 'Educación Física')
            ->whereHas('area', fn ($q) => $q
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('nombre', 'Educación Física'))
            ->first();

        $this->assertNotNull($educacionFisica);

        $totalCriterios = TemaSemanal::query()
            ->where('activo', true)
            ->whereNull('semana_academica_id')
            ->whereHas('periodoAcademico', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('bimestre', self::BIMESTRE))
            ->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                ->where('anio_escolar', self::ANIO_ESCOLAR))
            ->count();

        $this->assertSame(self::TOTAL_CRITERIOS, $totalCriterios);

        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            $this->assertSame(9, $this->conteoCriteriosEducacionFisicaPorGrado($grado));
            $this->assertSame(7, $this->conteoCriteriosInglesPorGrado($grado));
        }

        $estudiantesNuevos = Estudiante::query()
            ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
            ->where('sede', 'chilca')
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->whereBetween('codigo', ['83000001', '83000052'])
            ->count();

        $this->assertSame(52, $estudiantesNuevos);

        $porAula = Estudiante::query()
            ->whereBetween('codigo', ['83000001', '83000052'])
            ->selectRaw('grado, seccion, count(*) as total')
            ->groupBy('grado', 'seccion')
            ->get();

        $this->assertCount(13, $porAula);
        foreach ($porAula as $fila) {
            $this->assertSame(4, (int) $fila->total);
        }

        $this->assertSame(
            $asignacionesAntes,
            DB::table('docente_curso_aulas')->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)->count(),
        );

        $this->seed(InicialIIBimestre2026Seeder::class);

        $this->assertSame(self::TOTAL_CRITERIOS, $this->conteoCriteriosInicialBimestreDos());
    }

    #[Test]
    public function estudiantes_legacy_820_no_se_modifican(): void
    {
        Estudiante::query()->create([
            'codigo' => '82000001',
            'nombres' => 'Legacy',
            'apellidos' => 'Estudiante Demo',
            'fecha_nacimiento' => '2023-01-01',
            'sexo' => 'M',
            'grado' => '3 años',
            'seccion' => 'ARDILLITAS',
            'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
            'sede' => 'chilca',
            'anio_escolar' => self::ANIO_ESCOLAR,
            'activo' => true,
        ]);

        $this->seed(InicialIIBimestre2026Seeder::class);

        $legacy = Estudiante::query()->where('codigo', '82000001')->first();
        $this->assertNotNull($legacy);
        $this->assertSame('Legacy', $legacy->nombres);
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

    private function conteoCriteriosEducacionFisicaPorGrado(string $grado): int
    {
        return TemaSemanal::query()
            ->where('activo', true)
            ->whereNull('semana_academica_id')
            ->whereHas('periodoAcademico', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('bimestre', self::BIMESTRE))
            ->whereHas('mallaCurso', fn ($q) => $q
                ->whereHas('mallaCurricular', fn ($mq) => $mq
                    ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                    ->where('anio_escolar', self::ANIO_ESCOLAR)
                    ->where('grado', $grado))
                ->whereHas('area', fn ($aq) => $aq->where('nombre', 'Educación Física'))
                ->whereHas('cursoCatalogo', fn ($cq) => $cq->where('nombre', 'Educación Física')))
            ->count();
    }

    private function conteoCriteriosInglesPorGrado(string $grado): int
    {
        return TemaSemanal::query()
            ->where('activo', true)
            ->whereNull('semana_academica_id')
            ->whereHas('periodoAcademico', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('bimestre', self::BIMESTRE))
            ->whereHas('mallaCurso', fn ($q) => $q
                ->whereHas('mallaCurricular', fn ($mq) => $mq
                    ->where('nivel', CatalogoNivelGrado::NIVEL_INICIAL)
                    ->where('anio_escolar', self::ANIO_ESCOLAR)
                    ->where('grado', $grado))
                ->whereHas('area', fn ($aq) => $aq->where('nombre', 'Inglés'))
                ->whereHas('cursoCatalogo', fn ($cq) => $cq->where('nombre', 'Inglés')))
            ->count();
    }
}
