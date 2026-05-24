<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class PlantillaPrimariaInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogoNivelGrado::GRADOS_PRIMARIA as $grado) {
            $detalleCompleto = $grado === '2do';

            $plantilla = PlantillaCurricular::query()->updateOrCreate(
                [
                    'nivel' => CatalogoNivelGrado::NIVEL_PRIMARIA,
                    'grado' => $grado,
                ],
                [
                    'nombre' => 'Plantilla institucional Primaria '.$grado,
                    'activo' => true,
                    'detalle_completo' => $detalleCompleto,
                ]
            );

            if (! $detalleCompleto) {
                continue;
            }

            $orden = 0;
            $areas = Area::query()
                ->where('nivel', CatalogoNivelGrado::NIVEL_PRIMARIA)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            foreach ($areas as $area) {
                $cursos = CursoCatalogo::query()
                    ->where('area_id', $area->id)
                    ->where('es_institucional', true)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();

                foreach ($cursos as $curso) {
                    $orden++;
                    PlantillaCurso::query()->updateOrCreate(
                        [
                            'plantilla_curricular_id' => $plantilla->id,
                            'area_id' => $area->id,
                            'curso_catalogo_id' => $curso->id,
                        ],
                        [
                            'orden' => $orden,
                            'activo' => true,
                        ]
                    );
                }
            }
        }
    }
}
