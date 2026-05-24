<?php

namespace App\Services\Curricular;

use App\Models\Curricular\EquivalenciaGrado;

class EquivalenciaGradoService
{
    public function aLegacy(string $nivel, string $gradoCurricular): ?string
    {
        if (! in_array($nivel, [CatalogoNivelGrado::NIVEL_PRIMARIA, CatalogoNivelGrado::NIVEL_SECUNDARIA], true)) {
            return null;
        }

        return EquivalenciaGrado::query()
            ->where('nivel', $nivel)
            ->where('grado_curricular', $gradoCurricular)
            ->value('grado_estudiante_legacy');
    }

    public function aCurricular(string $nivel, string $gradoLegacy): ?string
    {
        if (! in_array($nivel, [CatalogoNivelGrado::NIVEL_PRIMARIA, CatalogoNivelGrado::NIVEL_SECUNDARIA], true)) {
            return null;
        }

        return EquivalenciaGrado::query()
            ->where('nivel', $nivel)
            ->where('grado_estudiante_legacy', $gradoLegacy)
            ->value('grado_curricular');
    }
}
