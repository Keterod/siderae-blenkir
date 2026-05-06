<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\User;
use App\Models\VariableSocioeconomica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Datos académicos de demostración (sede Chilca, año 2026, bimestre 1).
 * Idempotente vía updateOrCreate en las claves naturales disponibles en el esquema.
 *
 * Requiere que exista el usuario docente demo (DemoUsersSeeder: docente@siderae.test)
 * porque asistencias.registrado_por es NOT NULL y referencia users.
 */
class DemoAcademicDataSeeder extends Seeder
{
    private const SEDE = 'chilca';

    private const ANIO_ESCOLAR = '2026';

    private const BIMESTRE = '1';

    /** @var list<string> */
    private const MATERIAS = ['Matemática', 'Comunicación', 'Historia', 'Inglés'];

    /**
     * Códigos ficticios (DNI demo 8 dígitos) con patrón de riesgo académico / asistencia / VSE más vulnerables.
     * Índices de generación: 5, 28, 41, 63 (primaria); 122, 138, 156, 178 (secundaria, offset +120).
     *
     * @var list<string>
     */
    private const RISK_DEMO_CODIGOS = [
        '70000006',
        '70000029',
        '70000042',
        '70000064',
        '70000123',
        '70000139',
        '70000157',
        '70000179',
    ];

    /** @var list<string> */
    private const SEMANAS_INICIO_DEMO = ['2026-04-07', '2026-04-14'];

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
        $registradoPor = User::query()->where('email', 'docente@siderae.test')->value('id');

        if ($registradoPor === null) {
            throw new \RuntimeException(
                'DemoAcademicDataSeeder requiere el usuario docente@siderae.test. Ejecuta DemoUsersSeeder antes.'
            );
        }

        $this->seedMaterias();

        $materiasPorClave = $this->materiasIndexadas();

        $codigoNum = 70_000_001;
        $slotIndex = 0;

