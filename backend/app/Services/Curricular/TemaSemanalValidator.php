<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\TemaSemanalDuplicadoException;
use App\Models\Curricular\TemaSemanal;

class TemaSemanalValidator
{
    /**
     * Evita duplicado exacto: mismo curso, bimestre, título normalizado y mismas competencias/capacidades.
     *
     * @param  array{
     *   malla_curso_id: int,
     *   periodo_academico_id: int,
     *   titulo: string,
     *   competencia_ids: list<int>,
     *   capacidad_ids: list<int>,
     * }  $datos
     */
    public function validarDuplicadoExacto(array $datos, ?int $exceptoId = null): void
    {
        $tituloNorm = $this->normalizarTitulo($datos['titulo']);
        $competenciaIds = $this->ordenarIds($datos['competencia_ids'] ?? []);
        $capacidadIds = $this->ordenarIds($datos['capacidad_ids'] ?? []);

        $query = TemaSemanal::query()
            ->where('malla_curso_id', $datos['malla_curso_id'])
            ->where('periodo_academico_id', $datos['periodo_academico_id'])
            ->where('activo', true)
            ->whereRaw('LOWER(TRIM(titulo)) = ?', [$tituloNorm])
            ->with(['competencias', 'capacidades']);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        foreach ($query->get() as $candidato) {
            $compCandidato = $this->ordenarIds($candidato->competencias->pluck('id')->all());
            $capCandidato = $this->ordenarIds($candidato->capacidades->pluck('id')->all());

            if ($compCandidato === $competenciaIds && $capCandidato === $capacidadIds) {
                throw new TemaSemanalDuplicadoException;
            }
        }
    }

    private function normalizarTitulo(string $titulo): string
    {
        return mb_strtolower(trim($titulo));
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function ordenarIds(array $ids): array
    {
        $normalizados = array_map('intval', array_unique($ids));
        sort($normalizados);

        return $normalizados;
    }
}
