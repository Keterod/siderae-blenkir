<?php

namespace Database\Seeders;

use Database\Seeders\Curricular\ConfiguracionPesosGlobalSeeder;
use Database\Seeders\Curricular\EvalBimEscalaLogroSeeder;
use Database\Seeders\Curricular\CurriculoNacionalBaseSeeder;
use Database\Seeders\Curricular\EquivalenciasGradoSeeder;
use Database\Seeders\Curricular\PeriodosSemanasDemoSeeder;
use Database\Seeders\Curricular\PlantillasInstitucionalesSeeder;
use Illuminate\Database\Seeder;

class CurricularModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EquivalenciasGradoSeeder::class,
            CurriculoNacionalBaseSeeder::class,
            PlantillasInstitucionalesSeeder::class,
            PeriodosSemanasDemoSeeder::class,
            ConfiguracionPesosGlobalSeeder::class,
            EvalBimEscalaLogroSeeder::class,
        ]);
    }
}
