<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstudianteRequest;
use App\Http\Requests\UpdateEstudianteRequest;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;

class EstudianteController extends Controller
{
    public function index(): JsonResponse
    {
        $estudiantes = Estudiante::query()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return response()->json($estudiantes);
    }

    public function store(StoreEstudianteRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('activo', $data)) {
            $data['activo'] = true;
        }

        $estudiante = Estudiante::create($data);

        activity()
            ->causedBy($request->user())
            ->performedOn($estudiante)
            ->withProperties([
                'accion' => 'estudiante.creado',
                'estudiante_id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
            ])
            ->log('estudiante.creado');

        return response()->json($estudiante, 201);
    }

    public function show(Estudiante $estudiante): JsonResponse
    {
        $data = $estudiante->toArray();
        $data['ultimo_indice_riesgo'] = $estudiante->indicesRiesgo()->latest('id')->first();

        return response()->json($data);
    }

    public function update(UpdateEstudianteRequest $request, Estudiante $estudiante): JsonResponse
    {
        $estudiante->update($request->validated());
        $estudiante->refresh();

        activity()
            ->causedBy($request->user())
            ->performedOn($estudiante)
            ->withProperties([
                'accion' => 'estudiante.actualizado',
                'estudiante_id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
            ])
            ->log('estudiante.actualizado');

        return response()->json($estudiante->fresh());
    }
}
