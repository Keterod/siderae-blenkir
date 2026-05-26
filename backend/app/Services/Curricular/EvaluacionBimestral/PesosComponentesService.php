<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Models\Curricular\EvalBimComponente;
use Illuminate\Support\Collection;

class PesosComponentesService
{
    use PesosRedistribucionSupport;

    /**
     * @param  Collection<int, EvalBimComponente>  $componentes
     */
    public function redistribuirEntreActivos(Collection $componentes): void
    {
        $activos = $componentes->where('activo', true)->values();
        $pesos = $this->redistribuirEquitativo($activos->count());

        foreach ($activos as $indice => $componente) {
            $componente->peso = $pesos[$indice];
            $componente->save();
        }
    }

    public function redistribuirTrasAgregar(EvalBimComponente $nuevo, int $mallaCursoId, int $periodoAcademicoId): void
    {
        $componentes = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->where('activo', true)
            ->get();

        if (! $componentes->contains('id', $nuevo->id)) {
            $componentes->push($nuevo);
        }

        $this->redistribuirEntreActivos($componentes);
    }

    public function redistribuirTrasDesactivar(int $mallaCursoId, int $periodoAcademicoId): void
    {
        $componentes = EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->where('activo', true)
            ->get();

        if ($componentes->isEmpty()) {
            return;
        }

        $this->redistribuirEntreActivos($componentes);
    }

    /**
     * @param  array<int, float>  $pesosPorComponenteId
     */
    public function validarPesosManuales(array $pesosPorComponenteId): void
    {
        $this->validarSuma100($pesosPorComponenteId);
    }
}
