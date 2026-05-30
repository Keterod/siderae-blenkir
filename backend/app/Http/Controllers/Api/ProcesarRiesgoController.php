<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Estudiante;
use App\Services\MlRiskService;
use App\Services\RiesgoAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcesarRiesgoController extends Controller
{
    public function store(
        Request $request,
        Estudiante $estudiante,
        MlRiskService $mlRiskService,
        RiesgoAcademicoService $riesgoAcademicoService
    ): JsonResponse
    {
        $validated = $request->validate([
            'bimestre' => ['sometimes', 'string', 'in:1,2,3,4'],
        ]);

        $bimestre = $validated['bimestre'] ?? '1';
        $anio = $estudiante->anio_escolar;

        $resultado = $riesgoAcademicoService->procesarEstudiante($estudiante, $anio, $bimestre, $mlRiskService);
        if (($resultado['status'] ?? '') === 'no_disponible') {
            return response()->json([
                'message' => $resultado['message'] ?? RiesgoAcademicoService::MENSAJE_INICIAL_NO_DISPONIBLE,
                'errors' => $resultado['errors'] ?? [],
            ], 422);
        }

        if ($resultado['status'] === 'omitido') {
            return response()->json([
                'message' => $resultado['message'] ?? 'Faltan datos mínimos para calcular el riesgo.',
                'errors' => $resultado['errors'],
            ], 422);
        }

        if ($resultado['status'] === 'fallido') {
            return response()->json([
                'message' => $resultado['message'] ?? 'No se pudo procesar el riesgo.',
            ], 503);
        }

        $registro = $resultado['registro'];
        $alertaGenerada = $resultado['alerta_generada'];

        activity()
            ->causedBy($request->user())
            ->performedOn($registro)
            ->withProperties([
                'accion' => 'riesgo.procesado',
                'estudiante_id' => $estudiante->id,
                'indice_riesgo_id' => $registro->id,
                'nivel' => $registro->nivel,
                'bimestre' => $bimestre,
                'anio_escolar' => $anio,
            ])
            ->log('riesgo.procesado');

        if ($alertaGenerada !== null) {
            activity()
                ->causedBy($request->user())
                ->performedOn($alertaGenerada)
                ->withProperties([
                    'accion' => 'alerta.generada',
                    'alerta_id' => $alertaGenerada->id,
                    'estudiante_id' => $estudiante->id,
                    'indice_riesgo_id' => $registro->id,
                ])
                ->log('alerta.generada');
        }

        return response()->json(array_merge(
            $registro->toArray(),
            [
                'alerta_generada' => $alertaGenerada ? $alertaGenerada->toArray() : null,
            ]
        ), 201);
    }
}
