<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotaRequest;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;

class NotaController extends Controller
{
    public function index(Estudiante $estudiante): JsonResponse
    {
        $notas = $estudiante->notas()
            ->orderBy('anio_escolar')
            ->orderBy('bimestre')
            ->orderBy('id')
            ->get();

        return response()->json($notas);
    }

    public function store(StoreNotaRequest $request, Estudiante $estudiante): JsonResponse
    {
        $nota = $estudiante->notas()->create($request->validated());

        return response()->json($nota, 201);
    }
}
