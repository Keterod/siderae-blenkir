<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\ResolverPesoEvaluacionRequest;
use App\Http\Requests\Curricular\StoreConfiguracionPesoRequest;
use App\Http\Requests\Curricular\UpdateConfiguracionPesoRequest;
use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Models\Curricular\MallaCurso;
use App\Services\Curricular\PesoEvaluacionResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracionPesoEvaluacionController extends Controller
{
    public function __construct(
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ConfiguracionPesoEvaluacion::query()
            ->with(['area', 'cursoCatalogo'])
            ->orderByDesc('id');

        if ($request->filled('activo')) {
            $activo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo !== null) {
                $query->where('activo', $activo);
            }
        }

        return response()->json($query->get());
    }

    public function resolver(ResolverPesoEvaluacionRequest $request): JsonResponse
    {
        $mallaCurso = MallaCurso::query()
            ->with(['area', 'cursoCatalogo', 'mallaCurricular'])
            ->findOrFail($request->validated('malla_curso_id'));

        $detalle = $this->pesoResolver->resolverDetalleParaCurso(
            $mallaCurso,
            $mallaCurso->mallaCurricular
        );

        $config = $detalle['configuracion'];
        if ($config !== null) {
            $config->loadMissing(['area', 'cursoCatalogo']);
        }

        return response()->json([
            'malla_curso_id' => $mallaCurso->id,
            'curso' => [
                'nombre' => $mallaCurso->cursoCatalogo?->nombre,
                'area' => $mallaCurso->area?->nombre,
                'nivel' => $mallaCurso->mallaCurricular->nivel,
                'grado' => $mallaCurso->mallaCurricular->grado,
            ],
            'pesos' => $detalle['pesos'],
            'scope_aplicado' => $detalle['scope_aplicado'],
            'configuracion' => $config,
            'es_por_defecto' => $detalle['es_por_defecto'],
        ]);
    }

    public function store(StoreConfiguracionPesoRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->pesoResolver->validarSuma100([
            'cuaderno' => $data['peso_cuaderno'],
            'libro' => $data['peso_libro'],
            'tarea' => $data['peso_tarea'],
        ]);
        $this->pesoResolver->assertScopeActivoUnico($data);

        $config = ConfiguracionPesoEvaluacion::query()->create([
            ...$data,
            'activo' => true,
        ]);

        return response()->json($config->load(['area', 'cursoCatalogo']), 201);
    }

    public function update(UpdateConfiguracionPesoRequest $request, ConfiguracionPesoEvaluacion $configuracionPesoEvaluacion): JsonResponse
    {
        $data = $request->validated();
        $pesos = [
            'cuaderno' => $data['peso_cuaderno'] ?? (float) $configuracionPesoEvaluacion->peso_cuaderno,
            'libro' => $data['peso_libro'] ?? (float) $configuracionPesoEvaluacion->peso_libro,
            'tarea' => $data['peso_tarea'] ?? (float) $configuracionPesoEvaluacion->peso_tarea,
        ];
        $this->pesoResolver->validarSuma100($pesos);

        $scopeData = array_merge($configuracionPesoEvaluacion->only([
            'nivel', 'grado', 'area_id', 'curso_catalogo_id',
        ]), $data);

        if ($configuracionPesoEvaluacion->activo) {
            $this->pesoResolver->assertScopeActivoUnico($scopeData, $configuracionPesoEvaluacion->id);
        }

        $configuracionPesoEvaluacion->update($data);

        return response()->json($configuracionPesoEvaluacion->fresh(['area', 'cursoCatalogo']));
    }

    public function desactivar(ConfiguracionPesoEvaluacion $configuracionPesoEvaluacion): JsonResponse
    {
        $configuracionPesoEvaluacion->update(['activo' => false]);

        return response()->json($configuracionPesoEvaluacion->fresh(['area', 'cursoCatalogo']));
    }
}
