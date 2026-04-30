<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CerrarAlertaRequest;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AlertaCierreController extends Controller
{
    public function store(CerrarAlertaRequest $request, Alerta $alerta): JsonResponse
    {
        if ($alerta->estado === 'cerrada') {
            return response()->json([
                'message' => 'La alerta ya está cerrada.',
            ], 422);
        }

        if ($alerta->intervenciones()->count() === 0) {
            return response()->json([
                'message' => 'Debe existir al menos una intervención registrada antes de cerrar la alerta.',
            ], 422);
        }

        DB::transaction(function () use ($request, $alerta): void {
            $alerta->update([
                'estado' => 'cerrada',
                'resultado_cierre' => $request->validated('resultado_cierre'),
                'fecha_cierre' => now(),
                'cerrada_por' => $request->user()->id,
            ]);
        });

        $alerta->load(['cerradaPor:id,email', 'intervenciones.registradoPor:id,email']);

        return response()->json($alerta->fresh());
    }
}
