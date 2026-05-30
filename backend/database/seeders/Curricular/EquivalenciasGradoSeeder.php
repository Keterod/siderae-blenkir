<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\EquivalenciaGrado;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class EquivalenciasGradoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $grado) {
            EquivalenciaGrado::query()->updateOrCreate(
                ['nivel' => CatalogoNivelGrado::NIVEL_INICIAL, 'grado_curricular' => $grado],
                ['grado_estudiante_legacy' => $grado]
            );
        }

        $mapaLegacy = ['1ro' => '1°', '2do' => '2°', '3ro' => '3°', '4to' => '4°', '5to' => '5°', '6to' => '6°'];

        foreach (CatalogoNivelGrado::GRADOS_PRIMARIA as $grado) {
            EquivalenciaGrado::query()->updateOrCreate(
                ['nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA, 'grado_curricular' => $grado],
                ['grado_estudiante_legacy' => $mapaLegacy[$grado]]
            );
        }

        $mapaSec = ['1ro' => '1°', '2do' => '2°', '3ro' => '3°', '4to' => '4°', '5to' => '5°'];
        foreach (CatalogoNivelGrado::GRADOS_SECUNDARIA as $grado) {
            EquivalenciaGrado::query()->updateOrCreate(
                ['nivel' => CatalogoNivelGrado::NIVEL_SECUNDARIA, 'grado_curricular' => $grado],
                ['grado_estudiante_legacy' => $mapaSec[$grado]]
            );
        }
    }
}
