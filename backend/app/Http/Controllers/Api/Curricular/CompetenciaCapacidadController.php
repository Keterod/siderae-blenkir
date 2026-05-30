<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\StoreCapacidadRequest;
use App\Http\Requests\Curricular\StoreCompetenciaRequest;
use App\Http\Requests\Curricular\UpdateCapacidadRequest;
use App\Http\Requests\Curricular\UpdateCompetenciaRequest;
use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Services\Curricular\CompetenciaCapacidadService;
use Illuminate\Http\JsonResponse;

class CompetenciaCapacidadController extends Controller
{
    public function __construct(
        private readonly CompetenciaCapacidadService $service = new CompetenciaCapacidadService,
    ) {}

    public function storeCompetencia(StoreCompetenciaRequest $request, Area $area): JsonResponse
    {
        $competencia = $this->service->crearCompetencia($area, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($competencia)
            ->withProperties([
                'accion' => 'curricular.competencia.creada',
                'area_id' => $area->id,
                'competencia_id' => $competencia->id,
            ])
            ->log('Competencia creada');

        return response()->json($competencia, 201);
    }

    public function updateCompetencia(UpdateCompetenciaRequest $request, Competencia $competencia): JsonResponse
    {
        $competencia = $this->service->actualizarCompetencia($competencia, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($competencia)
            ->withProperties([
                'accion' => 'curricular.competencia.actualizada',
                'competencia_id' => $competencia->id,
            ])
            ->log('Competencia actualizada');

        return response()->json($competencia);
    }

    public function desactivarCompetencia(Competencia $competencia): JsonResponse
    {
        $competencia = $this->service->desactivarCompetencia($competencia);

        activity()
            ->causedBy(request()->user())
            ->performedOn($competencia)
            ->withProperties([
                'accion' => 'curricular.competencia.desactivada',
                'competencia_id' => $competencia->id,
            ])
            ->log('Competencia desactivada');

        return response()->json($competencia);
    }

    public function reactivarCompetencia(Competencia $competencia): JsonResponse
    {
        $competencia = $this->service->reactivarCompetencia($competencia);

        activity()
            ->causedBy(request()->user())
            ->performedOn($competencia)
            ->withProperties([
                'accion' => 'curricular.competencia.reactivada',
                'competencia_id' => $competencia->id,
            ])
            ->log('Competencia reactivada');

        return response()->json($competencia);
    }

    public function storeCapacidad(StoreCapacidadRequest $request, Competencia $competencia): JsonResponse
    {
        $capacidad = $this->service->crearCapacidad($competencia, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($capacidad)
            ->withProperties([
                'accion' => 'curricular.capacidad.creada',
                'competencia_id' => $competencia->id,
                'capacidad_id' => $capacidad->id,
            ])
            ->log('Capacidad creada');

        return response()->json($capacidad, 201);
    }

    public function updateCapacidad(UpdateCapacidadRequest $request, Capacidad $capacidad): JsonResponse
    {
        $capacidad = $this->service->actualizarCapacidad($capacidad, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($capacidad)
            ->withProperties([
                'accion' => 'curricular.capacidad.actualizada',
                'capacidad_id' => $capacidad->id,
            ])
            ->log('Capacidad actualizada');

        return response()->json($capacidad);
    }

    public function desactivarCapacidad(Capacidad $capacidad): JsonResponse
    {
        $capacidad = $this->service->desactivarCapacidad($capacidad);

        activity()
            ->causedBy(request()->user())
            ->performedOn($capacidad)
            ->withProperties([
                'accion' => 'curricular.capacidad.desactivada',
                'capacidad_id' => $capacidad->id,
            ])
            ->log('Capacidad desactivada');

        return response()->json($capacidad);
    }

    public function reactivarCapacidad(Capacidad $capacidad): JsonResponse
    {
        $capacidad = $this->service->reactivarCapacidad($capacidad);

        activity()
            ->causedBy(request()->user())
            ->performedOn($capacidad)
            ->withProperties([
                'accion' => 'curricular.capacidad.reactivada',
                'capacidad_id' => $capacidad->id,
            ])
            ->log('Capacidad reactivada');

        return response()->json($capacidad);
    }
}
