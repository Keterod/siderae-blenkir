<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMateriaRequest;
use App\Http\Requests\UpdateMateriaRequest;
use App\Models\Materia;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Materia::query()->orderBy('anio_escolar')->orderBy('nivel')->orderBy('grado')->orderBy('nombre');

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->query('nivel'));
        }

        if ($request->filled('grado')) {
            $query->where('grado', $request->query('grado'));
        }

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }

        $query->where(
            'sede',
            SedeOperativa::defaultConsulta($request->filled('sede') ? (string) $request->query('sede') : null),
        );

        if ($request->filled('activo')) {
            $filtroActivo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtroActivo !== null) {
                $query->where('activo', $filtroActivo);
            }
        }

        return response()->json($query->get());
    }

    public function store(StoreMateriaRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('activo', $data)) {
            $data['activo'] = true;
        }

        $duplicado = Materia::query()
            ->where('nombre', $data['nombre'])
            ->where('nivel', $data['nivel'])
            ->where('grado', $data['grado'])
            ->where('anio_escolar', $data['anio_escolar'])
            ->where('sede', $data['sede'])
            ->exists();

        if ($duplicado) {
            return response()->json([
                'message' => 'Ya existe una materia con el mismo nombre, nivel, grado, año escolar y sede.',
                'errors' => [
                    'nombre' => ['combinación duplicada'],
                ],
            ], 422);
        }

        $materia = Materia::create($data);

        activity()
            ->causedBy($request->user())
            ->performedOn($materia)
            ->withProperties([
                'accion' => 'materia.creada',
                'materia_id' => $materia->id,
                'nombre' => $materia->nombre,
                'nivel' => $materia->nivel,
                'grado' => $materia->grado,
                'anio_escolar' => $materia->anio_escolar,
                'sede' => $materia->sede,
                'activo' => $materia->activo,
            ])
            ->log('materia.creada');

        return response()->json($materia, 201);
    }

    public function show(Materia $materia): JsonResponse
    {
        return response()->json($materia);
    }

    public function update(UpdateMateriaRequest $request, Materia $materia): JsonResponse
    {
        $validated = $request->validated();

        $nombre = $validated['nombre'] ?? $materia->nombre;
        $nivel = $validated['nivel'] ?? $materia->nivel;
        $grado = $validated['grado'] ?? $materia->grado;
        $anioEscolar = $validated['anio_escolar'] ?? $materia->anio_escolar;
        $sede = $validated['sede'] ?? $materia->sede;

        $duplicado = Materia::query()
            ->where('nombre', $nombre)
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('anio_escolar', $anioEscolar)
            ->where('sede', $sede)
            ->where('id', '!=', $materia->id)
            ->exists();

        if ($duplicado) {
            return response()->json([
                'message' => 'Ya existe una materia con el mismo nombre, nivel, grado, año escolar y sede.',
                'errors' => [
                    'nombre' => ['combinación duplicada'],
                ],
            ], 422);
        }

        $materia->update($validated);
        $materia->refresh();

        activity()
            ->causedBy($request->user())
            ->performedOn($materia)
            ->withProperties([
                'accion' => 'materia.actualizada',
                'materia_id' => $materia->id,
                'nombre' => $materia->nombre,
                'nivel' => $materia->nivel,
                'grado' => $materia->grado,
                'anio_escolar' => $materia->anio_escolar,
                'sede' => $materia->sede,
                'activo' => $materia->activo,
            ])
            ->log('materia.actualizada');

        return response()->json($materia);
    }

    public function desactivar(Request $request, Materia $materia): JsonResponse
    {
        $materia->activo = false;
        $materia->save();

        activity()
            ->causedBy($request->user())
            ->performedOn($materia)
            ->withProperties([
                'accion' => 'materia.desactivada',
                'materia_id' => $materia->id,
            ])
            ->log('materia.desactivada');

        return response()->json($materia);
    }

    public function activar(Request $request, Materia $materia): JsonResponse
    {
        $materia->activo = true;
        $materia->save();

        activity()
            ->causedBy($request->user())
            ->performedOn($materia)
            ->withProperties([
                'accion' => 'materia.activada',
                'materia_id' => $materia->id,
            ])
            ->log('materia.activada');

        return response()->json($materia);
    }
}
