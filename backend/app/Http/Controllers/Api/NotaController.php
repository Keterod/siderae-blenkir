<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotaRequest;
use App\Models\Estudiante;
use App\Models\Materia;
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
        $data = $request->validated();
        $rawMateriaId = $data['materia_id'] ?? null;
        $materiaId = $rawMateriaId !== null && $rawMateriaId !== '' ? (int) $rawMateriaId : null;

        $curso = $data['curso'] ?? null;
        if ($materiaId !== null) {
            $materia = Materia::query()->findOrFail($materiaId);
            $curso = $materia->nombre;
        }

        $payload = [
            'anio_escolar' => $data['anio_escolar'],
            'bimestre' => $data['bimestre'],
            'curso' => $curso ?? '',
            'nota' => $data['nota'],
            'nota_conducta' => array_key_exists('nota_conducta', $data) ? $data['nota_conducta'] : null,
            'materia_id' => $materiaId,
        ];

        $nota = $estudiante->notas()->create($payload);

        activity()
            ->causedBy($request->user())
            ->performedOn($nota)
            ->withProperties([
                'accion' => 'nota.registrada',
                'estudiante_id' => $estudiante->id,
                'nota_id' => $nota->id,
                'materia_id' => $nota->materia_id,
                'anio_escolar' => $nota->anio_escolar,
                'bimestre' => $nota->bimestre,
                'curso' => $nota->curso,
            ])
            ->log('nota.registrada');

        return response()->json($nota, 201);
    }
}
