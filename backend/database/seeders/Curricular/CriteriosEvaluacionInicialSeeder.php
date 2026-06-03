<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Carga criterios de evaluación (temas semanales) de Inicial desde Untitled-1.php.
 * Seeder aislado: no está integrado en CurricularModuleSeeder.
 */
class CriteriosEvaluacionInicialSeeder extends Seeder
{
    private const ANIO_ESCOLAR = '2026';

    private const BIMESTRE = '2';

    private const NIVEL = CatalogoNivelGrado::NIVEL_INICIAL;

    private const TOTAL_CRITERIOS_ESPERADOS = 217;

    /** @var list<string> */
    private const CAMPOS_REQUERIDOS = [
        'grado',
        'area',
        'curso',
        'bimestre',
        'criterio',
        'descripcion',
        'competencia',
        'capacidad',
        'orden',
        'activo',
    ];

    /** @var list<string> */
    private const AREAS_PERMITIDAS = [
        'Matemática',
        'Comunicación',
        'Ciencia y Tecnología',
        'Personal Social',
        'Psicomotricidad',
        'Inglés',
    ];

    /**
     * Equivalencias seguras catálogo → BD (sin inventar competencias/capacidades).
     *
     * @var array<string, string>
     */
    private const ALIAS_CAPACIDADES = [
        'Construye y asume acuerdos y normas' => 'Construye normas y asume acuerdos y leyes',
    ];

