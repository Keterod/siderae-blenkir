<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\TemaSemanal;

class PromedioCriteriosService
{
    /**
     * @return array{valor: float|null, pendiente: bool, cantidad_criterios: int}
     */
    public function calcularParaEstudiante(AulaEvaluacionContext $aula, int $estudianteId): array
    {
        $temaIds = TemaSemanal::query()
            ->where('malla_curso_id', $aula->mallaCursoId)
            ->where('periodo_academico_id', $aula->periodoAcademicoId)
            ->where('activo', true)
            ->pluck('id');

        if ($temaIds->isEmpty()) {
            return ['valor' => null, 'pendiente' => true, 'cantidad_criterios' => 0];
        }

        $notas = NotaSemanal::query()
            ->where('estudiante_id', $estudianteId)
            ->whereIn('tema_semanal_id', $temaIds)
            ->whereNotNull('ce_calculado')
            ->get();

        if ($notas->isEmpty()) {
            return ['valor' => null, 'pendiente' => true, 'cantidad_criterios' => 0];
        }

        $promedio = round($notas->avg(fn (NotaSemanal $n) => (float) $n->ce_calculado), 2);

        return [
            'valor' => $promedio,
            'pendiente' => false,
            'cantidad_criterios' => $notas->count(),
        ];
    }
}
