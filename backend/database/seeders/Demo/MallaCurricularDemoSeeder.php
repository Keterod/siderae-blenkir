<?php

namespace Database\Seeders\Demo;

use App\Services\Curricular\MallaCurricularService;
use Illuminate\Database\Seeder;

/**
 * Provisiona mallas curriculares demo a partir de plantillas institucionales.
 */
class MallaCurricularDemoSeeder extends Seeder
{
    public function run(): void
    {
        $service = new MallaCurricularService;

        $service->obtenerOProvisionar(
            DemoCurricularContext::ANIO_ESCOLAR,
            DemoCurricularContext::NIVEL_PRIMARIA,
            DemoCurricularContext::GRADO_CURRICULAR_PRIMARIA,
        );

        $service->obtenerOProvisionar(
            DemoCurricularContext::ANIO_ESCOLAR,
            DemoCurricularContext::NIVEL_SECUNDARIA,
            DemoCurricularContext::GRADO_CURRICULAR_SECUNDARIA,
        );

        $service->obtenerOProvisionar(
            DemoCurricularContext::ANIO_ESCOLAR,
            DemoCurricularContext::NIVEL_INICIAL,
            DemoCurricularContext::GRADO_CURRICULAR_INICIAL,
        );
    }
}
