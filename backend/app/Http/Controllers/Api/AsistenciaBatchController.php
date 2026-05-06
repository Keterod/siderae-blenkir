<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsistenciaBatchRequest;
use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AsistenciaBatchController extends Controller
{
    public function store(StoreAsistenciaBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        $creadas = DB::transaction(function () use ($data, $userId) {
            $out = [];
            $base = [
                'semana_inicio' => $data['semana_inicio'],
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'registrado_por' => $userId,
            ];
            foreach ($data['filas'] as $fila) {
                $estudiante = Estudiante::query()->whereKey($fila['estudiante_id'])->firstOrFail();
                $payload = array_merge($base, ['estado' => $fila['estado']]);
                $out[] = $estudiante->asistencias()->create($payload);
            }

            return $out;
        });

        $ids = array_map(static fn (Asistencia $a) => $a->id, $creadas);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'asistencia.lote_registrado',
                'cantidad' => count($creadas),
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'semana_inicio' => $data['semana_inicio'],
                'asistencia_ids' => $ids,
            ])
            ->log('asistencia.lote_registrado');

        return response()->json([
            'creadas' => $creadas,
            'total' => count($creadas),
        ], 201);
    }
}
