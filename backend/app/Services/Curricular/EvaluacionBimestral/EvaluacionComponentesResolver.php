<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use Illuminate\Support\Collection;

class EvaluacionComponentesResolver
{
    public function __construct(
        private readonly EvaluacionBimestralConfiguracionService $configuracionService = new EvaluacionBimestralConfiguracionService,
    ) {}

    /**
     * @return array{
     *     componentes: Collection<int, EvalBimComponente>,
     *     componentes_activos: Collection<int, EvalBimComponente>,
     *     eta_items: Collection<int, EvalBimEtaItem>,
     *     eta_items_activos: Collection<int, EvalBimEtaItem>,
     *     componente_promedio_eta: EvalBimComponente|null
     * }
     */
    public function resolver(int $mallaCursoId, int $periodoAcademicoId, bool $asegurarDefaults = true): array
    {
        if ($asegurarDefaults) {
            $this->configuracionService->asegurarConfiguracionPorDefecto($mallaCursoId, $periodoAcademicoId);
        }

        $componentes = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $componenteEta = $componentes->first(
            fn (EvalBimComponente $c) => $c->tipo === EvalBimComponenteTipo::PromedioEta
        );

        $etaItems = $componenteEta !== null
            ? EvalBimEtaItem::query()
                ->where('eval_bim_componente_id', $componenteEta->id)
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
            : collect();

        return [
            'componentes' => $componentes,
            'componentes_activos' => $componentes->where('activo', true)->values(),
            'eta_items' => $etaItems,
            'eta_items_activos' => $etaItems->where('activo', true)->values(),
            'componente_promedio_eta' => $componenteEta,
        ];
    }

    public function obtenerPorCodigo(
        int $mallaCursoId,
        int $periodoAcademicoId,
        string $codigo,
    ): ?EvalBimComponente {
        $this->configuracionService->asegurarConfiguracionPorDefecto($mallaCursoId, $periodoAcademicoId);

        return EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->where('codigo', $codigo)
            ->first();
    }
}
