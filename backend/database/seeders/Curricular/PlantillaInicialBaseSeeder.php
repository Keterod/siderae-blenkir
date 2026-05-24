<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\PlantillaCurricular;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class PlantillaInicialBaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            PlantillaCurricular::query()->updateOrCreate(
                [
                    'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
                    'grado' => $grado,
                ],
                [
                    'nombre' => 'Plantilla base Inicial '.$grado,
                    'activo' => true,
                    'detalle_completo' => false,
                ]
            );
        }
    }
}
