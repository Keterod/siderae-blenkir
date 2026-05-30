<?php

namespace Database\Seeders;

use Database\Seeders\Demo\AsignacionDocenteDemoSeeder;
use Database\Seeders\Demo\AsistenciaCurricularDemoSeeder;
use Database\Seeders\Demo\ConfiguracionBimestralDemoSeeder;
use Database\Seeders\Demo\CriteriosEvaluacionDemoSeeder;
use Database\Seeders\Demo\MallaCurricularDemoSeeder;
use Database\Seeders\Demo\NotasCurricularesDemoSeeder;
use Illuminate\Database\Seeder;

/**
 * Demo operativo del flujo curricular vigente (malla, notas, asistencia).
 * No usa materias/notas/asistencia legacy.
 */
class DemoCurricularOperativoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MallaCurricularDemoSeeder::class,
            ConfiguracionBimestralDemoSeeder::class,
            CriteriosEvaluacionDemoSeeder::class,
            AsignacionDocenteDemoSeeder::class,
            NotasCurricularesDemoSeeder::class,
            AsistenciaCurricularDemoSeeder::class,
        ]);
    }
}
