<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\UpdatePeriodoAcademicoRequest;
use App\Models\Curricular\PeriodoAcademico;
use App\Services\Curricular\CalendarioAcademicoService;
use Illuminate\Http\JsonResponse;

class PeriodoAcademicoAdminController extends Controller
{
    public function __construct(
        private readonly CalendarioAcademicoService $calendario,
    ) {}

    public function update(UpdatePeriodoAcademicoRequest $request, PeriodoAcademico $periodoAcademico): JsonResponse
    {
        $periodo = $this->calendario->actualizarPeriodo($periodoAcademico, $request->validated());

        return response()->json($this->calendario->serializarPeriodo($periodo));
    }

    public function marcarVigente(PeriodoAcademico $periodoAcademico): JsonResponse
    {
        $periodo = $this->calendario->marcarPeriodoVigente($periodoAcademico);

        return response()->json($this->calendario->serializarPeriodo($periodo));
    }

    public function cerrar(PeriodoAcademico $periodoAcademico): JsonResponse
    {
        $periodo = $this->calendario->cerrarPeriodo($periodoAcademico);

        return response()->json($this->calendario->serializarPeriodo($periodo));
    }

    public function generarSemanas(PeriodoAcademico $periodoAcademico): JsonResponse
    {
        $this->calendario->generarSemanas($periodoAcademico);
        $periodoAcademico->refresh();

        return response()->json($this->calendario->serializarPeriodo($periodoAcademico));
    }
}
