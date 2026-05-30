<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\AsistenciaDiariaFormularioRequest;
use App\Http\Requests\Curricular\BulkAsistenciaDiariaRequest;
use App\Http\Requests\Curricular\ResumenAsistenciaDiariaRequest;
use App\Models\Estudiante;
use App\Services\Curricular\AsistenciaDiariaAuthService;
use App\Services\Curricular\AsistenciaDiariaBulkService;
use App\Services\Curricular\AsistenciaDiariaFormularioService;
use App\Services\Curricular\AsistenciaDiariaResumenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class AsistenciaDiariaController extends Controller
{
    public function __construct(
        private readonly AsistenciaDiariaAuthService $authService = new AsistenciaDiariaAuthService,
        private readonly AsistenciaDiariaFormularioService $formularioService = new AsistenciaDiariaFormularioService,
        private readonly AsistenciaDiariaBulkService $bulkService = new AsistenciaDiariaBulkService,
        private readonly AsistenciaDiariaResumenService $resumenService = new AsistenciaDiariaResumenService,
    ) {}

    public function formulario(AsistenciaDiariaFormularioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $contextoAula = $this->contextoAulaDesde($data);
        $contexto = array_merge($contextoAula, ['fecha' => $data['fecha']]);

        try {
            $this->authService->autorizarVer($request->user(), $contextoAula);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $resultado = $this->formularioService->construir($contexto);
        $puedeRegistrar = $this->authService->puedeRegistrar($request->user());

        try {
            $this->authService->autorizarRegistrar($request->user(), $contextoAula);
            $puedeRegistrarAula = true;
        } catch (AuthorizationException) {
            $puedeRegistrarAula = false;
        }

        $resultado['puede_registrar'] = $puedeRegistrar && $puedeRegistrarAula;
        $resultado['readonly'] = ! $resultado['puede_registrar'];

        return response()->json($resultado);
    }

    public function bulk(BulkAsistenciaDiariaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $contextoAula = $this->contextoAulaDesde($data);

        try {
            $this->authService->autorizarRegistrar($request->user(), $contextoAula);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $resultado = $this->bulkService->guardar($data, $request->user());

        return response()->json($resultado, 201);
    }

    public function resumen(ResumenAsistenciaDiariaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $estudiante = Estudiante::query()->findOrFail($data['estudiante_id']);

        $contextoAula = [
            'anio_escolar' => $estudiante->anio_escolar,
            'nivel' => $estudiante->nivel,
            'sede' => $estudiante->sede,
            'grado' => $estudiante->grado,
            'seccion' => $estudiante->seccion,
        ];

        try {
            $this->authService->autorizarVer($request->user(), $contextoAula);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        if ($data['anio_escolar'] !== $estudiante->anio_escolar) {
            return response()->json([
                'message' => 'El año escolar no coincide con el del estudiante.',
            ], 422);
        }

        $resultado = $this->resumenService->construirPorEstudiante($data);

        return response()->json($resultado);
    }

    /**
     * @param  array<string, string>  $data
     * @return array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string}
     */
    private function contextoAulaDesde(array $data): array
    {
        return [
            'anio_escolar' => $data['anio_escolar'],
            'nivel' => $data['nivel'],
            'sede' => $data['sede'],
            'grado' => $data['grado'],
            'seccion' => $data['seccion'],
        ];
    }
}
