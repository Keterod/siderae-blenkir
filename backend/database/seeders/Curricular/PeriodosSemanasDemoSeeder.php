<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use Illuminate\Database\Seeder;

class PeriodosSemanasDemoSeeder extends Seeder
{
    private const ANIO_DEMO = '2026';

    private const SEMANAS_POR_BIMESTRE = 4;

    public function run(): void
    {
        foreach (['1', '2', '3', '4'] as $bimestre) {
            $periodo = PeriodoAcademico::query()->updateOrCreate(
                ['anio_escolar' => self::ANIO_DEMO, 'bimestre' => $bimestre],
                [
                    'semanas_planificadas' => self::SEMANAS_POR_BIMESTRE,
                    'activo' => true,
                ]
            );

            for ($numero = 1; $numero <= self::SEMANAS_POR_BIMESTRE; $numero++) {
                SemanaAcademica::query()->updateOrCreate(
                    [
                        'periodo_academico_id' => $periodo->id,
                        'numero_semana' => $numero,
                    ],
                    ['activo' => true]
                );
            }
        }
    }
}
