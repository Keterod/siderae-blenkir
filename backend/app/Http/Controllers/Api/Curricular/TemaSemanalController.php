<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\StoreTemaSemanalRequest;
use App\Http\Requests\Curricular\UpdateTemaSemanalRequest;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\TemaSemanalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemaSemanalController extends Controller
{
    public function __construct(
        private readonly TemaSemanalService $temaService = new TemaSemanalService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = TemaSemanal::query()
            ->with([
                'mallaCurso.area',
                'mallaCurso.cursoCatalogo',
                'periodoAcademico',
                'semanaAcademica',
                'competencias',
                'capacidades',
            ])
            ->orderBy('periodo_academico_id')
            ->orderByRaw('semana_academica_id IS NULL')
            ->orderBy('semana_academica_id')
            ->orderBy('created_at')
            ->orderBy('id');

        if ($request->filled('malla_curso_id')) {
            $query->where('malla_curso_id', $request->query('malla_curso_id'));
        }
        if ($request->filled('periodo_academico_id')) {
            $query->where('periodo_academico_id', $request->query('periodo_academico_id'));
        }
        if ($request->filled('area_id')) {
            $areaId = $request->query('area_id');
            $query->whereHas('mallaCurso', fn ($q) => $q->where('area_id', $areaId));
        }
        if ($request->has('activo')) {
            $activo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo !== null) {
                $query->where('activo', $activo);
            }
        }

        return response()->json($query->get());
    }

    public function show(TemaSemanal $temaSemanal): JsonResponse
    {
        $temaSemanal->load([
            'mallaCurso.area',
            'mallaCurso.cursoCatalogo',
            'periodoAcademico',
            'semanaAcademica',
            'competencias',
            'capacidades',
        ]);

        return response()->json($temaSemanal);
    }

    public function store(StoreTemaSemanalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['creado_por'] = $request->user()->id;

        $tema = $this->temaService->crear($data);

        activity()
            ->causedBy($request->user())
            ->performedOn($tema)
            ->withProperties(['accion' => 'curricular.tema.creado', 'tema_id' => $tema->id])
            ->log('Tema semanal creado');

        return response()->json($tema, 201);
    }

    public function update(UpdateTemaSemanalRequest $request, TemaSemanal $temaSemanal): JsonResponse
    {
        $temaSemanal = $this->temaService->actualizar($temaSemanal, $request->validated());

        return response()->json($temaSemanal);
    }

    public function desactivar(TemaSemanal $temaSemanal): JsonResponse
    {
        $temaSemanal->update(['activo' => false]);

        return response()->json($temaSemanal->fresh());
    }
}