    public function run(): void
    {
        $catalogo = $this->cargarCatalogo();
        $periodo = $this->resolverPeriodoAcademico();

        $creados = 0;
        $actualizados = 0;

        DB::transaction(function () use ($catalogo, $periodo, &$creados, &$actualizados): void {
            foreach ($catalogo as $item) {
                $this->validarItem($item);

                $mallaCurso = $this->resolverMallaCurso($item);
                $competencia = $this->resolverCompetencia($item);
                $capacidad = $this->resolverCapacidad($item, $competencia);

                $tema = TemaSemanal::query()->updateOrCreate(
                    [
                        'malla_curso_id' => $mallaCurso->id,
                        'periodo_academico_id' => $periodo->id,
                        'semana_academica_id' => null,
                        'titulo' => $item['criterio'],
                    ],
                    [
                        'descripcion' => $item['descripcion'],
                        'activo' => (bool) $item['activo'],
                        'creado_por' => null,
                    ],
                );

                $this->sincronizarRelaciones($tema, $competencia, $capacidad);

                if ($tema->wasRecentlyCreated) {
                    $creados++;
                } else {
                    $actualizados++;
                }
            }
        });

        $this->command?->info(sprintf(
            'CriteriosEvaluacionInicialSeeder: %d criterios procesados (%d creados, %d actualizados) para Inicial II bimestre %s.',
            count($catalogo),
            $creados,
            $actualizados,
            self::ANIO_ESCOLAR,
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function cargarCatalogo(): array
    {
        $ruta = base_path('Untitled-1.php');

        if (! is_file($ruta)) {
            throw new RuntimeException("No se encontró el catálogo fuente en: {$ruta}");
        }

        $catalogo = require $ruta;

        if (! is_array($catalogo)) {
            throw new RuntimeException('Untitled-1.php debe retornar un array plano de criterios.');
        }

        if (count($catalogo) !== self::TOTAL_CRITERIOS_ESPERADOS) {
            throw new RuntimeException(sprintf(
                'El catálogo debe tener %d criterios; se encontraron %d.',
                self::TOTAL_CRITERIOS_ESPERADOS,
                count($catalogo),
            ));
        }

        return $catalogo;
    }

    private function resolverPeriodoAcademico(): PeriodoAcademico
    {
        $periodo = PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->where('bimestre', self::BIMESTRE)
            ->where('activo', true)
            ->first();

        if ($periodo === null) {
            throw new RuntimeException(sprintf(
                'No existe periodo académico activo para año escolar %s, bimestre %s. Ejecute PeriodosSemanasDemoSeeder o cree el periodo antes de sembrar criterios.',
                self::ANIO_ESCOLAR,
                self::BIMESTRE,
            ));
        }

        return $periodo;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function validarItem(array $item): void
    {
        foreach (self::CAMPOS_REQUERIDOS as $campo) {
            if (! array_key_exists($campo, $item)) {
                throw new RuntimeException("Falta el campo obligatorio '{$campo}' en un criterio del catálogo.");
            }
        }

        if (! CatalogoNivelGrado::esGradoValido(self::NIVEL, (string) $item['grado'])) {
            throw new RuntimeException(sprintf(
                "Grado inválido para Inicial: '%s'.",
                $item['grado'],
            ));
        }

        if ((string) $item['bimestre'] !== self::BIMESTRE) {
            throw new RuntimeException(sprintf(
                "Todos los criterios deben ser del bimestre %s; se encontró '%s' en '%s'.",
                self::BIMESTRE,
                $item['bimestre'],
                $item['criterio'],
            ));
        }

        if (! in_array($item['area'], self::AREAS_PERMITIDAS, true)) {
            throw new RuntimeException(sprintf(
                "Área no permitida '%s' en criterio '%s'.",
                $item['area'],
                $item['criterio'],
            ));
        }

        $texto = strtolower((string) $item['criterio'].' '.(string) ($item['descripcion'] ?? ''));
        if (str_contains($texto, 'aprestamiento')) {
            throw new RuntimeException(sprintf(
                "El catálogo no debe contener Aprestamiento. Criterio rechazado: '%s'.",
                $item['criterio'],
            ));
        }

        if (! is_bool($item['activo']) || $item['activo'] !== true) {
            throw new RuntimeException(sprintf(
                "Todos los criterios deben estar activos. Revisar: '%s'.",
                $item['criterio'],
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolverMallaCurso(array $item): MallaCurso
    {
        $mallaCurso = MallaCurso::query()
            ->where('activo', true)
            ->whereHas('mallaCurricular', fn ($q) => $q
                ->where('anio_escolar', self::ANIO_ESCOLAR)
                ->where('nivel', self::NIVEL)
                ->where('grado', $item['grado']))
            ->whereHas('area', fn ($q) => $q
                ->where('nombre', $item['area'])
                ->where('nivel', self::NIVEL)
                ->where('activo', true))
            ->whereHas('cursoCatalogo', fn ($q) => $q
                ->where('nombre', $item['curso'])
                ->where('activo', true))
            ->first();

        if ($mallaCurso === null) {
            throw new RuntimeException(sprintf(
                'No se encontró malla/curso activo para Inicial %s — área "%s", curso "%s". Provisione la malla curricular 2026 antes de ejecutar este seeder.',
                $item['grado'],
                $item['area'],
                $item['curso'],
            ));
        }

        return $mallaCurso;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolverCompetencia(array $item): Competencia
    {
        $competencia = Competencia::query()
            ->where('nombre', $item['competencia'])
            ->where('activo', true)
            ->whereHas('area', fn ($q) => $q
                ->where('nivel', self::NIVEL)
                ->where('nombre', $item['area']))
            ->first();

        if ($competencia === null) {
            $competencia = Competencia::query()
                ->where('nombre', $item['competencia'])
                ->where('activo', true)
                ->whereHas('area', fn ($q) => $q->where('nivel', self::NIVEL))
                ->first();
        }

        if ($competencia === null) {
            throw new RuntimeException(sprintf(
                'Competencia no encontrada en BD para Inicial. Grado: %s. Curso: %s. Criterio: %s. Competencia: %s.',
                $item['grado'],
                $item['curso'],
                $item['criterio'],
                $item['competencia'],
            ));
        }

        return $competencia;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolverCapacidad(array $item, Competencia $competencia): Capacidad
    {
        $nombreCapacidad = (string) $item['capacidad'];
        $capacidad = $this->buscarCapacidad($competencia, $nombreCapacidad);

        if ($capacidad === null && isset(self::ALIAS_CAPACIDADES[$nombreCapacidad])) {
            $capacidad = $this->buscarCapacidad($competencia, self::ALIAS_CAPACIDADES[$nombreCapacidad]);
        }

        if ($capacidad === null) {
            throw new RuntimeException(sprintf(
                'Capacidad no encontrada en BD para la competencia indicada. Grado: %s. Curso: %s. Criterio: %s. Competencia: %s. Capacidad: %s.',
                $item['grado'],
                $item['curso'],
                $item['criterio'],
                $item['competencia'],
                $item['capacidad'],
            ));
        }

        return $capacidad;
    }

    private function buscarCapacidad(Competencia $competencia, string $nombre): ?Capacidad
    {
        return Capacidad::query()
            ->where('competencia_id', $competencia->id)
            ->where('nombre', $nombre)
            ->where('activo', true)
            ->first();
    }

    private function sincronizarRelaciones(TemaSemanal $tema, Competencia $competencia, Capacidad $capacidad): void
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
}
