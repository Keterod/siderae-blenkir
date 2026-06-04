<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;

/**
 * Estudiantes demo para módulo curricular (sin notas, asistencias ni VSE).
 * Idempotente vía updateOrCreate por codigo.
 *
 * Rangos de código:
 * - Inicial: 82000001–82000042 (42)
 * - Primaria/Secundaria: 80000001–80154007 (154)
 * - Total: 196 (sede operativa demo: chilca)
 */
class DemoEstudiantesCurricularesSeeder extends Seeder
{
    public const ANIO_ESCOLAR = '2026';

    public const ESTUDIANTES_POR_AULA = 7;

    public const TOTAL_INICIAL = 42;

    public const TOTAL_PRIMARIA_SECUNDARIA = 154;

    public const TOTAL_ESPERADO = 196;

    /** Códigos demo Inicial (3 grados × 1 sede × 2 secciones × 7). */
    private const CODIGO_INICIO_INICIAL = 82_000_001;

    /** Códigos demo Primaria/Secundaria. */
    private const CODIGO_INICIO_PRIMARIA_SECUNDARIA = 80_000_001;

    /** @var list<string> */
    private const SEDES = ['chilca'];

    /** Segunda sección demo por nivel (pareja con la primera del catálogo). */
    private const SECCION_DEMO_SECUNDARIA_POR_NIVEL = [
        CatalogoNivelGrado::NIVEL_PRIMARIA => 'AMOR',
        CatalogoNivelGrado::NIVEL_SECUNDARIA => 'CICLADO',
    ];

    /** @var array<string, int> */
    private const GRADOS_NUMERICOS_POR_NIVEL = [
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
        $slotIndex = 0;

        $slotIndex = $this->sembrarInicial($slotIndex);
        $this->sembrarPrimariaSecundaria($slotIndex);
    }

    private function sembrarInicial(int $slotIndex): int
    {
        $codigoNum = self::CODIGO_INICIO_INICIAL;

        foreach (CatalogoNivelGrado::GRADOS_INICIAL as $indiceGrado => $grado) {
            foreach (self::SEDES as $sede) {
                foreach ($this->seccionesDemo(CatalogoNivelGrado::NIVEL_INICIAL, $grado) as $seccion) {
                    for ($i = 0; $i < self::ESTUDIANTES_POR_AULA; $i++) {
                        $codigo = str_pad((string) $codigoNum, 8, '0', STR_PAD_LEFT);
                        $codigoNum++;

                        Estudiante::query()->updateOrCreate(
                            ['codigo' => $codigo],
                            [
                                'nombres' => $this->nombresDemo($slotIndex),
                                'apellidos' => $this->apellidosDemo($slotIndex),
                                'fecha_nacimiento' => $this->fechaNacimientoInicial($indiceGrado, $slotIndex),
                                'sexo' => $slotIndex % 2 === 0 ? 'F' : 'M',
                                'grado' => $grado,
                                'seccion' => $seccion,
                                'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
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

        return $slotIndex;
    }

    private function sembrarPrimariaSecundaria(int $slotIndex): void
    {
        $codigoNum = self::CODIGO_INICIO_PRIMARIA_SECUNDARIA;

        foreach (self::GRADOS_NUMERICOS_POR_NIVEL as $nivel => $maxGrado) {
            for ($g = 1; $g <= $maxGrado; $g++) {
                $grado = $g.'°';

                foreach (self::SEDES as $sede) {
                    foreach ($this->seccionesDemo($nivel, $grado) as $seccion) {
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

    private function fechaNacimientoInicial(int $indiceGrado, int $slotIndex): string
    {
        $aniosNacimiento = [2023, 2022, 2021];
        $year = $aniosNacimiento[$indiceGrado] ?? 2022;
        $month = 1 + ($slotIndex % 12);
        $day = 1 + ($slotIndex % 28);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
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

    /**
     * Dos secciones demo por aula, alineadas con SeccionesAulasSeeder.
     *
     * @return list<string>
     */
    private function seccionesDemo(string $nivel, string $gradoEstudiante): array
    {
        if ($nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            return match ($gradoEstudiante) {
                '3 años' => ['ARDILLITAS', 'ESTRELLITAS DE MAR'],
                '4 años' => ['HORMIGUITAS', 'LEONCITOS'],
                '5 años' => ['CANGREJITOS', 'LORITOS'],
                default => ['ARDILLITAS', 'ESTRELLITAS DE MAR'],
            };
        }

        if ($nivel === CatalogoNivelGrado::NIVEL_SECUNDARIA) {
            return ['BASICO', self::SECCION_DEMO_SECUNDARIA_POR_NIVEL[CatalogoNivelGrado::NIVEL_SECUNDARIA]];
        }

        return ['AMISTAD', self::SECCION_DEMO_SECUNDARIA_POR_NIVEL[CatalogoNivelGrado::NIVEL_PRIMARIA]];
    }
}
