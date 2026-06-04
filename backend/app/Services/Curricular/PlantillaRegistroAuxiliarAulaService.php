<?php

namespace App\Services\Curricular;

use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Support\ExcelSheetNameSanitizer;
use App\Support\SedeOperativa;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PlantillaRegistroAuxiliarAulaService
{
    public function __construct(
        private readonly MallaCurricularService $mallaService = new MallaCurricularService,
        private readonly PlantillaRegistroAuxiliarService $plantillaService = new PlantillaRegistroAuxiliarService,
        private readonly PlantillaRegistroAuxiliarAulaExcelService $excelService = new PlantillaRegistroAuxiliarAulaExcelService,
        private readonly ExcelSheetNameSanitizer $sheetNames = new ExcelSheetNameSanitizer,
    ) {}

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     grado: string,
     *     seccion: string,
     *     periodo_academico_id: int,
     *     sede?: string|null,
     *     modo?: string
     * }  $filtros
     * @return array{binary: string, filename: string}
     */
    public function generarSinDatos(array $filtros): array
    {
        $sede = SedeOperativa::defaultConsulta($filtros['sede'] ?? null);
        $periodo = PeriodoAcademico::query()->findOrFail($filtros['periodo_academico_id']);

        if ($periodo->anio_escolar !== $filtros['anio_escolar']) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar indicado.'],
            ]);
        }

        $malla = $this->mallaService->obtenerOProvisionar(
            $filtros['anio_escolar'],
            $filtros['nivel'],
            $filtros['grado'],
        );

        $cursos = $this->cursosActivosOrdenados($malla->id);
        if ($cursos->isEmpty()) {
            throw ValidationException::withMessages([
                'cursos' => ['No hay cursos activos en la malla para este nivel y grado.'],
            ]);
        }

        $contextoFiltros = [
            'anio_escolar' => $filtros['anio_escolar'],
            'nivel' => $filtros['nivel'],
            'sede' => $sede,
            'grado' => $filtros['grado'],
            'seccion' => $filtros['seccion'],
            'periodo_academico_id' => (int) $periodo->id,
        ];

        $resumen = [
            'colegio' => 'SIDERAE-Blenkir',
            'sede' => 'Chilca',
            'anio_escolar' => $filtros['anio_escolar'],
            'nivel' => $filtros['nivel'],
            'grado' => $filtros['grado'],
            'seccion' => $filtros['seccion'],
            'bimestre' => $periodo->bimestre ?? '',
            'modo' => PlantillaExcelAulaLayout::MODO_SIN_DATOS,
        ];

        $hojasCurso = [];
        $nombresUsados = [PlantillaExcelAulaLayout::HOJA_ESTUDIANTES];

        foreach ($cursos as $mallaCurso) {
            $payload = $this->plantillaService->construirPorMallaCursoEnAula($contextoFiltros, $mallaCurso, false);
            $payload['estudiantes'] = $this->filasEstudiantesVacias($payload);
            $payload['encabezado']['docente'] = null;

            $nombreCurso = $mallaCurso->cursoCatalogo?->nombre ?? 'Curso';
            $tituloHoja = $this->sheetNames->sanitizar($nombreCurso, $nombresUsados);
            $nombresUsados[] = $tituloHoja;

            $hojasCurso[] = [
                'titulo' => $tituloHoja,
                'payload' => $payload,
            ];
        }

        $binary = $this->excelService->generar($resumen, $hojasCurso);

        return [
            'binary' => $binary,
            'filename' => $this->nombreArchivo(
                $filtros['nivel'],
                $filtros['grado'],
                $filtros['seccion'],
                (int) ($periodo->bimestre ?? 0),
            ),
        ];
    }

    /**
     * @return Collection<int, MallaCurso>
     */
    private function cursosActivosOrdenados(int $mallaCurricularId): Collection
    {
        return MallaCurso::query()
            ->where('malla_curricular_id', $mallaCurricularId)
            ->where('activo', true)
            ->with(['area', 'cursoCatalogo'])
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{numero: int, nombre: string, notas_criterio: list<array{valores: list<null>}>, bimestral: list<null>}>
     */
    private function filasEstudiantesVacias(array $payload): array
    {
        $columnasNota = $payload['columnas_nota'] ?? PlantillaRegistroAuxiliarLayout::columnasNotaLegacy();
        $columnasCriterios = $payload['columnas_criterios'] ?? [];
        $columnasBimestral = $payload['columnas_bimestral'] ?? [];

        $notaPlantilla = [];
        foreach ($columnasCriterios as $_) {
            $notaPlantilla[] = [
                'valores' => array_fill(0, count($columnasNota), null),
            ];
        }

        $bimPlantilla = array_fill(0, count($columnasBimestral), null);

        $filas = [];
        for ($i = 1; $i <= PlantillaExcelAulaLayout::FILAS_ESTUDIANTES; $i++) {
            $filas[] = [
                'numero' => $i,
                'nombre' => '',
                'notas_criterio' => $notaPlantilla,
                'bimestral' => $bimPlantilla,
            ];
        }

        return $filas;
    }

    private function nombreArchivo(string $nivel, string $grado, string $seccion, int $bimestre): string
    {
        $slug = fn (string $s) => preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim($s))) ?: 'x';

        return sprintf(
            'notas_aula_%s_%s_%s_bimestre_%d.xlsx',
            $slug($nivel),
            $slug($grado),
            $slug($seccion),
            max(0, $bimestre),
        );
    }
}
