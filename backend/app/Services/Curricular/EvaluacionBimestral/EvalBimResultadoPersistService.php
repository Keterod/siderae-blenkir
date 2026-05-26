<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\DTO\Curricular\NivelLogroBimestralResultado;
use App\Models\Curricular\EvalBimResultado;
use Illuminate\Support\Carbon;

class EvalBimResultadoPersistService
{
    public function __construct(
        private readonly NivelLogroBimestralService $nivelLogroService = new NivelLogroBimestralService,
    ) {}

    public function recalcularEstudiante(
        AulaEvaluacionContext $aula,
        int $estudianteId,
        ?string $conclusionDescriptiva = null,
    ): EvalBimResultado {
        $calculo = $this->nivelLogroService->calcularParaEstudiante($aula, $estudianteId, $conclusionDescriptiva);

        return $this->persistir($aula, $estudianteId, $calculo, $conclusionDescriptiva);
    }

    /**
     * @return list<EvalBimResultado>
     */
    public function recalcularAula(AulaEvaluacionContext $aula): array
    {
        $resultados = [];
        foreach ($aula->estudianteIds as $estudianteId) {
            $existente = EvalBimResultado::query()
                ->where('estudiante_id', $estudianteId)
                ->where('malla_curso_id', $aula->mallaCursoId)
                ->where('periodo_academico_id', $aula->periodoAcademicoId)
                ->where('sede', $aula->sede)
                ->where('grado', $aula->grado)
                ->where('seccion', $aula->seccion)
                ->first();

            $conclusion = $existente?->conclusion_descriptiva;
            $resultados[] = $this->recalcularEstudiante($aula, $estudianteId, $conclusion);
        }

        return $resultados;
    }

    public function persistir(
        AulaEvaluacionContext $aula,
        int $estudianteId,
        NivelLogroBimestralResultado $calculo,
        ?string $conclusionDescriptiva = null,
    ): EvalBimResultado {
        return EvalBimResultado::query()->updateOrCreate(
            [
                'estudiante_id' => $estudianteId,
                'malla_curso_id' => $aula->mallaCursoId,
                'periodo_academico_id' => $aula->periodoAcademicoId,
                'sede' => $aula->sede,
                'grado' => $aula->grado,
                'seccion' => $aula->seccion,
            ],
            [
                'promedio_criterios' => $calculo->promedioCriterios,
                'oral' => $calculo->oral,
                'promedio_eta' => $calculo->promedioEta,
                'examen_bimestral' => $calculo->examenBimestral,
                'nivel_logro_numerico' => $calculo->nivelLogroNumerico,
                'nivel_logro_literal' => $calculo->nivelLogroLiteral,
                'conclusion_descriptiva' => $conclusionDescriptiva,
                'estado_calculo' => $calculo->estadoCalculo,
                'detalle_json' => array_merge($calculo->detalle, [
                    'pendientes' => $calculo->pendientes,
                ]),
                'calculado_en' => Carbon::now(),
            ],
        );
    }

    public function actualizarConclusion(
        AulaEvaluacionContext $aula,
        int $estudianteId,
        ?string $conclusion,
    ): EvalBimResultado {
        $resultado = EvalBimResultado::query()
            ->where('estudiante_id', $estudianteId)
            ->where('malla_curso_id', $aula->mallaCursoId)
            ->where('periodo_academico_id', $aula->periodoAcademicoId)
            ->where('sede', $aula->sede)
            ->where('grado', $aula->grado)
            ->where('seccion', $aula->seccion)
            ->first();

        if ($resultado === null) {
            return $this->recalcularEstudiante($aula, $estudianteId, $conclusion);
        }

        $resultado->conclusion_descriptiva = $conclusion;
        $resultado->save();

        return $resultado;
    }
}
