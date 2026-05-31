<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\StoreSeccionAulaRequest;
use App\Http\Requests\Curricular\UpdateSeccionAulaRequest;
use App\Models\Curricular\SeccionAula;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\SeccionAulaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeccionAulaController extends Controller
{
    public function __construct(
        private readonly SeccionAulaService $service = new SeccionAulaService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nivel' => ['nullable', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['nullable', 'string', 'max:20'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $activo = $request->has('activo') ? $request->boolean('activo') : null;
        $incluirInactivas = $request->boolean('incluir_inactivas')
            || $request->boolean('incluir_inactivos');

        $secciones = $this->service->listar(
            $data['nivel'] ?? null,
            $data['grado'] ?? null,
            $activo,
            $incluirInactivas,
            $data['q'] ?? null,
        );

        return response()->json($secciones->map(fn (SeccionAula $s) => $this->serializar($s))->values());
    }

    public function store(StoreSeccionAulaRequest $request): JsonResponse
    {
        $seccion = $this->service->crear($request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($seccion)
            ->withProperties([
                'accion' => 'curricular.seccion_aula.creada',
                'seccion_aula_id' => $seccion->id,
                'nivel' => $seccion->nivel,
                'grado' => $seccion->grado,
            ])
            ->log('Sección/aula creada');

        return response()->json($this->serializar($seccion), 201);
    }

    public function update(UpdateSeccionAulaRequest $request, SeccionAula $seccionAula): JsonResponse
    {
        $seccion = $this->service->actualizar($seccionAula, $request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($seccion)
            ->withProperties([
                'accion' => 'curricular.seccion_aula.actualizada',
                'seccion_aula_id' => $seccion->id,
            ])
            ->log('Sección/aula actualizada');

        return response()->json($this->serializar($seccion));
    }

    public function desactivar(SeccionAula $seccionAula): JsonResponse
    {
        $seccion = $this->service->desactivar($seccionAula);

        activity()
            ->causedBy(request()->user())
            ->performedOn($seccion)
            ->withProperties([
                'accion' => 'curricular.seccion_aula.desactivada',
                'seccion_aula_id' => $seccion->id,
            ])
            ->log('Sección/aula desactivada');

        return response()->json($this->serializar($seccion));
    }

    public function reactivar(SeccionAula $seccionAula): JsonResponse
    {
        $seccion = $this->service->reactivar($seccionAula);

        activity()
            ->causedBy(request()->user())
            ->performedOn($seccion)
            ->withProperties([
                'accion' => 'curricular.seccion_aula.reactivada',
                'seccion_aula_id' => $seccion->id,
            ])
            ->log('Sección/aula reactivada');

        return response()->json($this->serializar($seccion));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(SeccionAula $seccion): array
    {
        return [
            'id' => $seccion->id,
            'nivel' => $seccion->nivel,
            'grado' => $seccion->grado,
            'nombre' => $seccion->nombre,
            'codigo' => $seccion->codigo,
            'activo' => $seccion->activo,
            'orden' => $seccion->orden,
        ];
    }
}
