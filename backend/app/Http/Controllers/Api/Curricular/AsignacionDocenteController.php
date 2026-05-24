<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\BulkAsignacionDocenteRequest;
use App\Http\Requests\Curricular\StoreAsignacionDocenteRequest;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\User;
use App\Services\Curricular\AsignacionDocenteBulkService;
use App\Services\Curricular\DocenteCurricularListadoService;
use App\Services\Curricular\DocenteCursoAulaValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{
    public function __construct(
        private readonly DocenteCursoAulaValidator $validator = new DocenteCursoAulaValidator,
        private readonly AsignacionDocenteBulkService $bulkService = new AsignacionDocenteBulkService,
        private readonly DocenteCurricularListadoService $docenteListadoService = new DocenteCurricularListadoService,
    ) {}

    public function docentes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'anio_escolar' => ['nullable', 'string', 'max:20'],
            'nivel' => ['nullable', 'string', 'max:20'],
            'sede' => ['nullable', 'string', 'max:20'],
        ]);

        $docentes = $this->docenteListadoService->listarDocentes(
            $data['search'] ?? null,
            $data['anio_escolar'] ?? null,
            $data['nivel'] ?? null,
            $data['sede'] ?? null,
        );

        return response()->json($docentes);
    }

    public function index(Request $request): JsonResponse
    {
        $query = DocenteCursoAula::query()
            ->with(['user:id,name,email', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
            ->orderByDesc('id');

        if ($request->filled('activo')) {
            $activo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo !== null) {
                $query->where('activo', $activo);
            }
        }
        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->query('nivel'));
        }
        if ($request->filled('sede')) {
            $query->where('sede', $request->query('sede'));
        }
        if ($request->filled('grado')) {
            $query->where('grado', $request->query('grado'));
        }
        if ($request->filled('seccion')) {
            $query->where('seccion', $request->query('seccion'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        return response()->json($query->get());
    }

    public function porDocente(Request $request, User $docente): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', 'string', 'max:20'],
            'sede' => ['required', 'string', 'max:20'],
        ]);

        $resumen = $this->bulkService->construirResumenDocente(
            $docente->id,
            $data['anio_escolar'],
            $data['nivel'],
            $data['sede'],
        );

        return response()->json([
            'docente' => [
                'id' => $docente->id,
                'name' => $docente->name,
                'email' => $docente->email,
            ],
            'resumen' => $resumen,
        ]);
    }

    public function store(StoreAsignacionDocenteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['activo'] = true;

        $docente = User::query()->findOrFail($data['user_id']);
        $this->validator->validarUsuarioDocente($docente);
        $this->validator->validarMallaCursosEnMallaGrado(
            [$data['malla_curso_id']],
            $data['anio_escolar'],
            $data['nivel'],
            $data['grado'],
        );
        $this->validator->validarMallaCursosActivos([$data['malla_curso_id']]);
        $this->validator->validarAsignacionUnicaActiva($data);

        $asignacion = DocenteCursoAula::query()->create($data);
        $asignacion->load(['user:id,name,email', 'mallaCurso.area', 'mallaCurso.cursoCatalogo']);

        activity()
            ->causedBy($request->user())
            ->performedOn($asignacion)
            ->withProperties(['accion' => 'curricular.asignacion_docente.creada', 'id' => $asignacion->id])
            ->log('Asignación docente creada');

        return response()->json($asignacion, 201);
    }

    public function bulk(BulkAsignacionDocenteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $resultado = $this->bulkService->sincronizar($data);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'curricular.asignacion_docente.bulk',
                'docente_id' => $data['docente_id'],
                'malla_curso_ids' => $data['malla_curso_ids'],
            ])
            ->log('Asignación docente masiva sincronizada');

        return response()->json([
            'asignaciones' => $resultado['asignaciones'],
            'resumen' => $resultado['resumen'],
        ]);
    }

    public function desactivar(DocenteCursoAula $docenteCursoAula): JsonResponse
    {
        $docenteCursoAula->update(['activo' => false]);

        return response()->json($docenteCursoAula->fresh(['user:id,name,email', 'mallaCurso.area', 'mallaCurso.cursoCatalogo']));
    }
}
