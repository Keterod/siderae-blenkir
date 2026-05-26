<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Models\Curricular\EvalBimEtaItem;
use Illuminate\Support\Collection;

class PromedioEtaService
{
    public function __construct(
        private readonly EtaParticipacionPorAulaService $participacionService = new EtaParticipacionPorAulaService,
    ) {}

    /**
     * @param  Collection<int, EvalBimEtaItem>  $etaItemsActivos
     * @return array{
     *     por_estudiante: array<int, array{valor: float|null, pendiente_bloque: bool}>,
     *     participantes_ids: list<int>,
     *     pesos_efectivos: array<int, float>
     * }
     */
    public function calcularParaAula(AulaEvaluacionContext $aula, Collection $etaItemsActivos): array
    {
        $participacion = $this->participacionService->resolverParticipacion($aula, $etaItemsActivos);
        $sinParticipantes = $participacion['participantes']->isEmpty();

        $porEstudiante = [];
        foreach ($aula->estudianteIds as $estudianteId) {
            if ($sinParticipantes) {
                $porEstudiante[$estudianteId] = [
                    'valor' => null,
                    'pendiente_bloque' => true,
                ];

                continue;
            }

            $notasEta = $participacion['notas_por_estudiante_eta'][$estudianteId] ?? [];
            $valor = $this->participacionService->valorEfectivoAlumno(
                $participacion['pesos_efectivos'],
                $notasEta,
            );

            $porEstudiante[$estudianteId] = [
                'valor' => $valor,
                'pendiente_bloque' => false,
            ];
        }

        return [
            'por_estudiante' => $porEstudiante,
            'participantes_ids' => $participacion['participantes']->pluck('id')->all(),
            'pesos_efectivos' => $participacion['pesos_efectivos'],
        ];
    }
}
