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

        activity()
            ->causedBy($request->user())
            ->performedOn($asistencia)
            ->withProperties([
                'accion' => 'asistencia.registrada',
                'estudiante_id' => $estudiante->id,
                'asistencia_id' => $asistencia->id,
                'anio_escolar' => $asistencia->anio_escolar,
                'bimestre' => $asistencia->bimestre,
                'estado' => $asistencia->estado,
            ])
            ->log('asistencia.registrada');

        return response()->json($asistencia, 201);
    }
}
