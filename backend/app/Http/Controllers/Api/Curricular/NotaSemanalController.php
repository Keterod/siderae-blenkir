<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\BulkNotasSemanalesRequest;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Services\Curricular\NotaSemanalBulkService;
use App\Services\Curricular\NotaSemanalFormularioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotaSemanalController extends Controller
{
    public function __construct(
        private readonly NotaSemanalBulkService $bulkService = new NotaSemanalBulkService,
        private readonly NotaSemanalFormularioService $formularioService = new NotaSemanalFormularioService,
    ) {}

    public function formulario(Request $request): JsonResponse
    {
        $data = $request->validate([
            'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'estudiante_id' => ['nullable', 'integer', 'exists:estudiantes,id'],
        ]);

        $asignacion = DocenteCursoAula::query()
            ->with(['mallaCurso.area', 'mallaCurso.cursoCatalogo', 'mallaCurso.mallaCurricular'])
            ->findOrFail($data['asignacion_docente_id']);

        if (! $request->user()->can('gestionar_asignaciones_docente') && $asignacion->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado para esta asignación.'], 403);
        }

        $resultado = $this->formularioService->construir(
            $asignacion,
            (int) $data['periodo_academico_id'],
            isset($data['estudiante_id']) ? (int) $data['estudiante_id'] : null,
        );

        return response()->json([
            'asignacion' => $resultado['asignacion'],
            'curso' => $resultado['curso'],
            'periodo' => $resultado['periodo'],
            'estudiantes' => $resultado['estudiantes'],
            'pesos' => $resultado['pesos'],
            'criterios' => $resultado['criterios'],
            'notas_por_criterio' => $resultado['notas_por_criterio'],
            'notas_por_estudiante_criterio' => $resultado['notas_por_estudiante_criterio'],
        ]);
    }

    public function bulk(BulkNotasSemanalesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asignacion = DocenteCursoAula::query()->findOrFail($data['asignacion_docente_id']);

        if ($asignacion->user_id !== $request->user()->id) {
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

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'curricular.notas_semanales.bulk',
                'asignacion_docente_id' => $asignacion->id,
                'cantidad' => count($resultado['notas']),
            ])
            ->log('Registro masivo de notas semanales');

        return response()->json([
            'notas' => $resultado['notas'],
            'advertencias' => $resultado['advertencias'],
        ], 201);
    }
}
