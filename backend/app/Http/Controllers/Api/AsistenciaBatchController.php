<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsistenciaBatchRequest;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Services\MlRiskService;
use App\Services\RiesgoAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AsistenciaBatchController extends Controller
{
    public function store(
        StoreAsistenciaBatchRequest $request,
        MlRiskService $mlRiskService,
        RiesgoAcademicoService $riesgoAcademicoService
    ): JsonResponse
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        $creadas = DB::transaction(function () use ($data, $userId) {
            $out = [];
            $base = [
                'semana_inicio' => $data['semana_inicio'],
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'registrado_por' => $userId,
            ];
            foreach ($data['filas'] as $fila) {
                $estudiante = Estudiante::query()->whereKey($fila['estudiante_id'])->firstOrFail();
                $payload = array_merge($base, ['estado' => $fila['estado']]);
                $out[] = $estudiante->asistencias()->create($payload);
            }

            return $out;
        });

        $ids = array_map(static fn (Asistencia $a) => $a->id, $creadas);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'accion' => 'asistencia.lote_registrado',
                'cantidad' => count($creadas),
                'anio_escolar' => $data['anio_escolar'],
                'bimestre' => $data['bimestre'],
                'semana_inicio' => $data['semana_inicio'],
                'asistencia_ids' => $ids,
            ])
            ->log('asistencia.lote_registrado');

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
