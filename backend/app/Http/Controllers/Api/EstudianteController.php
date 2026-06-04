<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstudianteRequest;
use App\Http\Requests\UpdateEstudianteRequest;
use App\Models\Estudiante;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Estudiante::query()
            ->orderBy('apellidos')
            ->orderBy('nombres');

        $query->where(
            'sede',
            SedeOperativa::defaultConsulta($request->filled('sede') ? (string) $request->query('sede') : null),
        );

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->query('nivel'));
        }

        if ($request->filled('grado')) {
            $query->where('grado', $request->query('grado'));
        }

        if ($request->filled('seccion')) {
            $query->where('seccion', $request->query('seccion'));
        }

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }

        $search = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if (! $request->boolean('incluir_inactivos')) {
            $query->where('activo', true);
        }

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $page = max(1, (int) $request->query('page', 1));

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function store(StoreEstudianteRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('activo', $data)) {
            $data['activo'] = true;
        }

        $estudiante = Estudiante::create($data);

        activity()
            ->causedBy($request->user())
            ->performedOn($estudiante)
            ->withProperties([
                'accion' => 'estudiante.creado',
                'estudiante_id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
            ])
            ->log('estudiante.creado');

        return response()->json($estudiante, 201);
    }

    public function show(Estudiante $estudiante): JsonResponse
    {
        $data = $estudiante->toArray();
        $data['ultimo_indice_riesgo'] = $estudiante->indicesRiesgo()->latest('id')->first();

        return response()->json($data);
    }

    public function update(UpdateEstudianteRequest $request, Estudiante $estudiante): JsonResponse
    {
        $estudiante->update($request->validated());
        $estudiante->refresh();

        activity()
            ->causedBy($request->user())
            ->performedOn($estudiante)
            ->withProperties([
                'accion' => 'estudiante.actualizado',
                'estudiante_id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
            ])
            ->log('estudiante.actualizado');

        return response()->json($estudiante->fresh());
    }
}
