<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsistenciaRequest;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;

class AsistenciaController extends Controller
{
    public function index(Estudiante $estudiante): JsonResponse
    {
        $registros = $estudiante->asistencias()
            ->orderByDesc('semana_inicio')
            ->orderByDesc('id')
            ->get();

        return response()->json($registros);
    }

    public function store(StoreAsistenciaRequest $request, Estudiante $estudiante): JsonResponse
    {
        $payload = $request->validated();
        $payload['registrado_por'] = $request->user()->id;

        $asistencia = $estudiante->asistencias()->create($payload);

        return response()->json($asistencia, 201);
    }
}
