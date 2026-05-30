<?php

namespace Database\Seeders\Demo;

use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use Illuminate\Database\Seeder;

/**
 * Asegura configuración bimestral por defecto en los cursos demo de la malla principal.
 */
class ConfiguracionBimestralDemoSeeder extends Seeder
{
    public function run(): void
    {
        $periodo = DemoCurricularContext::periodoBimestreUno();
        $configService = new EvaluacionBimestralConfiguracionService;

        foreach (DemoCurricularContext::mallaCursosPrimaria2do() as $mallaCurso) {
            $configService->asegurarConfiguracionPorDefecto($mallaCurso->id, $periodo->id);
        }
    }
}
