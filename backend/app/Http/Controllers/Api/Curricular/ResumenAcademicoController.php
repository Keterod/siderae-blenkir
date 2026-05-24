<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Services\Curricular\ResumenAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumenAcademicoController extends Controller
{
    public function __construct(
        private readonly ResumenAcademicoService $resumenService = new ResumenAcademicoService,
    ) {}

    public function show(Request $request, Estudiante $estudiante): JsonResponse
    {
        $anio = $request->query('anio_escolar', $estudiante->anio_escolar);

        return response()->json($this->resumenService->construir($estudiante, $anio));
    }
}
