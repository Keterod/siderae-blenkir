<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Models\Curricular\EvalBimEtaItem;
use Illuminate\Support\Collection;

class PesosEtaInternosService
{
    use PesosRedistribucionSupport;

    /**
     * @param  Collection<int, EvalBimEtaItem>  $items
     */
    public function redistribuirEntreActivos(Collection $items): void
    {
        $activos = $items->where('activo', true)->values();
        $pesos = $this->redistribuirEquitativo($activos->count());

        foreach ($activos as $indice => $item) {
            $item->peso_interno = $pesos[$indice];
            $item->save();
        }
    }

    public function redistribuirTrasAgregar(EvalBimEtaItem $nuevo, int $componenteEtaId): void
    {
        $items = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $componenteEtaId)
            ->where('activo', true)
            ->get();

        if (! $items->contains('id', $nuevo->id)) {
            $items->push($nuevo);
        }

        $this->redistribuirEntreActivos($items);
    }

    public function redistribuirTrasDesactivar(int $componenteEtaId): void
    {
        $items = EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $componenteEtaId)
            ->where('activo', true)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $this->redistribuirEntreActivos($items);
    }

    /**
     * @param  array<int, float>  $pesosPorEtaId
     */
    public function validarPesosManuales(array $pesosPorEtaId): void
    {
        $this->validarSuma100($pesosPorEtaId);
    }
}
