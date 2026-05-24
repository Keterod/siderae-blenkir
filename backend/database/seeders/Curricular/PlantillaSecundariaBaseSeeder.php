<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\PlantillaCurricular;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class PlantillaSecundariaBaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_SECUNDARIA as $grado) {
            PlantillaCurricular::query()->updateOrCreate(
                [
                    'nivel' => CatalogoNivelGrado::NIVEL_SECUNDARIA,
                    'grado' => $grado,
                ],
                [
                    'nombre' => 'Plantilla base Secundaria '.$grado.' (solo CN)',
                    'activo' => true,
                    'detalle_completo' => false,
                ]
            );
        }
    }
}
