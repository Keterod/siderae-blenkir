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

        activity()
            ->causedBy($request->user())
            ->performedOn($nota)
            ->withProperties([
                'accion' => 'nota.registrada',
                'estudiante_id' => $estudiante->id,
                'nota_id' => $nota->id,
                'anio_escolar' => $nota->anio_escolar,
                'bimestre' => $nota->bimestre,
                'curso' => $nota->curso,
            ])
            ->log('nota.registrada');

        return response()->json($nota, 201);
    }
}
