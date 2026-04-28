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

        return response()->json($estudiante, 201);
    }

    public function show(Estudiante $estudiante): JsonResponse
    {
        return response()->json($estudiante);
    }

    public function update(UpdateEstudianteRequest $request, Estudiante $estudiante): JsonResponse
    {
        $estudiante->update($request->validated());

        return response()->json($estudiante->fresh());
    }
}
