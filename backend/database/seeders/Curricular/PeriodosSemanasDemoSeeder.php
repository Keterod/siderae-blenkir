<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\AnioEscolar;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use Illuminate\Database\Seeder;

class PeriodosSemanasDemoSeeder extends Seeder
{
    private const ANIO_DEMO = '2026';

    private const SEMANAS_POR_BIMESTRE = 4;

    public function run(): void
    {
        $anioEscolar = AnioEscolar::query()->updateOrCreate(
            ['anio' => self::ANIO_DEMO],
            [
                'nombre' => 'Año escolar '.self::ANIO_DEMO,
                'estado' => 'activo',
                'es_activo' => true,
            ],
        );

        foreach (['1', '2', '3', '4'] as $bimestre) {
            $periodo = PeriodoAcademico::query()->updateOrCreate(
                ['anio_escolar' => self::ANIO_DEMO, 'bimestre' => $bimestre],
                [
                    'anio_escolar_id' => $anioEscolar->id,
                    'semanas_planificadas' => self::SEMANAS_POR_BIMESTRE,
                    'activo' => true,
                    'estado' => 'activo',
                    'es_vigente' => $bimestre === '1',
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
