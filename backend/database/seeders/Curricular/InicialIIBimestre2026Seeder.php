<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use App\Models\Curricular\SeccionAula;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\MallaCurricularService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Base académica Inicial — II Bimestre 2026 (Chilca).
 * Ejecutar manualmente; no integrado en DatabaseSeeder.
 */
class InicialIIBimestre2026Seeder extends Seeder
{
    private const DATA_PATH = 'database/seeders/data/inicial_ii_bimestre_2026.php';

    /** @var array<string, mixed> */
    private array $dataset = [];

    private string $anioEscolar = '2026';

    private string $bimestre = '2';

    private string $nivel = CatalogoNivelGrado::NIVEL_INICIAL;

    private string $sede = 'chilca';

    /** @var array<string, string> */
    private const ALIAS_CAPACIDADES = [
        'Construye y asume acuerdos y normas' => 'Construye normas y asume acuerdos y leyes',
        'Obtiene información de textos orales.' => 'Obtiene información de textos orales en inglés',
        'Obtiene información de textos orales' => 'Obtiene información de textos orales en inglés',
        'Comprende su cuerpo' => 'Comprende su cuerpo y su movimiento',
    ];


    /** @var array<string, string> */
    private const ALIAS_CURSOS_MALLA = [
        'Raz. Matemático' => 'Razonamiento Matemático',
        'Raz. Verbal' => 'Razonamiento Verbal',
    ];

    private const AREA_PSICOMOTRICIDAD_LEGACY = 'Psicomotricidad';

    private const AREA_EDUCACION_FISICA = 'Educación Física';

    private const CURSO_EDUCACION_FISICA = 'Educación Física';

    /** @var list<array{area: string, legacy: string, canon: string, area_canon?: string}> */
    private const MIGRACIONES_MALLA_CURSO_LEGACY = [
        ['area' => 'Matemática', 'legacy' => 'Raz. Matemático', 'canon' => 'Razonamiento Matemático'],
        ['area' => 'Comunicación', 'legacy' => 'Raz. Verbal', 'canon' => 'Razonamiento Verbal'],
        [
            'area' => self::AREA_PSICOMOTRICIDAD_LEGACY,
            'legacy' => self::CURSO_EDUCACION_FISICA,
            'canon' => self::CURSO_EDUCACION_FISICA,
            'area_canon' => self::AREA_EDUCACION_FISICA,
        ],
    ];

