<?php

namespace Database\Seeders\Demo;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimNotaEta;
use App\Models\Curricular\EvalBimNotaScalar;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\NotaSemanal;
use App\Models\Curricular\TemaSemanal;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\EvaluacionBimestral\EvalBimResultadoPersistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Notas semanales y componentes bimestrales demo (sin materia_id legacy).
 */
class NotasCurricularesDemoSeeder extends Seeder
{
    /** @var list<array{ce: float, oral: float, eta: float, examen: float}> */
    private const PERFILES_NOTA = [
        ['ce' => 16.0, 'oral' => 17.0, 'eta' => 16.0, 'examen' => 15.0],
        ['ce' => 14.0, 'oral' => 14.0, 'eta' => 14.0, 'examen' => 13.0],
        ['ce' => 15.0, 'oral' => 15.0, 'eta' => 15.0, 'examen' => 14.0],
        ['ce' => 10.0, 'oral' => 10.0, 'eta' => 9.0, 'examen' => 8.0],
        ['ce' => 12.5, 'oral' => 12.0, 'eta' => 12.0, 'examen' => 11.0],
        ['ce' => 17.0, 'oral' => 16.0, 'eta' => 16.5, 'examen' => 16.0],
        ['ce' => 9.0, 'oral' => 9.5, 'eta' => 8.0, 'examen' => 7.5],
    ];

    public function run(): void
    {
        $periodo = DemoCurricularContext::periodoBimestreUno();
        $docente = DemoCurricularContext::docente();
        $estudiantes = DemoCurricularContext::estudiantesAulaPrincipal();

        if ($estudiantes->isEmpty()) {
            return;
        }

        $persistService = new EvalBimResultadoPersistService;
        $cursos = DemoCurricularContext::mallaCursosPrimaria2do()->take(2);

        foreach ($cursos as $mallaCurso) {
            $tema = TemaSemanal::query()
                ->where('malla_curso_id', $mallaCurso->id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('activo', true)
                ->orderBy('id')
                ->first();

            if ($tema === null) {
                continue;
            }

            foreach ($estudiantes as $indice => $estudiante) {
                $perfil = self::PERFILES_NOTA[$indice % count(self::PERFILES_NOTA)];
                $this->sembrarNotaSemanal($estudiante, $tema, $perfil['ce'], $docente);
                $this->sembrarComponentesBimestrales($estudiante, $mallaCurso, $periodo->id, $perfil, $docente);
            }

            $aula = new AulaEvaluacionContext(
                mallaCursoId: $mallaCurso->id,
                periodoAcademicoId: $periodo->id,
                sede: DemoCurricularContext::SEDE_PRINCIPAL,
                grado: DemoCurricularContext::GRADO_ESTUDIANTE_PRIMARIA,
                seccion: DemoCurricularContext::SECCION_PRINCIPAL,
                estudianteIds: $estudiantes->pluck('id')->all(),
            );

            $persistService->recalcularAula($aula);
        }
    }

    private function sembrarNotaSemanal(Estudiante $estudiante, TemaSemanal $tema, float $ce, User $docente): void
    {
        NotaSemanal::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_semanal_id' => $tema->id,
            ],
            [
                'docente_id' => $docente->id,
                'nota_cuaderno' => $ce,
                'nota_libro' => null,
                'nota_tarea' => null,
                'ce_calculado' => $ce,
                'fecha_registro' => Carbon::today(),
            ],
        );
    }

    /**
     * @param  array{ce: float, oral: float, eta: float, examen: float}  $perfil
     */
    private function sembrarComponentesBimestrales(
        Estudiante $estudiante,
        MallaCurso $mallaCurso,
        int $periodoId,
        array $perfil,
        User $docente,
    ): void {
        $oral = $this->componente($mallaCurso->id, $periodoId, 'oral');
        $examen = $this->componente($mallaCurso->id, $periodoId, 'examen_bimestral');
        $eta = $this->etaPorNombre($mallaCurso->id, $periodoId, 'ETA 1');

        EvalBimNotaScalar::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'eval_bim_componente_id' => $oral->id,
            ],
            [
                'nota' => $perfil['oral'],
                'docente_id' => $docente->id,
            ],
        );

        EvalBimNotaScalar::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'eval_bim_componente_id' => $examen->id,
            ],
            [
                'nota' => $perfil['examen'],
                'docente_id' => $docente->id,
            ],
        );

        EvalBimNotaEta::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'eval_bim_eta_item_id' => $eta->id,
            ],
            [
                'nota' => $perfil['eta'],
                'docente_id' => $docente->id,
            ],
        );
    }

    private function componente(int $mallaCursoId, int $periodoId, string $codigo): EvalBimComponente
    {
        return EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoId)
            ->where('codigo', $codigo)
            ->firstOrFail();
    }

    private function etaPorNombre(int $mallaCursoId, int $periodoId, string $nombre): EvalBimEtaItem
    {
        return EvalBimEtaItem::query()
            ->whereHas('componente', fn ($q) => $q
                ->where('malla_curso_id', $mallaCursoId)
                ->where('periodo_academico_id', $periodoId)
                ->where('codigo', 'promedio_eta'))
            ->where('nombre', $nombre)
            ->firstOrFail();
    }
}
