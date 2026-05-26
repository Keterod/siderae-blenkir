<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimNotaEta;
use Illuminate\Support\Collection;

class EtaParticipacionPorAulaService
{
    use PesosRedistribucionSupport;

    public static function etaEstaCargada(?float $nota): bool
    {
        return $nota !== null && $nota >= 0 && $nota <= 20;
    }

    /**
     * @param  Collection<int, EvalBimEtaItem>  $etaItemsActivos
     * @return array{
     *     participantes: Collection<int, EvalBimEtaItem>,
     *     pesos_efectivos: array<int, float>,
     *     notas_por_estudiante_eta: array<int, array<int, float|null>>
     * }
     */
    public function resolverParticipacion(
        AulaEvaluacionContext $aula,
        Collection $etaItemsActivos,
    ): array {
        if ($etaItemsActivos->isEmpty() || $aula->estudianteIds === []) {
            return [
                'participantes' => collect(),
                'pesos_efectivos' => [],
                'notas_por_estudiante_eta' => [],
            ];
        }

        $etaIds = $etaItemsActivos->pluck('id')->all();
        $notas = EvalBimNotaEta::query()
            ->whereIn('estudiante_id', $aula->estudianteIds)
            ->whereIn('eval_bim_eta_item_id', $etaIds)
            ->get();

        $notasPorEstudianteEta = [];
        foreach ($aula->estudianteIds as $estudianteId) {
            $notasPorEstudianteEta[$estudianteId] = [];
            foreach ($etaIds as $etaId) {
                $notasPorEstudianteEta[$estudianteId][$etaId] = null;
            }
        }

        foreach ($notas as $nota) {
            $valor = $nota->nota !== null ? (float) $nota->nota : null;
            $notasPorEstudianteEta[$nota->estudiante_id][$nota->eval_bim_eta_item_id] = $valor;
        }

        $participantes = $etaItemsActivos->filter(function (EvalBimEtaItem $item) use ($notasPorEstudianteEta, $aula) {
            foreach ($aula->estudianteIds as $estudianteId) {
                $nota = $notasPorEstudianteEta[$estudianteId][$item->id] ?? null;
                if (self::etaEstaCargada($nota)) {
                    return true;
                }
            }

            return false;
        })->values();

        $pesosConfig = [];
        foreach ($participantes as $item) {
            $pesosConfig[$item->id] = (float) $item->peso_interno;
        }

        $pesosEfectivos = $pesosConfig === []
            ? []
            : $this->renormalizarPesos($pesosConfig);

        return [
            'participantes' => $participantes,
            'pesos_efectivos' => $pesosEfectivos,
            'notas_por_estudiante_eta' => $notasPorEstudianteEta,
        ];
    }

    /**
     * @param  array<int, float>  $pesosEfectivos
     * @param  array<int, float|null>  $notasPorEtaId
     */
    public function valorEfectivoAlumno(array $pesosEfectivos, array $notasPorEtaId): ?float
    {
        if ($pesosEfectivos === []) {
            return null;
        }

        $sumaPonderada = 0.0;
        foreach ($pesosEfectivos as $etaId => $peso) {
            $nota = $notasPorEtaId[$etaId] ?? null;
            $valor = self::etaEstaCargada($nota) ? (float) $nota : 0.0;
            $sumaPonderada += $valor * ($peso / 100);
        }

        return round($sumaPonderada, 2);
    }
}
