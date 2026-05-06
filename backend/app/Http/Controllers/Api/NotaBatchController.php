<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotaBatchRequest;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NotaBatchController extends Controller
{
    public function store(StoreNotaBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $materia = Materia::query()->whereKey((int) $data['materia_id'])->firstOrFail();
        $cursoNombre = $materia->nombre;

        $creadas = DB::transaction(function () use ($data, $materia, $cursoNombre) {
            $out = [];
            foreach ($data['filas'] as $fila) {
                $estudiante = Estudiante::query()->whereKey($fila['estudiante_id'])->firstOrFail();
                $payload = [
                    'anio_escolar' => $data['anio_escolar'],
                    'bimestre' => $data['bimestre'],
                    'curso' => $cursoNombre,
                    'nota' => $fila['nota'],
                    'nota_conducta' => array_key_exists('nota_conducta', $fila) ? $fila['nota_conducta'] : null,
                    'materia_id' => $materia->id,
                ];
                $out[] = $estudiante->notas()->create($payload);
            }

            return $out;
        });

        $ids = array_map(static fn (Nota $n) => $n->id, $creadas);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'nota.lote_registrado',
                'materia_id' => $materia->id,
                'cantidad' => count($creadas),
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'curso' => $cursoNombre,
                'nota_ids' => $ids,
            ])
            ->log('nota.lote_registrado');

        return response()->json([
            'creadas' => $creadas,
            'total' => count($creadas),
        ], 201);
    }
}
