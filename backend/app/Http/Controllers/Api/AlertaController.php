<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;

class AlertaController extends Controller
{
    public function index(): JsonResponse
    {
        $alertas = Alerta::query()
            ->with(['estudiante:id,codigo,nombres,apellidos', 'indiceRiesgo:id,indice,nivel,created_at'])
            ->orderByDesc('id')
            ->get();

        return response()->json($alertas);
    }

    public function show(Alerta $alerta): JsonResponse
    {
        $alerta->load([
            'estudiante:id,codigo,nombres,apellidos,grado,seccion,anio_escolar',
            'indiceRiesgo',
            'cerradaPor:id,email',
            'intervenciones' => function ($query): void {
                $query->orderByDesc('fecha')->orderByDesc('id');
            },
            'intervenciones.registradoPor:id,email',
        ]);

        return response()->json($alerta);
    }
}
