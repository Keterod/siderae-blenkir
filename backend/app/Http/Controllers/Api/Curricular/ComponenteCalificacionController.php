<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\ReactivarComponenteCalificacionRequest;
use App\Http\Requests\Curricular\ReordenarComponentesCalificacionRequest;
use App\Http\Requests\Curricular\StoreComponenteCalificacionRequest;
use App\Http\Requests\Curricular\UpdateComponenteCalificacionRequest;
use App\Models\Curricular\ComponenteCalificacionNivel;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\ComponenteCalificacionNivelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComponenteCalificacionController extends Controller
{
    public function __construct(
        private readonly ComponenteCalificacionNivelService $service = new ComponenteCalificacionNivelService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:10'],
            'nivel' => ['nullable', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'activo' => ['nullable', 'boolean'],
        ], [], [
            'anio_escolar' => 'año escolar',
        ]);

        $activo = array_key_exists('activo', $data)
            ? filter_var($data['activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $componentes = $this->service->listar(
            $data['anio_escolar'],
            $data['nivel'] ?? null,
            $activo,
        );

        return response()->json($componentes);
    }

    public function porNivel(Request $request, string $nivel): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:10'],
        ], [], [
            'anio_escolar' => 'año escolar',
        ]);

        $componentes = $this->service->listarPorNivel($data['anio_escolar'], $nivel);

        return response()->json([
            'anio_escolar' => $data['anio_escolar'],
            'nivel' => $nivel,
            'componentes' => $componentes,
            'validacion' => $this->service->evaluarSumaActivos($data['anio_escolar'], $nivel),
        ]);
    }

    public function validarSuma(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:10'],
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
        ], [], [
            'anio_escolar' => 'año escolar',
        ]);

        return response()->json($this->service->evaluarSumaActivos($data['anio_escolar'], $data['nivel']));
    }

    public function store(StoreComponenteCalificacionRequest $request): JsonResponse
    {
        $componente = $this->service->crear($request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($componente)
            ->withProperties([
                'accion' => 'curricular.componente_calificacion.creado',
                'componente_id' => $componente->id,
                'nivel' => $componente->nivel,
                'anio_escolar' => $componente->anio_escolar,
            ])
            ->log('Componente de calificación creado');

        return response()->json($componente, 201);
    }

    public function update(UpdateComponenteCalificacionRequest $request, ComponenteCalificacionNivel $componenteCalificacionNivel): JsonResponse
    {
        $componente = $this->service->actualizar($componenteCalificacionNivel, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($componente)
            ->withProperties([
                'accion' => 'curricular.componente_calificacion.actualizado',
                'componente_id' => $componente->id,
            ])
            ->log('Componente de calificación actualizado');

        return response()->json($componente);
    }

    public function desactivar(ComponenteCalificacionNivel $componenteCalificacionNivel): JsonResponse
    {
        $componente = $this->service->desactivar($componenteCalificacionNivel);

        activity()
            ->causedBy(request()->user())
            ->performedOn($componente)
            ->withProperties([
                'accion' => 'curricular.componente_calificacion.desactivado',
                'componente_id' => $componente->id,
            ])
            ->log('Componente de calificación desactivado');

        return response()->json([
            'componente' => $componente,
            'validacion' => $this->service->evaluarSumaActivos($componente->anio_escolar, $componente->nivel),
        ]);
    }

    public function reactivar(
        ReactivarComponenteCalificacionRequest $request,
        ComponenteCalificacionNivel $componenteCalificacionNivel,
    ): JsonResponse {
        $componente = $this->service->reactivar($componenteCalificacionNivel, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($componente)
            ->withProperties([
                'accion' => 'curricular.componente_calificacion.reactivado',
                'componente_id' => $componente->id,
            ])
            ->log('Componente de calificación reactivado');

        return response()->json($componente);
    }

    public function reordenar(ReordenarComponentesCalificacionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $componentes = $this->service->reordenar($data['anio_escolar'], $data['nivel'], $data['ordenes']);

        return response()->json($componentes);
    }

    public function asegurarDefaults(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:10'],
        ], [], [
            'anio_escolar' => 'año escolar',
        ]);

        $resultado = $this->service->asegurarDefaults($data['anio_escolar']);

        return response()->json([
            'anio_escolar' => $data['anio_escolar'],
            'creados_por_nivel' => $resultado,
        ]);
    }
}
