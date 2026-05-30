<?php

namespace Database\Seeders\Curricular;

use App\Services\Curricular\ComponenteCalificacionNivelService;
use Illuminate\Database\Seeder;

class ComponentesCalificacionNivelSeeder extends Seeder
{
    private const ANIO_DEMO = '2026';

    public function run(): void
    {
        (new ComponenteCalificacionNivelService)->asegurarDefaults(self::ANIO_DEMO);
    }
}
