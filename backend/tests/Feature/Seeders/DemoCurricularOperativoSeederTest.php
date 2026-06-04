<?php

namespace Tests\Feature\Seeders;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Demo\DemoCurricularContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DemoCurricularOperativoSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function database_seeder_deja_demo_curricular_operativo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('estudiantes', 196);

        $this->assertTrue(
            MallaCurricular::query()
                ->where('anio_escolar', DemoCurricularContext::ANIO_ESCOLAR)
                ->where('nivel', DemoCurricularContext::NIVEL_PRIMARIA)
                ->where('grado', DemoCurricularContext::GRADO_CURRICULAR_PRIMARIA)
                ->exists()
        );

        $this->assertGreaterThanOrEqual(2, DocenteCursoAula::query()->where('activo', true)->count());
        $this->assertGreaterThanOrEqual(2, TemaSemanal::query()->where('activo', true)->count());
        $this->assertGreaterThanOrEqual(7, NotaSemanal::query()->count());
        $this->assertGreaterThanOrEqual(7, EvalBimResultado::query()->count());
        $this->assertGreaterThanOrEqual(35, AsistenciaDiaria::query()->count());

        $this->assertSame(
            7,
            Estudiante::query()
                ->where('anio_escolar', DemoCurricularContext::ANIO_ESCOLAR)
                ->where('nivel', DemoCurricularContext::NIVEL_PRIMARIA)
                ->where('grado', DemoCurricularContext::GRADO_ESTUDIANTE_PRIMARIA)
                ->where('seccion', DemoCurricularContext::SECCION_PRINCIPAL)
                ->where('sede', DemoCurricularContext::SEDE_PRINCIPAL)
                ->count()
        );

        $this->assertDatabaseCount('materias', 0);
        $this->assertDatabaseCount('notas', 0);
        $this->assertDatabaseCount('asistencias', 0);
    }
}
