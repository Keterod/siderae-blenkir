<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\BulkNotasSemanalesRequest;
use App\Http\Requests\Curricular\ExcelAulaRequest;
use App\Http\Requests\Curricular\ImportPlantillaNotasSemanalesRequest;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\CurricularNotasAuthService;
use App\Services\Curricular\ImportPlantillaRegistroAuxiliarService;
use App\Services\Curricular\NotaSemanalBulkService;
use App\Services\Curricular\NotaSemanalCalificacionAdapter;
use App\Services\Curricular\NotaSemanalFormularioService;
use App\Services\Curricular\PlantillaRegistroAuxiliarAulaService;
use App\Services\Curricular\PlantillaRegistroAuxiliarExcelService;
use App\Services\Curricular\PlantillaRegistroAuxiliarService;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotaSemanalController extends Controller
{
    public function __construct(
        private readonly NotaSemanalBulkService $bulkService = new NotaSemanalBulkService,
        private readonly NotaSemanalFormularioService $formularioService = new NotaSemanalFormularioService,
        private readonly NotaSemanalCalificacionAdapter $calificacionAdapter = new NotaSemanalCalificacionAdapter,
        private readonly PlantillaRegistroAuxiliarService $plantillaService = new PlantillaRegistroAuxiliarService,
        private readonly PlantillaRegistroAuxiliarExcelService $plantillaExcelService = new PlantillaRegistroAuxiliarExcelService,
        private readonly ImportPlantillaRegistroAuxiliarService $importPlantillaService = new ImportPlantillaRegistroAuxiliarService,
        private readonly PlantillaRegistroAuxiliarAulaService $excelAulaService = new PlantillaRegistroAuxiliarAulaService,
        private readonly CurricularNotasAuthService $notasAuth = new CurricularNotasAuthService,
    ) {}

    public static function usuarioPuedeConsultaGlobalNotas(User $user): bool
    {
        return $user->can('gestionar_asignaciones_docente')
            || $user->hasRole('directivo');
    }

    public function formulario(Request $request): JsonResponse
    {
        $consultaGlobal = $request->boolean('consulta_global');

        if ($consultaGlobal) {
            $data = Validator::make($request->query(), [
                'anio_escolar' => ['required', 'string'],
                'nivel' => ['required', 'string', 'in:' . implode(',', CatalogoNivelGrado::nivelesCurriculares())],
                'sede' => ['required', 'string'],
                'grado' => ['required', 'string'],
                'seccion' => ['required', 'string'],
                'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
                'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
                'area_id' => ['nullable'],
                'estudiante_id' => ['nullable', 'integer', 'exists:estudiantes,id'],
            ], [], [
                'anio_escolar' => 'año escolar',
                'malla_curso_id' => 'curso',
                'periodo_academico_id' => 'bimestre',
            ])->validate();

            if (! static::usuarioPuedeConsultaGlobalNotas($request->user())) {
                return response()->json([
                    'message' => 'No autorizado para la consulta global de notas curriculares.',
                ], 403);
            }

            if (! $request->user()->can('ver_notas_academicas')) {
                return response()->json(['message' => 'Permiso denegado.'], 403);
            }

            $resultado = $this->formularioService->construirConsultaGlobal($data);
            $asignacionActiva = $this->notasAuth->resolverAsignacionActiva($data);

            if ($asignacionActiva !== null) {
                $resultado['asignacion'] = $asignacionActiva;
            }

            if ($asignacionActiva !== null && $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacionActiva)) {
                $resultado['readonly'] = false;
            }

            return response()->json($this->serializarRespuestaFormulario($resultado, consultaGlobal: true));
        }

        $data = Validator::make($request->query(), [
            'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'estudiante_id' => ['nullable', 'integer', 'exists:estudiantes,id'],
        ], [], [
            'asignacion_docente_id' => 'asignación docente',
            'periodo_academico_id' => 'bimestre',
        ])->validate();

        $asignacion = DocenteCursoAula::query()
            ->with(['mallaCurso.area', 'mallaCurso.cursoCatalogo', 'mallaCurso.mallaCurricular'])
            ->findOrFail($data['asignacion_docente_id']);

        if (! $this->notasAuth->puedeVerAsignacion($request->user(), $asignacion)) {
            return response()->json(['message' => 'No autorizado para esta asignación.'], 403);
        }

        $resultado = $this->formularioService->construir(
            $asignacion,
            (int) $data['periodo_academico_id'],
            isset($data['estudiante_id']) ? (int) $data['estudiante_id'] : null,
        );

        $resultado['readonly'] = ! $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacion);

        return response()->json($this->serializarRespuestaFormulario($resultado, consultaGlobal: false));
    }

    /**
     * @param  array<string, mixed>  $resultado
     * @return array<string, mixed>
     */
    private function serializarRespuestaFormulario(array $resultado, bool $consultaGlobal): array
    {
        return [
            'asignacion' => $resultado['asignacion'] ?? null,
            'consulta_global' => $consultaGlobal,
            'readonly' => (bool) ($resultado['readonly'] ?? false),
            'curso' => $resultado['curso'],
            'periodo' => $resultado['periodo'],
            'estudiantes' => $resultado['estudiantes'],
            'pesos' => $resultado['pesos'],
            'componentes_calificacion' => $resultado['componentes_calificacion'] ?? [],
            'calificacion_dinamica_disponible' => (bool) ($resultado['calificacion_dinamica_disponible'] ?? false),
            'nivel' => $resultado['nivel'] ?? null,
            'anio_escolar' => $resultado['anio_escolar'] ?? null,
            'criterios' => $resultado['criterios'],
            'notas_por_criterio' => $resultado['notas_por_criterio'],
            'notas_por_estudiante_criterio' => $resultado['notas_por_estudiante_criterio'],
        ];
    }

    public function bulk(BulkNotasSemanalesRequest $request): JsonResponse
    {
        if (! $request->user()->can('registrar_notas_semanales')) {
            return response()->json(['message' => 'No tiene permiso para registrar notas semanales.'], 403);
        }

        $data = $request->validated();
        $asignacion = DocenteCursoAula::query()->findOrFail($data['asignacion_docente_id']);

        if (! $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacion)) {
            return response()->json(['message' => 'Solo puede registrar notas en sus asignaciones activas.'], 403);
        }

        if (isset($data['registros_por_estudiante'])) {
            $resultado = $this->bulkService->registrarPorVariosEstudiantes(
                $request->user(),
                $asignacion,
                $data['registros_por_estudiante'],
            );
        } elseif (isset($data['estudiante_id'])) {
            $estudiante = Estudiante::query()->findOrFail($data['estudiante_id']);
            $resultado = $this->bulkService->registrarPorEstudiante(
                $request->user(),
                $asignacion,
                $estudiante,
                $data['registros'] ?? [],
            );
        } else {
            $tema = TemaSemanal::query()->findOrFail($data['tema_semanal_id']);
            $resultado = $this->bulkService->registrarPorTema(
                $request->user(),
                $asignacion,
                $tema,
                $data['notas'],
            );
        }

        $propsLog = [
            'accion' => 'curricular.notas_semanales.bulk',
            'asignacion_docente_id' => $asignacion->id,
            'docente_asignado_user_id' => $asignacion->user_id,
            'cantidad' => count($resultado['notas']),
        ];
        if ($this->notasAuth->esAdministrador($request->user()) && (int) $asignacion->user_id !== (int) $request->user()->id) {
            $propsLog['registro_institucional_admin'] = true;
        }

        activity()
            ->causedBy($request->user())
            ->withProperties($propsLog)
            ->log('Registro masivo de notas semanales');

        return response()->json([
            'notas' => array_map(
                fn ($nota) => $this->calificacionAdapter->serializarNota($nota),
                $resultado['notas'],
            ),
            'advertencias' => $resultado['advertencias'],
        ], 201);
    }

    public function excelAula(ExcelAulaRequest $request): StreamedResponse|JsonResponse
    {
        $data = $request->validated();
        $data['sede'] = SedeOperativa::defaultConsulta($data['sede'] ?? null);

        $resultado = $this->excelAulaService->generarSinDatos($data);

        return response()->streamDownload(
            static function () use ($resultado): void {
                echo $resultado['binary'];
            },
            $resultado['filename'],
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }

    public function plantillaExcel(Request $request): StreamedResponse|JsonResponse
    {
        $consultaGlobal = $request->boolean('consulta_global');
        $incluirNotas = $request->boolean('incluir_notas');

        if ($consultaGlobal) {
            if (! static::usuarioPuedeConsultaGlobalNotas($request->user())) {
                return response()->json([
                    'message' => 'No autorizado para la consulta global de notas curriculares.',
                ], 403);
            }

            if (! $request->user()->can('ver_notas_academicas')) {
                return response()->json(['message' => 'Permiso denegado.'], 403);
            }

            $data = Validator::make($request->query(), [
                'anio_escolar' => ['required', 'string'],
                'nivel' => ['required', 'string', 'in:'.implode(',', CatalogoNivelGrado::nivelesCurriculares())],
                'sede' => ['required', 'string'],
                'grado' => ['required', 'string'],
                'seccion' => ['required', 'string'],
                'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
                'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
                'area_id' => ['nullable'],
                'incluir_notas' => ['nullable', 'boolean'],
            ])->validate();

            $payload = $this->plantillaService->construirConsultaGlobal($data, $incluirNotas);
        } else {
            $puedeDocente = $request->user()->can('registrar_notas_semanales');
            $puedeConsulta = $request->user()->can('ver_notas_academicas');

            if (! $puedeDocente && ! $puedeConsulta) {
                return response()->json(['message' => 'Permiso denegado.'], 403);
            }

            $data = Validator::make($request->query(), [
                'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
                'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
                'incluir_notas' => ['nullable', 'boolean'],
            ])->validate();

            $asignacion = DocenteCursoAula::query()
                ->with(['user', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
                ->findOrFail($data['asignacion_docente_id']);

            $esPropia = (int) $asignacion->user_id === (int) $request->user()->id;

            if (! $esPropia) {
                if (! $puedeConsulta || ! static::usuarioPuedeConsultaGlobalNotas($request->user())) {
                    return response()->json(['message' => 'No autorizado para esta asignación.'], 403);
                }
            } elseif (! $puedeDocente) {
                return response()->json(['message' => 'Permiso denegado.'], 403);
            }

            $payload = $this->plantillaService->construirDesdeAsignacion(
                $asignacion,
                (int) $data['periodo_academico_id'],
                $incluirNotas,
            );
        }

        $binary = $this->plantillaExcelService->generar($payload);
        $filename = $payload['nombre_archivo'] ?? 'plantilla_registro_auxiliar.xlsx';

        return response()->streamDownload(
            static function () use ($binary): void {
                echo $binary;
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }

    public function importarExcel(ImportPlantillaNotasSemanalesRequest $request): JsonResponse
    {
        if (! $request->user()->can('registrar_notas_semanales')) {
            return response()->json(['message' => 'No tiene permiso para registrar notas semanales.'], 403);
        }

        $data = $request->validated();
        $asignacion = DocenteCursoAula::query()->findOrFail($data['asignacion_docente_id']);

        if (! $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacion)) {
            return response()->json(['message' => 'Solo puede importar notas en sus asignaciones activas.'], 403);
        }

        $archivo = $request->file('archivo');
        if ($archivo === null) {
            return response()->json(['message' => 'Debe adjuntar un archivo Excel.'], 422);
        }

        $resultado = $this->importPlantillaService->importar(
            $archivo->getContent(),
            $request->user(),
            $asignacion,
            (int) $data['periodo_academico_id'],
        );

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'curricular.notas_semanales.importar_excel',
                'asignacion_docente_id' => $asignacion->id,
                'importados' => $resultado['importados'],
            ])
            ->log('Importación Excel de notas semanales');

        return response()->json([
            'notas' => array_map(
                fn ($nota) => $this->calificacionAdapter->serializarNota($nota),
                $resultado['notas'],
            ),
            'advertencias' => $resultado['advertencias'],
            'importados' => $resultado['importados'],
            'importados_criterios' => $resultado['importados_criterios'],
            'importados_bimestral' => $resultado['importados_bimestral'],
            'omitidos' => $resultado['omitidos'],
        ], 201);
    }
}
