<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVariableSocioeconomicaRequest;
use App\Models\Estudiante;
use App\Models\VariableSocioeconomica;
use Illuminate\Http\JsonResponse;

class VariableSocioeconomicaController extends Controller
{
    public function index(Estudiante $estudiante): JsonResponse
    {
        $lista = VariableSocioeconomica::query()
            ->where('estudiante_id', $estudiante->id)
            ->orderByDesc('anio_escolar')
            ->orderByDesc('id')
            ->get();

        return response()->json($lista);
    }

    public function store(StoreVariableSocioeconomicaRequest $request, Estudiante $estudiante): JsonResponse
    {
        $validated = $request->validated();

        $registro = VariableSocioeconomica::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => $validated['anio_escolar'],
            ],
            [
                'composicion_familiar' => $validated['composicion_familiar'],
                'nivel_socioeconomico' => $validated['nivel_socioeconomico'],
                'acceso_internet' => $validated['acceso_internet'],
                'distancia_colegio_km' => $validated['distancia_colegio_km'] ?? null,
            ]
        )->fresh();

        return response()->json($registro);
    }
}
