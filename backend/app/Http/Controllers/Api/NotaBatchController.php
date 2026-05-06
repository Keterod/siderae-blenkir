<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotaBatchRequest;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Nota;
use App\Services\MlRiskService;
use App\Services\RiesgoAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotaBatchController extends Controller
{
    public function store(
        StoreNotaBatchRequest $request,
        MlRiskService $mlRiskService,
        RiesgoAcademicoService $riesgoAcademicoService
    ): JsonResponse
    {
        $data = $request->validated();
        $materia = Materia::query()->whereKey((int) $data['materia_id'])->firstOrFail();
        $cursoNombre = $materia->nombre;

        $creadas = DB::transaction(function () use ($data, $materia, $cursoNombre) {
            $out = [];
            foreach ($data['filas'] as $fila) {
                $estudiante = Estudiante::query()->whereKey($fila['estudiante_id'])->firstOrFail();
                $payload = [
                    'anio_escolar' => $data['anio_escolar'],
                    'bimestre' => $data['bimestre'],
                    'curso' => $cursoNombre,
                    'nota' => $fila['nota'],
                    'nota_conducta' => array_key_exists('nota_conducta', $fila) ? $fila['nota_conducta'] : null,
                    'materia_id' => $materia->id,
                ];
                $out[] = $estudiante->notas()->create($payload);
            }

            return $out;
        });

        $ids = array_map(static fn (Nota $n) => $n->id, $creadas);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'nota.lote_registrado',
                'materia_id' => $materia->id,
                'cantidad' => count($creadas),
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'curso' => $cursoNombre,
                'nota_ids' => $ids,
            ])
            ->log('nota.lote_registrado');

        $resumenRiesgo = $this->procesarRiesgosLote(
            collect($creadas)->pluck('estudiante_id')->unique()->values(),
            (string) $data['anio_escolar'],
            (string) $data['bimestre'],
            $mlRiskService,
            $riesgoAcademicoService
        );

        return response()->json([
            'creadas' => $creadas,
            'total' => count($creadas),
            'riesgo' => $resumenRiesgo,
            'registros_guardados' => count($creadas),
            'riesgos_procesados' => $resumenRiesgo['procesados'],
            'riesgos_omitidos' => $resumenRiesgo['omitidos'],
            'riesgos_fallidos' => $resumenRiesgo['fallidos'],
        ], 201);
    }

    /**
     * @param  Collection<int, int>  $estudianteIds
     * @return array{procesados: int, omitidos: array<int, array<string, mixed>>, fallidos: array<int, array<string, mixed>>}
     */
    private function procesarRiesgosLote(
        Collection $estudianteIds,
        string $anio,
        string $bimestre,
        MlRiskService $mlRiskService,
        RiesgoAcademicoService $riesgoAcademicoService
    ): array {
        $procesados = 0;
        $omitidos = [];
        $fallidos = [];

        $estudiantes = Estudiante::query()->whereIn('id', $estudianteIds->all())->get()->keyBy('id');

        foreach ($estudianteIds as $estudianteId) {
            /** @var Estudiante|null $estudiante */
            $estudiante = $estudiantes->get((int) $estudianteId);
            if (! $estudiante) {
                continue;
            }

            $resultado = $riesgoAcademicoService->procesarEstudiante(
                $estudiante,
                $anio,
                $bimestre,
                $mlRiskService
            );

            if ($resultado['status'] === 'procesado') {
                $procesados++;
                continue;
            }

            if ($resultado['status'] === 'omitido') {
                $omitidos[] = [
                    'estudiante_id' => $estudiante->id,
                    'codigo' => $estudiante->codigo,
                    'motivo' => 'Faltan datos mínimos.',
                    'errors' => $resultado['errors'] ?? [],
                ];
                continue;
            }

            $fallidos[] = [
                'estudiante_id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
                'motivo' => $resultado['message'] ?? 'Error al procesar riesgo.',
            ];
        }

        return [
            'procesados' => $procesados,
            'omitidos' => $omitidos,
            'fallidos' => $fallidos,
        ];
    }
}
