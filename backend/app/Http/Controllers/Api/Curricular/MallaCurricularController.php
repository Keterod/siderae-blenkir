<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\CargarPlantillaMallaRequest;
use App\Http\Requests\Curricular\StoreMallaCursoRequest;
use App\Http\Requests\Curricular\UpdateMallaCursoRequest;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Services\Curricular\MallaCurricularService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MallaCurricularController extends Controller
{
    public function __construct(
        private readonly MallaCurricularService $mallaService = new MallaCurricularService,
    ) {}

    public function grado(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', 'string'],
            'grado' => ['required', 'string', 'max:20'],
        ]);

        $malla = $this->mallaService->obtenerOProvisionar(
            $data['anio_escolar'],
            $data['nivel'],
            $data['grado'],
        );

        return response()->json($malla);
    }

    public function index(Request $request): JsonResponse
    {
        $query = MallaCurricular::query()
            ->withCount(['mallaCursos as cursos_activos_count' => fn ($q) => $q->where('activo', true)])
            ->with('plantillaCurricular')
            ->orderByDesc('anio_escolar')
            ->orderBy('nivel')
            ->orderBy('grado');

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->query('nivel'));
        }
        if ($request->filled('grado')) {
            $query->where('grado', $request->query('grado'));
        }

        return response()->json($query->get());
    }

    public function show(MallaCurricular $malla): JsonResponse
    {
        $malla->load([
            'plantillaCurricular',
            'mallaCursos' => fn ($q) => $q->orderBy('orden'),
            'mallaCursos.area',
            'mallaCursos.cursoCatalogo',
        ]);

        return response()->json($malla);
    }

    public function cargarPlantilla(CargarPlantillaMallaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $malla = $this->mallaService->cargarDesdePlantilla(
            $data['anio_escolar'],
            $data['nivel'],
            $data['grado'],
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($malla)
            ->withProperties([
                'accion' => 'curricular.malla.cargar_plantilla',
                'anio_escolar' => $malla->anio_escolar,
                'nivel' => $malla->nivel,
                'grado' => $malla->grado,
            ])
            ->log('Malla curricular cargada desde plantilla');

        return response()->json($malla, 201);
    }

    public function agregarCurso(StoreMallaCursoRequest $request, MallaCurricular $malla): JsonResponse
    {
        $result = $this->mallaService->agregarCurso($malla, $request->validated());
        $payload = $result['malla_curso']->toArray();
        $payload['message'] = $result['message'];

        return response()->json($payload, 201);
    }

    public function actualizarCurso(UpdateMallaCursoRequest $request, MallaCurricular $malla, MallaCurso $mallaCurso): JsonResponse
    {
        if ($mallaCurso->malla_curricular_id !== $malla->id) {
            return response()->json(['message' => 'El curso no pertenece a esta malla.'], 404);
        }

        $data = $request->validated();
        $mallaCurso->update([
            'curso_catalogo_id' => $data['curso_catalogo_id'],
            'orden' => $data['orden'] ?? $mallaCurso->orden,
        ]);

        return response()->json($mallaCurso->fresh(['area', 'cursoCatalogo']));
    }

    public function desactivarCurso(MallaCurricular $malla, MallaCurso $mallaCurso): JsonResponse
    {
        if ($mallaCurso->malla_curricular_id !== $malla->id) {
            return response()->json(['message' => 'El curso no pertenece a esta malla.'], 404);
        }

        $mallaCurso->update(['activo' => false]);

        return response()->json($mallaCurso->fresh(['area', 'cursoCatalogo']));
    }

    public function reactivarCurso(MallaCurricular $malla, MallaCurso $mallaCurso): JsonResponse
    {
        if ($mallaCurso->malla_curricular_id !== $malla->id) {
            return response()->json(['message' => 'El curso no pertenece a esta malla.'], 404);
        }

        $mallaCurso->update(['activo' => true]);

        return response()->json($mallaCurso->fresh(['area', 'cursoCatalogo']));
    }
}