        foreach (['primaria' => 6, 'secundaria' => 5] as $nivel => $maxGrado) {
            for ($g = 1; $g <= $maxGrado; $g++) {
                $grado = $g.'°';
                foreach (['A', 'B'] as $seccion) {
                    for ($i = 0; $i < 10; $i++) {
                        $codigo = str_pad((string) $codigoNum, 8, '0', STR_PAD_LEFT);
                        $codigoNum++;

                        $estudiante = Estudiante::query()->updateOrCreate(
                            ['codigo' => $codigo],
                            [
                                'nombres' => $this->nombresDemo($slotIndex),
                                'apellidos' => $this->apellidosDemo($slotIndex),
                                'fecha_nacimiento' => $this->fechaNacimientoDemo($nivel, $g, $slotIndex),
                                'sexo' => $slotIndex % 2 === 0 ? 'F' : 'M',
                                'grado' => $grado,
                                'seccion' => $seccion,
                                'nivel' => $nivel,
                                'sede' => self::SEDE,
                                'anio_escolar' => self::ANIO_ESCOLAR,
                                'activo' => true,
                            ]
                        );

                        $isRisk = in_array($codigo, self::RISK_DEMO_CODIGOS, true);

                        $this->seedVariableSocioeconomica($estudiante, $slotIndex, $isRisk);
                        $this->seedNotas($estudiante, $materiasPorClave, $codigo, $isRisk);
                        $this->seedAsistencias($estudiante, (int) $registradoPor, $codigo, $isRisk);

                        $slotIndex++;
                    }
                }
            }
        }
    }

    private function seedMaterias(): void
    {
        foreach (['primaria' => 6, 'secundaria' => 5] as $nivel => $maxGrado) {
            for ($g = 1; $g <= $maxGrado; $g++) {
                $grado = $g.'°';
                foreach (self::MATERIAS as $nombre) {
                    Materia::query()->updateOrCreate(
                        [
                            'nombre' => $nombre,
                            'nivel' => $nivel,
                            'grado' => $grado,
                            'anio_escolar' => self::ANIO_ESCOLAR,
                            'sede' => self::SEDE,
                        ],
                        ['activo' => true]
                    );
                }
            }
        }
    }

    /**
     * @return Collection<string, Collection<int, Materia>> clave "primaria|4°" -> materias por nombre
     */
    private function materiasIndexadas(): Collection
    {
        $rows = Materia::query()
            ->where('sede', self::SEDE)
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->get();

        return $rows->groupBy(fn (Materia $m) => $m->nivel.'|'.$m->grado)
            ->map(fn (Collection $group) => $group->keyBy('nombre'));
    }

    private function seedVariableSocioeconomica(Estudiante $estudiante, int $slotIndex, bool $isRisk): void
    {
        if ($isRisk) {
            $composicion = $slotIndex % 2 === 0 ? 'monoparental' : 'extendida';
            $nivelSec = 'bajo';
            $internet = false;
            $distancia = 18.5 + ($slotIndex % 5);
        } else {
            $composiciones = ['nuclear', 'nuclear', 'monoparental', 'extendida', 'otros'];
            $composicion = $composiciones[$slotIndex % count($composiciones)];
            $niveles = ['medio', 'medio', 'alto', 'bajo', 'medio'];
            $nivelSec = $niveles[$slotIndex % count($niveles)];
            $internet = $slotIndex % 7 !== 0;
            $distancia = round(0.8 + (($slotIndex * 17) % 120) / 10, 2);
        }

        VariableSocioeconomica::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => self::ANIO_ESCOLAR,
            ],
            [
                'composicion_familiar' => $composicion,
                'nivel_socioeconomico' => $nivelSec,
                'acceso_internet' => $internet,
                'distancia_colegio_km' => $distancia,
            ]
        );
    }

    /**
     * @param  Collection<string, Collection<int, Materia>>  $materiasPorClave
     */
    private function seedNotas(
        Estudiante $estudiante,
        Collection $materiasPorClave,
        string $codigo,
        bool $isRisk
    ): void {
        $clave = $estudiante->nivel.'|'.$estudiante->grado;
        $grupo = $materiasPorClave->get($clave);

        if ($grupo === null) {
            throw new \RuntimeException("No hay materias catalogadas para {$clave} (sede ".self::SEDE.').');
        }

        foreach (self::MATERIAS as $mi => $nombreMateria) {
            $materia = $grupo->get($nombreMateria);

            if ($materia === null) {
                throw new \RuntimeException("Falta materia {$nombreMateria} en {$clave}.");
            }

            $nota = $this->notaDemo($codigo, $mi, $isRisk);
            $conducta = $isRisk
                ? (float) (10 + ((int) $codigo + $mi) % 3)
                : (float) (14 + ((int) $codigo + $mi * 2) % 5);

            Nota::query()->updateOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'materia_id' => $materia->id,
                    'anio_escolar' => self::ANIO_ESCOLAR,
                    'bimestre' => self::BIMESTRE,
                ],
                [
                    'curso' => $materia->nombre,
                    'nota' => $nota,
                    'nota_conducta' => $conducta,
                ]
            );
        }
    }

    private function seedAsistencias(Estudiante $estudiante, int $registradoPor, string $codigo, bool $isRisk): void
    {
        $n = (int) $codigo;

        foreach (self::SEMANAS_INICIO_DEMO as $weekIdx => $semanaInicio) {
            if ($isRisk) {
                $estado = $weekIdx === 0
                    ? (($n % 2 === 0) ? 'falta' : 'tardanza')
                    : 'falta';
            } else {
                $h = ($n + $weekIdx * 11) % 10;
                $estado = match (true) {
                    $h < 7 => 'presente',
                    $h === 7 => 'tardanza',
                    default => 'falta',
                };
            }

            Asistencia::query()->updateOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'semana_inicio' => $semanaInicio,
                    'anio_escolar' => self::ANIO_ESCOLAR,
                    'bimestre' => self::BIMESTRE,
                ],
                [
                    'estado' => $estado,
                    'registrado_por' => $registradoPor,
                ]
            );
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

    private function notaDemo(string $codigo, int $materiaIndex, bool $isRisk): float
    {
        $n = (int) $codigo;

        if ($isRisk) {
            return (float) (5 + (($n + $materiaIndex * 5) % 5));
        }

        if (($n + $materiaIndex * 3) % 11 === 0) {
            return (float) (9 + (($n + $materiaIndex) % 3));
        }

        return (float) (12 + (($n + $materiaIndex * 7) % 7));
    }
}
