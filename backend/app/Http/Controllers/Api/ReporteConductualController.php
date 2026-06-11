<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReporteConductualRequest;
use App\Models\Estudiante;
use App\Models\ReporteConductual;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;

class ReporteConductualController extends Controller
{
    public function index(Estudiante $estudiante): JsonResponse
    {
        if ($response = $this->rechazarSiNoSedeOperativa($estudiante)) {
            return $response;
        }

        $reportes = ReporteConductual::query()
            ->where('estudiante_id', $estudiante->id)
            ->activos()
            ->with('registradoPor:id,email')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($reportes);
    }

    public function store(StoreReporteConductualRequest $request, Estudiante $estudiante): JsonResponse
    {
        if ($response = $this->rechazarSiNoSedeOperativa($estudiante)) {
            return $response;
        }

        $reporte = ReporteConductual::query()->create([
            'estudiante_id' => $estudiante->id,
            'registrado_por' => $request->user()->id,
            'fecha' => $request->validated('fecha'),
            'tipo_conducta' => $request->validated('tipo_conducta'),
            'descripcion' => $request->validated('descripcion'),
            'nivel_gravedad' => $request->validated('nivel_gravedad'),
            'accion_inmediata' => $request->validated('accion_inmediata'),
            'estado' => 'activo',
        ]);

        $reporte->load('registradoPor:id,email');

        activity()
            ->causedBy($request->user())
            ->performedOn($reporte)
            ->withProperties([
                'accion' => 'reporte_conductual.registrado',
                'estudiante_id' => $estudiante->id,
                'reporte_conductual_id' => $reporte->id,
            ])
            ->log('reporte_conductual.registrado');

        return response()->json($reporte, 201);
    }

    public function anular(ReporteConductual $reporteConductual): JsonResponse
    {
        $reporteConductual->load('estudiante');

        if ($response = $this->rechazarSiNoSedeOperativa($reporteConductual->estudiante)) {
            return $response;
        }

        if ($reporteConductual->estado === 'anulado') {
            return response()->json([
                'message' => 'El reporte conductual ya está anulado.',
            ], 422);
        }

        $reporteConductual->update(['estado' => 'anulado']);
        $reporteConductual->load('registradoPor:id,email');

        activity()
            ->causedBy(request()->user())
            ->performedOn($reporteConductual)
            ->withProperties([
                'accion' => 'reporte_conductual.anulado',
                'estudiante_id' => $reporteConductual->estudiante_id,
                'reporte_conductual_id' => $reporteConductual->id,
            ])
            ->log('reporte_conductual.anulado');

        return response()->json($reporteConductual);
    }

    private function rechazarSiNoSedeOperativa(Estudiante $estudiante): ?JsonResponse
    {
        if ($estudiante->sede !== SedeOperativa::CHILCA) {
            return response()->json([
                'message' => 'Estudiante fuera de la sede operativa V1 (Chilca).',
            ], 403);
        }

        return null;
    }
}
