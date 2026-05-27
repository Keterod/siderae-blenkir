<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use Illuminate\Database\Seeder;

/**
 * Estudiantes demo para módulo curricular (sin notas, asistencias ni VSE).
 * Idempotente vía updateOrCreate por codigo (rango 80xxxxxx, distinto de DemoAcademicDataSeeder).
 */
class DemoEstudiantesCurricularesSeeder extends Seeder
{
    public const ANIO_ESCOLAR = '2026';

    public const ESTUDIANTES_POR_AULA = 7;

    public const TOTAL_ESPERADO = 308;

    private const CODIGO_INICIO = 80_000_001;

    /** @var list<string> */
    private const SEDES = ['chilca', 'auquimarca'];

    /** @var list<string> */
    private const SECCIONES = ['A', 'B'];

    /** @var array<string, int> */
    private const GRADOS_POR_NIVEL = [
        'primaria' => 6,
        'secundaria' => 5,
    ];

    /** @var list<string> */
    private const NOMBRES = [
        'María', 'José', 'Rosa', 'Luis', 'Carmen', 'Carlos', 'Ana', 'Jorge',
        'Lucía', 'Miguel', 'Paola', 'Diego', 'Gabriela', 'Renzo', 'Stefany',
        'Martín', 'Valeria', 'Andrea', 'Fabrizio', 'Camila',
    ];

    /** @var list<string> */
    private const APELLIDOS = [
        'García', 'Quispe', 'López', 'Huamán', 'Flores', 'Ramos', 'Torres',
        'Vásquez', 'Mendoza', 'Castro', 'Rojas', 'Silva', 'Paredes', 'Díaz',
        'Espinoza', 'Medina', 'Salazar', 'Córdova', 'Valverde', 'Ponce',
    ];

    public function run(): void
    {
        $codigoNum = self::CODIGO_INICIO;
        $slotIndex = 0;

        foreach (self::GRADOS_POR_NIVEL as $nivel => $maxGrado) {
            for ($g = 1; $g <= $maxGrado; $g++) {
                $grado = $g.'°';

                foreach (self::SEDES as $sede) {
                    foreach (self::SECCIONES as $seccion) {
                        for ($i = 0; $i < self::ESTUDIANTES_POR_AULA; $i++) {
                            $codigo = str_pad((string) $codigoNum, 8, '0', STR_PAD_LEFT);
                            $codigoNum++;

                            Estudiante::query()->updateOrCreate(
                                ['codigo' => $codigo],
                                [
                                    'nombres' => $this->nombresDemo($slotIndex),
                                    'apellidos' => $this->apellidosDemo($slotIndex),
                                    'fecha_nacimiento' => $this->fechaNacimientoDemo($nivel, $g, $slotIndex),
                                    'sexo' => $slotIndex % 2 === 0 ? 'F' : 'M',
                                    'grado' => $grado,
                                    'seccion' => $seccion,
                                    'nivel' => $nivel,
                                    'sede' => $sede,
                                    'anio_escolar' => self::ANIO_ESCOLAR,
                                    'activo' => true,
                                ]
                            );

                            $slotIndex++;
                        }
                    }
                }
            }
        }
    }

    private function nombresDemo(int $slotIndex): string
    {
        $a = self::NOMBRES[$slotIndex % count(self::NOMBRES)];
        if ($slotIndex % 5 === 0) {
            $b = self::NOMBRES[($slotIndex + 7) % count(self::NOMBRES)];

            return $a.' '.$b;
        }

        return $a;
    }

    private function apellidosDemo(int $slotIndex): string
    {
        $p = self::APELLIDOS[$slotIndex % count(self::APELLIDOS)];
        $m = self::APELLIDOS[($slotIndex + 3) % count(self::APELLIDOS)];

        return $p.' '.$m;
    }

    private function fechaNacimientoDemo(string $nivel, int $grado, int $slotIndex): string
    {
        $year = $nivel === 'primaria'
            ? 2020 - ($grado - 1)
            : 2014 - ($grado - 1);

        $month = 1 + ($slotIndex % 12);
        $day = 1 + ($slotIndex % 28);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
