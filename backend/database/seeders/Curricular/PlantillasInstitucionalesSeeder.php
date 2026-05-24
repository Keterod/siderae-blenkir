<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class PlantillasInstitucionalesSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrarPorNivel(CatalogoNivelGrado::NIVEL_INICIAL, CatalogoNivelGrado::GRADOS_INICIAL);
        $this->sembrarPorNivel(CatalogoNivelGrado::NIVEL_PRIMARIA, CatalogoNivelGrado::GRADOS_PRIMARIA);
        $this->sembrarPorNivel(CatalogoNivelGrado::NIVEL_SECUNDARIA, CatalogoNivelGrado::GRADOS_SECUNDARIA);
    }

    /**
     * @param  list<string>  $grados
     */
    private function sembrarPorNivel(string $nivel, array $grados): void
    {
        foreach ($grados as $grado) {
            $plantilla = PlantillaCurricular::query()->updateOrCreate(
                ['nivel' => $nivel, 'grado' => $grado],
                [
                    'nombre' => 'Plantilla institucional '.ucfirst($nivel).' '.$grado,
                    'activo' => true,
                    'detalle_completo' => true,
                ]
            );

            $this->sincronizarCursosPlantilla($plantilla, $nivel);
        }
    }

    private function sincronizarCursosPlantilla(PlantillaCurricular $plantilla, string $nivel): void
    {
        $cursos = CursoCatalogo::query()
            ->where('es_institucional', true)
            ->where('activo', true)
            ->whereHas('area', fn ($q) => $q->where('nivel', $nivel)->where('activo', true))
            ->with('area')
            ->get()
            ->sortBy(fn (CursoCatalogo $c) => $c->area->nombre.' '.$c->nombre)
            ->values();

        $orden = 0;
        foreach ($cursos as $curso) {
            $orden++;
            PlantillaCurso::query()->updateOrCreate(
                [
                    'plantilla_curricular_id' => $plantilla->id,
                    'area_id' => $curso->area_id,
                    'curso_catalogo_id' => $curso->id,
                ],
                ['orden' => $orden, 'activo' => true]
            );
        }
    }
}
