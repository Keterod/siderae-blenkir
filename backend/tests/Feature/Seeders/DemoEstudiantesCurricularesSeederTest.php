<?php

namespace Tests\Feature\Seeders;

use App\Models\Estudiante;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoEstudiantesCurricularesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DemoEstudiantesCurricularesSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function migrate_fresh_seed_crea_estudiantes_demo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertEstudiantesDemoSembrados();
    }

    #[Test]
    public function ejecutar_seeder_dos_veces_no_duplica_estudiantes(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);
        $countPrimera = Estudiante::query()->count();

        $this->seed(DemoEstudiantesCurricularesSeeder::class);
        $countSegunda = Estudiante::query()->count();

        $this->assertSame($countPrimera, $countSegunda);
        $this->assertSame(DemoEstudiantesCurricularesSeeder::TOTAL_ESPERADO, $countSegunda);
    }

    #[Test]
    public function existen_estudiantes_primaria_2_grado_a_chilca_2026(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('primaria', '2°', 'A', 'chilca')
        );
    }

    #[Test]
    public function existen_estudiantes_primaria_2_grado_a_auquimarca_2026(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('primaria', '2°', 'A', 'auquimarca')
        );
    }

    #[Test]
    public function existen_estudiantes_secundaria_1_grado_a_chilca_2026(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('secundaria', '1°', 'A', 'chilca')
        );
    }

    #[Test]
    public function existen_estudiantes_secundaria_1_grado_b_auquimarca_2026(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('secundaria', '1°', 'B', 'auquimarca')
        );
    }

    #[Test]
    public function existen_84_estudiantes_inicial(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::TOTAL_INICIAL,
            Estudiante::query()->where('nivel', 'inicial')->count()
        );
    }

    #[Test]
    public function existen_estudiantes_inicial_3_anos_a_chilca_2026(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('inicial', '3 años', 'A', 'chilca')
        );
    }

    #[Test]
    public function total_esperado_de_estudiantes_es_392(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(DemoEstudiantesCurricularesSeeder::TOTAL_ESPERADO, Estudiante::query()->count());
    }

    #[Test]
    public function todos_los_estudiantes_demo_estan_activos(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $this->assertSame(0, Estudiante::query()->where('activo', false)->count());
        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::TOTAL_ESPERADO,
            Estudiante::query()->where('activo', true)->count()
        );
    }

    #[Test]
    public function niveles_incluyen_inicial_primaria_y_secundaria(): void
    {
        $this->seed(DemoEstudiantesCurricularesSeeder::class);

        $niveles = Estudiante::query()->distinct()->pluck('nivel')->sort()->values()->all();

        $this->assertSame(['inicial', 'primaria', 'secundaria'], $niveles);
    }

    private function assertEstudiantesDemoSembrados(): void
    {
        $this->assertSame(DemoEstudiantesCurricularesSeeder::TOTAL_ESPERADO, Estudiante::query()->count());
        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('primaria', '2°', 'A', 'chilca')
        );
        $this->assertSame(
            DemoEstudiantesCurricularesSeeder::ESTUDIANTES_POR_AULA,
            $this->contarAula('inicial', '3 años', 'A', 'chilca')
        );
    }

    private function contarAula(string $nivel, string $grado, string $seccion, string $sede): int
    {
        return Estudiante::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('seccion', $seccion)
            ->where('sede', $sede)
            ->where('anio_escolar', DemoEstudiantesCurricularesSeeder::ANIO_ESCOLAR)
            ->count();
    }
}
