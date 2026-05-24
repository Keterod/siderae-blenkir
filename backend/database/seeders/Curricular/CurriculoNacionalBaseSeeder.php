<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\CursoCatalogo;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

class CurriculoNacionalBaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrarNivel(CatalogoNivelGrado::NIVEL_INICIAL, CatalogoInstitucionalBlenkir::definicionInicial());
        $this->sembrarNivel(CatalogoNivelGrado::NIVEL_PRIMARIA, CatalogoInstitucionalBlenkir::definicionPrimaria());
        $this->sembrarNivel(CatalogoNivelGrado::NIVEL_SECUNDARIA, CatalogoInstitucionalBlenkir::definicionSecundaria());
    }

    /**
     * @param  array<string, array{cn: array<string, list<string>>, cursos: list<string>}>  $definicion
     */
    private function sembrarNivel(string $nivel, array $definicion): void
    {
        foreach ($definicion as $nombreArea => $bloque) {
            $area = Area::query()->updateOrCreate(
                ['nombre' => $nombreArea, 'nivel' => $nivel],
                ['activo' => true]
            );

            $competenciasCn = CurriculoNacionalOficial::fusionarConInstitucional($nombreArea, $bloque['cn']);

            foreach ($competenciasCn as $nombreCompetencia => $capacidades) {
                $competencia = Competencia::query()->updateOrCreate(
                    ['area_id' => $area->id, 'nombre' => $nombreCompetencia],
                    ['descripcion' => null, 'activo' => true]
                );

                foreach ($capacidades as $nombreCapacidad) {
                    Capacidad::query()->updateOrCreate(
                        ['competencia_id' => $competencia->id, 'nombre' => $nombreCapacidad],
                        ['descripcion' => null, 'activo' => true]
                    );
                }
            }

            foreach ($bloque['cursos'] as $nombreCurso) {
                CursoCatalogo::query()->updateOrCreate(
                    ['area_id' => $area->id, 'nombre' => $nombreCurso],
                    ['es_institucional' => true, 'activo' => true]
                );
            }
        }
    }
}
