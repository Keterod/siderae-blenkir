<?php

namespace Database\Seeders\Demo;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\SemanaAcademica;
use App\Models\Curricular\TemaSemanal;
use App\Services\Curricular\TemaSemanalService;
use Illuminate\Database\Seeder;

/**
 * Criterios de evaluación (temas semanales) mínimos para el demo curricular.
 */
class CriteriosEvaluacionDemoSeeder extends Seeder
{
    /** @var list<string> */
    private const TITULOS_DEMO = [
        'Criterio demo — semana 1',
        'Criterio demo — semana 2',
    ];

    public function run(): void
    {
        $periodo = DemoCurricularContext::periodoBimestreUno();
        $semana = SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->where('numero_semana', 1)
            ->firstOrFail();

        $coordinadorId = DemoCurricularContext::coordinador()->id;
        $temaService = new TemaSemanalService;

        $cursos = DemoCurricularContext::mallaCursosPrimaria2do()->take(2);

        foreach ($cursos as $indiceCurso => $mallaCurso) {
            $titulo = self::TITULOS_DEMO[$indiceCurso] ?? self::TITULOS_DEMO[0];
            $this->asegurarTema($temaService, $mallaCurso, $periodo->id, $semana->id, $titulo, $coordinadorId);
        }
    }

    private function asegurarTema(
        TemaSemanalService $temaService,
        MallaCurso $mallaCurso,
        int $periodoId,
        int $semanaId,
        string $titulo,
        int $creadoPor,
    ): TemaSemanal {
        $existente = TemaSemanal::query()
            ->where('malla_curso_id', $mallaCurso->id)
            ->where('periodo_academico_id', $periodoId)
            ->where('titulo', $titulo)
            ->first();

        if ($existente !== null) {
            return $existente;
        }

        $mallaCurso->loadMissing('area');
        $competencia = Competencia::query()
            ->where('area_id', $mallaCurso->area_id)
            ->where('activo', true)
            ->firstOrFail();

        $capacidad = Capacidad::query()
            ->where('competencia_id', $competencia->id)
            ->where('activo', true)
            ->firstOrFail();

        return $temaService->crear([
            'malla_curso_id' => $mallaCurso->id,
            'periodo_academico_id' => $periodoId,
            'semana_academica_id' => $semanaId,
            'titulo' => $titulo,
            'creado_por' => $creadoPor,
            'activo' => true,
            'competencia_ids' => [$competencia->id],
            'capacidad_ids' => [$capacidad->id],
        ]);
    }
}
