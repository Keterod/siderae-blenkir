<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistorialRiesgoController extends Controller
{
    public function index(Request $request, Estudiante $estudiante): JsonResponse
    {
        if ($response = $this->rechazarSiNoSedeOperativa($estudiante)) {
            return $response;
        }

        $query = $estudiante->indicesRiesgo()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->has('anio_escolar')) {
            $query->where('anio_escolar', (string) $request->query('anio_escolar'));
        }

        if ($request->has('bimestre')) {
            $query->where('bimestre', (string) $request->query('bimestre'));
        }

        $historial = $query->get()->map(static function ($indice): array {
            return [
                'id' => $indice->id,
                'indice' => (float) $indice->indice,
                'nivel' => $indice->nivel,
                'anio_escolar' => $indice->anio_escolar,
                'bimestre' => $indice->bimestre,
                'fecha' => $indice->created_at?->toDateString(),
                'variables_utilizadas' => $indice->variables_utilizadas,
            ];
        });

        return response()->json([
            'estudiante_id' => $estudiante->id,
            'historial' => $historial,
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
