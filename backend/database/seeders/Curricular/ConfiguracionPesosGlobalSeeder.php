<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Services\Curricular\PesoEvaluacionResolver;
use Illuminate\Database\Seeder;

class ConfiguracionPesosGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $resolver = new PesoEvaluacionResolver;
        $pesos = $resolver->pesosPorDefecto();

        ConfiguracionPesoEvaluacion::query()->updateOrCreate(
            [
                'nivel' => null,
                'grado' => null,
                'area_id' => null,
                'curso_catalogo_id' => null,
            ],
            [
                'peso_cuaderno' => $pesos['cuaderno'],
                'peso_libro' => $pesos['libro'],
                'peso_tarea' => $pesos['tarea'],
                'activo' => true,
            ]
        );
    }
}
