<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\StoreConfiguracionPesoRequest;
use App\Http\Requests\Curricular\UpdateConfiguracionPesoRequest;
use App\Models\Curricular\ConfiguracionPesoEvaluacion;
use App\Services\Curricular\PesoEvaluacionResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfiguracionPesoEvaluacionController extends Controller
{
    public function __construct(
        private readonly PesoEvaluacionResolver $pesoResolver = new PesoEvaluacionResolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ConfiguracionPesoEvaluacion::query()->orderByDesc('id');

        if ($request->filled('activo')) {
            $activo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo !== null) {
                $query->where('activo', $activo);
            }
        }

        return response()->json($query->get());
    }

    public function store(StoreConfiguracionPesoRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->pesoResolver->validarSuma100([
            'cuaderno' => $data['peso_cuaderno'],
            'libro' => $data['peso_libro'],
            'tarea' => $data['peso_tarea'],
        ]);

        $config = ConfiguracionPesoEvaluacion::query()->create([
            ...$data,
            'activo' => true,
        ]);

        return response()->json($config, 201);
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

        $configuracionPesoEvaluacion->update($data);

        return response()->json($configuracionPesoEvaluacion->fresh());
    }

    public function desactivar(ConfiguracionPesoEvaluacion $configuracionPesoEvaluacion): JsonResponse
    {
        $configuracionPesoEvaluacion->update(['activo' => false]);

        return response()->json($configuracionPesoEvaluacion);
    }
}