    public function run(): void
    {
        $this->dataset = $this->cargarDataset();
        $this->anioEscolar = (string) ($this->dataset['meta']['anio_escolar'] ?? '2026');
        $this->bimestre = (string) ($this->dataset['meta']['bimestre'] ?? '2');
        $this->sede = (string) ($this->dataset['meta']['sede'] ?? 'chilca');

        $periodo = $this->resolverPeriodoAcademico();

        DB::transaction(function () use ($periodo): void {
            $this->sincronizarCatalogo();
            $this->sincronizarPlantillas();
            $this->sincronizarMallas();
            $this->sincronizarCriterios($periodo);
            $this->sincronizarAulas();
            $this->sincronizarEstudiantesDemo();
        });

        $this->command?->info(sprintf(
            'InicialIIBimestre2026Seeder: completado para %s — Inicial II bimestre %s (%d criterios en dataset).',
            $this->anioEscolar,
            $this->bimestre,
            count($this->dataset['criterios'] ?? []),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function cargarDataset(): array
    {
        $ruta = base_path(self::DATA_PATH);
        if (! is_file($ruta)) {
            throw new RuntimeException("No se encontró el dataset en: {$ruta}");
        }

        $data = require $ruta;
        if (! is_array($data)) {
            throw new RuntimeException('El dataset debe retornar un array.');
        }

        $totalEsperado = (int) ($data['meta']['total_criterios_esperados'] ?? 0);
        $totalReal = count($data['criterios'] ?? []);
        if ($totalEsperado > 0 && $totalReal !== $totalEsperado) {
            throw new RuntimeException(sprintf(
                'El dataset debe tener %d criterios; se encontraron %d.',
                $totalEsperado,
                $totalReal,
            ));
        }

        return $data;
    }

    private function resolverPeriodoAcademico(): PeriodoAcademico
    {
        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', $this->anioEscolar)
            ->where('bimestre', $this->bimestre)
            ->where('activo', true)
            ->first();

        if ($periodo === null) {
            throw new RuntimeException(sprintf(
                'No existe periodo académico activo para %s bimestre %s. Ejecute PeriodosSemanasDemoSeeder antes.',
                $this->anioEscolar,
                $this->bimestre,
            ));
        }

        return $periodo;
    }

    private function sincronizarCatalogo(): void
    {
        /** @var list<array{area: string, curso: string, orden: int}> $cursos */
        $cursos = $this->dataset['cursos_canonicos'] ?? [];

        foreach ($cursos as $def) {
            $area = Area::query()->updateOrCreate(
                ['nombre' => $def['area'], 'nivel' => $this->nivel],
                ['activo' => true],
            );

            CursoCatalogo::query()->updateOrCreate(
                ['area_id' => $area->id, 'nombre' => $def['curso']],
                ['es_institucional' => true, 'activo' => true],
            );
        }
    }

    private function sincronizarPlantillas(): void
    {
        /** @var list<string> $grados */
        $grados = $this->dataset['grados'] ?? CatalogoNivelGrado::GRADOS_INICIAL;
        /** @var list<array{area: string, curso: string, orden: int}> $cursosCanonicos */
        $cursosCanonicos = $this->dataset['cursos_canonicos'] ?? [];

        foreach ($grados as $grado) {
            $plantilla = PlantillaCurricular::query()->updateOrCreate(
                ['nivel' => $this->nivel, 'grado' => $grado],
                [
                    'nombre' => 'Plantilla Inicial II Bimestre '.$grado,
                    'activo' => true,
                    'detalle_completo' => true,
                ],
            );

            foreach ($cursosCanonicos as $def) {
                $area = Area::query()
                    ->where('nivel', $this->nivel)
                    ->where('nombre', $def['area'])
                    ->firstOrFail();

                $curso = CursoCatalogo::query()
                    ->where('area_id', $area->id)
                    ->where('nombre', $def['curso'])
                    ->firstOrFail();

                PlantillaCurso::query()->updateOrCreate(
                    [
                        'plantilla_curricular_id' => $plantilla->id,
                        'area_id' => $area->id,
                        'curso_catalogo_id' => $curso->id,
                    ],
                    ['orden' => $def['orden'], 'activo' => true],
                );
            }
        }
    }

    private function sincronizarMallas(): void
    {
        $service = new MallaCurricularService;
        /** @var list<string> $grados */
        $grados = $this->dataset['grados'] ?? CatalogoNivelGrado::GRADOS_INICIAL;
        /** @var list<array{area: string, curso: string, orden: int}> $cursosCanonicos */
        $cursosCanonicos = $this->dataset['cursos_canonicos'] ?? [];

        foreach ($grados as $grado) {
            $malla = $service->obtenerOProvisionar($this->anioEscolar, $this->nivel, $grado);

            foreach ($cursosCanonicos as $def) {
                $this->asegurarMallaCurso($malla, $def['area'], $def['curso'], $def['orden']);
            }

            $this->normalizarMallaActiva($malla);
        }
    }

    /**
     * Deja exactamente los 10 cursos canónicos activos en la malla Inicial 2026.
     * Migra criterios desde vínculos legacy al canónico antes de desactivar legacy.
     */
    private function normalizarMallaActiva(MallaCurricular $malla): void
    {
        $this->migrarCriteriosDesdeMallaCursoLegacy($malla);

        /** @var list<array{area: string, curso: string, orden: int}> $cursosCanonicos */
        $cursosCanonicos = $this->dataset['cursos_canonicos'] ?? [];
        $idsPermitidos = [];

        foreach ($cursosCanonicos as $def) {
            $mallaCurso = $this->buscarMallaCursoEnArea($malla, $def['area'], $def['curso']);
            if ($mallaCurso !== null) {
                $idsPermitidos[] = $mallaCurso->id;
            }
        }

        if ($idsPermitidos === []) {
            return;
        }

        MallaCurso::query()
            ->where('malla_curricular_id', $malla->id)
            ->where('activo', true)
            ->whereNotIn('id', $idsPermitidos)
            ->update(['activo' => false]);
    }

    private function migrarCriteriosDesdeMallaCursoLegacy(MallaCurricular $malla): void
    {
        foreach (self::MIGRACIONES_MALLA_CURSO_LEGACY as $def) {
            $legacy = $this->buscarMallaCursoEnArea($malla, $def['area'], $def['legacy']);
            if ($legacy === null) {
                continue;
            }

            $areaCanon = $def['area_canon'] ?? $def['area'];
            $canon = $this->buscarMallaCursoEnArea($malla, $areaCanon, $def['canon']);
            if ($canon === null) {
                continue;
            }

            TemaSemanal::query()
                ->where('malla_curso_id', $legacy->id)
                ->update(['malla_curso_id' => $canon->id]);
        }
    }

    private function asegurarMallaCurso(MallaCurricular $malla, string $areaNombre, string $cursoNombre, int $orden): void
    {
        $area = Area::query()
            ->where('nivel', $this->nivel)
            ->where('nombre', $areaNombre)
            ->where('activo', true)
            ->firstOrFail();

        $curso = CursoCatalogo::query()
            ->where('area_id', $area->id)
            ->where('nombre', $cursoNombre)
            ->where('activo', true)
            ->firstOrFail();

        $existente = MallaCurso::query()
            ->where('malla_curricular_id', $malla->id)
            ->where('area_id', $area->id)
            ->where('curso_catalogo_id', $curso->id)
            ->first();

        if ($existente !== null) {
            $existente->update(['orden' => $orden, 'activo' => true]);

            return;
        }

        MallaCurso::query()->create([
            'malla_curricular_id' => $malla->id,
            'area_id' => $area->id,
            'curso_catalogo_id' => $curso->id,
            'orden' => $orden,
            'activo' => true,
        ]);
    }

    private function buscarMallaCursoEnArea(MallaCurricular $malla, string $areaNombre, string $nombreCurso): ?MallaCurso
    {
        return MallaCurso::query()
            ->where('malla_curricular_id', $malla->id)
            ->whereHas('area', fn ($q) => $q
                ->where('nivel', $this->nivel)
                ->where('nombre', $areaNombre))
            ->whereHas('cursoCatalogo', fn ($q) => $q->where('nombre', $nombreCurso))
            ->first();
    }

    private function sincronizarCriterios(PeriodoAcademico $periodo): void
    {
        /** @var list<array<string, mixed>> $criterios */
        $criterios = $this->dataset['criterios'] ?? [];

        foreach ($criterios as $item) {
            $mallaCurso = $this->resolverMallaCurso($item);
            $area = Area::query()->findOrFail($mallaCurso->area_id);
            $competencia = $this->asegurarCompetencia($area, (string) $item['competencia']);
            $capacidad = $this->asegurarCapacidad($competencia, (string) $item['capacidad']);

            $titulo = $this->tituloTema($item);
            $descripcion = trim((string) ($item['criterio'] ?? ''));

            $tema = TemaSemanal::query()->updateOrCreate(
                [
                    'malla_curso_id' => $mallaCurso->id,
                    'periodo_academico_id' => $periodo->id,
                    'semana_academica_id' => null,
                    'titulo' => $titulo,
                ],
                [
                    'descripcion' => $descripcion !== '' ? $descripcion : null,
                    'activo' => true,
                    'creado_por' => null,
                ],
            );

            $this->sincronizarRelacionesTema($tema, $competencia, $capacidad);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function tituloTema(array $item): string
    {
        $tema = trim((string) ($item['tema'] ?? ''));
        if ($tema !== '') {
            return $tema;
        }

        return trim((string) ($item['criterio'] ?? 'Criterio'));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolverMallaCurso(array $item): MallaCurso
    {
        $nombreCurso = (string) $item['curso'];
        $nombreArea = (string) $item['area'];

        $mallaCurso = MallaCurso::query()
            ->where('activo', true)
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', $this->anioEscolar)
                ->where('nivel', $this->nivel)
                ->where('grado', $item['grado']))
            ->whereHas('area', fn ($q) => $q
                ->where('nombre', $nombreArea)
                ->where('nivel', $this->nivel))
            ->whereHas('cursoCatalogo', fn ($q) => $q->where('nombre', $nombreCurso))
            ->first();

        if ($mallaCurso !== null) {
            return $mallaCurso;
        }

        throw new RuntimeException(sprintf(
            'No se encontró malla/curso activo para Inicial %s — área "%s", curso "%s".',
            $item['grado'],
            $nombreArea,
            $nombreCurso,
        ));
    }

    private function asegurarCompetencia(Area $area, string $nombreRaw): Competencia
    {
        $nombre = $this->normalizarTexto($nombreRaw);
        $nombre = rtrim($nombre, '.');

        $competencia = Competencia::query()
            ->where('area_id', $area->id)
            ->where('nombre', $nombre)
            ->first();

        if ($competencia === null) {
            $competencia = Competencia::query()
                ->where('area_id', $area->id)
                ->where('nombre', 'like', $nombre.'%')
                ->where('activo', true)
                ->first();
        }

        if ($competencia === null) {
            $competencia = Competencia::query()->create([
                'area_id' => $area->id,
                'nombre' => $nombre,
                'descripcion' => null,
                'activo' => true,
            ]);
        } elseif (! $competencia->activo) {
            $competencia->update(['activo' => true]);
        }

        return $competencia;
    }

    private function asegurarCapacidad(Competencia $competencia, string $nombreRaw): Capacidad
    {
        $nombre = $this->normalizarTexto($nombreRaw);
        $nombre = rtrim($nombre, '.');
        $nombre = self::ALIAS_CAPACIDADES[$nombre] ?? $nombre;

        $capacidad = Capacidad::query()
            ->where('competencia_id', $competencia->id)
            ->where('nombre', $nombre)
            ->first();

        if ($capacidad === null) {
            $capacidad = Capacidad::query()
                ->where('competencia_id', $competencia->id)
                ->where('nombre', 'like', $nombre.'%')
                ->where('activo', true)
                ->first();
        }

        if ($capacidad === null && isset(self::ALIAS_CAPACIDADES[$nombreRaw])) {
            $capacidad = Capacidad::query()
                ->where('competencia_id', $competencia->id)
                ->where('nombre', self::ALIAS_CAPACIDADES[$nombreRaw])
                ->first();
        }

        if ($capacidad === null) {
            $capacidad = Capacidad::query()->create([
                'competencia_id' => $competencia->id,
                'nombre' => $nombre,
                'descripcion' => null,
                'activo' => true,
            ]);
        } elseif (! $capacidad->activo) {
            $capacidad->update(['activo' => true]);
        }

        return $capacidad;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = trim($texto);
        $texto = preg_replace('/\s+/u', ' ', $texto) ?? $texto;
        $texto = str_replace(['"', '"', '"'], '"', $texto);

        if (mb_strtoupper($texto) === $texto && mb_strlen($texto) > 12) {
            $texto = mb_strtolower($texto);
            $texto = mb_strtoupper(mb_substr($texto, 0, 1)).mb_substr($texto, 1);
        }

        return $texto;
    }

    private function sincronizarRelacionesTema(TemaSemanal $tema, Competencia $competencia, Capacidad $capacidad): void
    {
        $tema->competencias()->sync([$competencia->id]);

        DB::table('tema_capacidades')->where('tema_semanal_id', $tema->id)->delete();
        DB::table('tema_capacidades')->insert([
            'tema_semanal_id' => $tema->id,
            'competencia_id' => $competencia->id,
            'capacidad_id' => $capacidad->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sincronizarAulas(): void
    {
        /** @var array<string, list<string>> $aulas */
        $aulas = $this->dataset['aulas'] ?? [];

        foreach ($aulas as $grado => $nombres) {
            foreach ($nombres as $indice => $nombre) {
                $codigo = Str::slug($nombre, '_');

                SeccionAula::query()->updateOrCreate(
                    [
                        'nivel' => $this->nivel,
                        'grado' => $grado,
                        'nombre' => $nombre,
                    ],
                    [
                        'codigo' => $codigo !== '' ? $codigo : 'seccion_'.($indice + 1),
                        'orden' => $indice + 1,
                        'activo' => true,
                    ],
                );
            }
        }
    }

    private function sincronizarEstudiantesDemo(): void
    {
        /** @var array{codigo_inicio: int, codigo_fin: int, por_aula: int} $config */
        $config = $this->dataset['estudiantes_demo'] ?? [];
        $codigoNum = (int) ($config['codigo_inicio'] ?? 83_000_001);
        $codigoFin = (int) ($config['codigo_fin'] ?? 83_000_052);

        /** @var array<string, list<string>> $aulas */
        $aulas = $this->dataset['aulas'] ?? [];
        $slotIndex = 0;

        foreach ($aulas as $grado => $secciones) {
            $indiceGrado = array_search($grado, CatalogoNivelGrado::GRADOS_INICIAL, true);
            if ($indiceGrado === false) {
                $indiceGrado = 0;
            }

            foreach ($secciones as $seccion) {
                for ($i = 0; $i < (int) ($config['por_aula'] ?? 4); $i++) {
                    if ($codigoNum > $codigoFin) {
                        throw new RuntimeException('Rango de códigos demo agotado antes de completar las 13 aulas.');
                    }

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
                            'nivel' => $this->nivel,
                            'sede' => $this->sede,
                            'anio_escolar' => $this->anioEscolar,
                            'activo' => true,
                        ],
                    );

                    $slotIndex++;
                }
            }
        }
    }

    private function nombresDemo(int $slotIndex): string
    {
        $nombres = [
            'María', 'José', 'Rosa', 'Luis', 'Carmen', 'Carlos', 'Ana', 'Jorge',
            'Lucía', 'Miguel', 'Paola', 'Diego', 'Gabriela', 'Renzo', 'Stefany',
        ];
        $a = $nombres[$slotIndex % count($nombres)];

        return $a;
    }

    private function apellidosDemo(int $slotIndex): string
    {
        $apellidos = [
            'García', 'Quispe', 'López', 'Huamán', 'Flores', 'Ramos', 'Torres',
            'Vásquez', 'Mendoza', 'Castro', 'Rojas', 'Silva', 'Paredes', 'Díaz',
        ];
        $p = $apellidos[$slotIndex % count($apellidos)];
        $m = $apellidos[($slotIndex + 3) % count($apellidos)];

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
}
