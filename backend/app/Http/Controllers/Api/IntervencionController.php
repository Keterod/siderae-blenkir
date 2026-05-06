<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIntervencionRequest;
use App\Models\Alerta;
use App\Models\Intervencion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IntervencionController extends Controller
{
    public function store(StoreIntervencionRequest $request, Alerta $alerta): JsonResponse
    {
        if ($alerta->estado === 'cerrada') {
            return response()->json([
                'message' => 'No se puede registrar una intervención en una alerta cerrada.',
            ], 422);
        }

        $intervencion = DB::transaction(function () use ($request, $alerta) {
            $row = Intervencion::query()->create([
                'alerta_id' => $alerta->id,
                'estudiante_id' => $alerta->estudiante_id,
                'registrado_por' => $request->user()->id,
                'tipo' => $request->validated('tipo'),
                'descripcion' => $request->validated('descripcion'),
                'fecha' => $request->validated('fecha'),
            ]);

            if ($alerta->estado === 'pendiente') {
                $alerta->update(['estado' => 'en_atencion']);
            }

            return $row;
        });

        $intervencion->load('registradoPor:id,email');

        activity()
            ->causedBy($request->user())
            ->performedOn($intervencion)
            ->withProperties([
                'accion' => 'intervencion.registrada',
                'alerta_id' => $alerta->id,
                'estudiante_id' => $alerta->estudiante_id,
                'intervencion_id' => $intervencion->id,
            ])
            ->log('intervencion.registrada');

        return response()->json($intervencion, 201);
    }
}
