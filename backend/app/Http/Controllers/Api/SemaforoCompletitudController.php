<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Services\CompletitudDatosService;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemaforoCompletitudController extends Controller
{
    public function __construct(private readonly CompletitudDatosService $completitudDatosService) {}

    public function show(Request $request, Estudiante $estudiante): JsonResponse
    {
        if ($response = $this->rechazarSiNoSedeOperativa($estudiante)) {
            return $response;
        }

        $anioEscolar = $request->query('anio_escolar', $estudiante->anio_escolar);
        $bimestre = $request->query('bimestre');

        $resultado = $this->completitudDatosService->evaluar($estudiante, (string) $anioEscolar, $bimestre ? (string) $bimestre : null);

        return response()->json([
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => $anioEscolar,
            'bimestre' => $bimestre,
            ...$resultado,
        ]);
    }

    private function rechazarSiNoSedeOperativa(Estudiante $estudiante): ?JsonResponse
    {
        if ($estudiante->sede !== SedeOperativa::CHILCA) {
            return response()->json([
                'message' => 'Estudiante fuera de la sede operativa V1 (Chilca).',
            ], 403);
        }

        return null;
    }
}
