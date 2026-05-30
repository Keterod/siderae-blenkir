<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\StoreAnioEscolarRequest;
use App\Http\Requests\Curricular\UpdateAnioEscolarRequest;
use App\Http\Requests\Curricular\UpdatePeriodoAcademicoRequest;
use App\Models\Curricular\AnioEscolar;
use App\Models\Curricular\PeriodoAcademico;
use App\Services\Curricular\CalendarioAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnioEscolarController extends Controller
{
    public function __construct(
        private readonly CalendarioAcademicoService $calendario,
    ) {}

    public function index(): JsonResponse
    {
        $anios = AnioEscolar::query()
            ->orderByDesc('anio')
            ->get()
            ->map(fn (AnioEscolar $a) => $this->calendario->serializarAnio($a));

        return response()->json($anios);
    }

    public function activo(): JsonResponse
    {
        $payload = $this->calendario->obtenerAnioActivoConVigente();

        if ($payload === null) {
            return response()->json(['message' => 'No hay año escolar activo configurado.'], 404);
        }

        return response()->json($payload);
    }

    public function store(StoreAnioEscolarRequest $request): JsonResponse
    {
        $anio = $this->calendario->crearAnioEscolar($request->validated());

        return response()->json($this->calendario->serializarAnio($anio), 201);
    }

    public function show(AnioEscolar $anioEscolar): JsonResponse
    {
        return response()->json($this->calendario->serializarAnio($anioEscolar));
    }

    public function update(UpdateAnioEscolarRequest $request, AnioEscolar $anioEscolar): JsonResponse
    {
        $anio = $this->calendario->actualizarAnioEscolar($anioEscolar, $request->validated());

        return response()->json($this->calendario->serializarAnio($anio));
    }

    public function activar(AnioEscolar $anioEscolar): JsonResponse
    {
        $anio = $this->calendario->activarAnioEscolar($anioEscolar);

        return response()->json($this->calendario->serializarAnio($anio));
    }

    public function cerrar(AnioEscolar $anioEscolar): JsonResponse
    {
        $anio = $this->calendario->cerrarAnioEscolar($anioEscolar);

        return response()->json($this->calendario->serializarAnio($anio));
    }

    public function generarBimestres(Request $request, AnioEscolar $anioEscolar): JsonResponse
    {
        $semanas = (int) $request->input('semanas_planificadas', CalendarioAcademicoService::SEMANAS_POR_BIMESTRE_DEFAULT);
        $this->calendario->generarBimestres($anioEscolar, $semanas);
        $anioEscolar->refresh();

        return response()->json($this->calendario->serializarAnio($anioEscolar));
    }
}
