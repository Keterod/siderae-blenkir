<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimNotaEta;
use App\Models\Curricular\EvalBimNotaScalar;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\CurricularNotasAuthService;
use App\Services\Curricular\EstudianteAsignacionDocenteValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluacionBimestralBulkService
{
    public const ADVERTENCIA_NO_ELIMINAR_NOTA = 'Para eliminar una nota registrada se requiere una acción específica.';

    public function __construct(
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
        private readonly EvalBimResultadoPersistService $resultadoPersistService = new EvalBimResultadoPersistService,
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
        private readonly CurricularNotasAuthService $notasAuth = new CurricularNotasAuthService,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $registrosPorEstudiante
     * @return array{resultados: list<\App\Models\Curricular\EvalBimResultado>, advertencias: list<string>}
     */
    public function registrar(
        User $docente,
        DocenteCursoAula $asignacion,
        int $periodoAcademicoId,
        array $registrosPorEstudiante,
    ): array {
        $this->notasAuth->assertPuedeRegistrarEnAsignacion($docente, $asignacion);

        if (! $asignacion->activo) {
            throw ValidationException::withMessages([
                'asignacion_docente_id' => ['La asignación docente está inactiva.'],
            ]);
        }

        $periodo = PeriodoAcademico::query()->findOrFail($periodoAcademicoId);
        if ($periodo->anio_escolar !== $asignacion->anio_escolar) {
            throw ValidationException::withMessages([
                'periodo_academico_id' => ['El bimestre no corresponde al año escolar de la asignación.'],
            ]);
        }

        $config = $this->componentesResolver->resolver($asignacion->malla_curso_id, $periodoAcademicoId);
        $componentesPorCodigo = $config['componentes']->keyBy('codigo');
        $componentesPorId = $config['componentes']->keyBy('id');
        $etasPorId = $config['eta_items']->keyBy('id');

        $oral = $componentesPorCodigo->get('oral');
        $examen = $componentesPorCodigo->get('examen_bimestral');

        $advertencias = [];
        $estudianteIds = [];

        DB::transaction(function () use (
            $registrosPorEstudiante,
            $asignacion,
            $docente,
            $oral,
            $examen,
            $componentesPorId,
            $etasPorId,
            &$advertencias,
            &$estudianteIds,
        ): void {
            foreach ($registrosPorEstudiante as $indice => $fila) {
                $estudiante = Estudiante::query()->findOrFail($fila['estudiante_id']);
                if (! $this->estudianteValidator->perteneceAAsignacion($estudiante, $asignacion)) {
                    throw ValidationException::withMessages([
                        "registros_por_estudiante.{$indice}.estudiante_id" => [
                            'El estudiante no pertenece a la asignación docente indicada.',
                        ],
                    ]);
                }

                $estudianteIds[] = $estudiante->id;

                if ($oral !== null && array_key_exists('oral', $fila)) {
                    $this->persistirScalar(
                        $estudiante->id,
                        $oral,
                        $fila['oral'],
                        $docente->id,
                        $advertencias,
                        "registros_por_estudiante.{$indice}.oral",
                    );
                }

                if ($examen !== null && array_key_exists('examen_bimestral', $fila)) {
                    $this->persistirScalar(
                        $estudiante->id,
                        $examen,
                        $fila['examen_bimestral'],
                        $docente->id,
                        $advertencias,
                        "registros_por_estudiante.{$indice}.examen_bimestral",
                    );
                }

                foreach ($fila['componentes_personalizados'] ?? [] as $j => $item) {
                    $componente = $componentesPorId->get($item['componente_id'] ?? 0);
                    if ($componente === null || $componente->tipo !== EvalBimComponenteTipo::Personalizado) {
                        throw ValidationException::withMessages([
                            "registros_por_estudiante.{$indice}.componentes_personalizados.{$j}.componente_id" => [
                                'Componente personalizado no válido para este curso y bimestre.',
                            ],
                        ]);
                    }
                    if (! array_key_exists('nota', $item)) {
                        continue;
                    }
                    $this->persistirScalar(
                        $estudiante->id,
                        $componente,
                        $item['nota'],
                        $docente->id,
                        $advertencias,
                        "registros_por_estudiante.{$indice}.componentes_personalizados.{$j}.nota",
                    );
                }

                foreach ($fila['etas'] ?? [] as $j => $item) {
                    $eta = $etasPorId->get($item['eta_item_id'] ?? 0);
                    if ($eta === null) {
                        throw ValidationException::withMessages([
                            "registros_por_estudiante.{$indice}.etas.{$j}.eta_item_id" => [
                                'ETA no válida para este curso y bimestre.',
                            ],
                        ]);
                    }
                    if (! array_key_exists('nota', $item)) {
                        continue;
                    }
                    $this->persistirEta(
                        $estudiante->id,
                        $eta,
                        $item['nota'],
                        $docente->id,
                        $advertencias,
                        "registros_por_estudiante.{$indice}.etas.{$j}.nota",
                    );
                }
            }
        });

        $estudiantesAula = Estudiante::query()
            ->where('anio_escolar', $asignacion->anio_escolar)
            ->where('nivel', $asignacion->nivel)
            ->where('sede', $asignacion->sede)
            ->where('activo', true)
            ->get()
            ->filter(fn (Estudiante $e) => $this->estudianteValidator->perteneceAAsignacion($e, $asignacion))
            ->values();

        $aula = new AulaEvaluacionContext(
            mallaCursoId: $asignacion->malla_curso_id,
            periodoAcademicoId: $periodoAcademicoId,
            sede: $asignacion->sede,
            grado: $asignacion->grado,
            seccion: $asignacion->seccion,
            estudianteIds: $estudiantesAula->pluck('id')->all(),
        );

        $conclusionesPorEstudiante = collect($registrosPorEstudiante)->keyBy('estudiante_id');

        $resultados = [];
        foreach ($estudiantesAula as $estudiante) {
            $fila = $conclusionesPorEstudiante->get($estudiante->id);
            $conclusion = is_array($fila) && array_key_exists('conclusion_descriptiva', $fila)
                ? $fila['conclusion_descriptiva']
                : null;

            if ($conclusion !== null) {
                $existente = \App\Models\Curricular\EvalBimResultado::query()
                    ->where('estudiante_id', $estudiante->id)
                    ->where('malla_curso_id', $aula->mallaCursoId)
                    ->where('periodo_academico_id', $aula->periodoAcademicoId)
                    ->where('sede', $aula->sede)
                    ->where('grado', $aula->grado)
                    ->where('seccion', $aula->seccion)
                    ->first();
                $conclusion = $conclusion !== '' ? $conclusion : null;
                if ($existente !== null) {
                    $existente->conclusion_descriptiva = $conclusion;
                    $existente->save();
                }
            }

            $resultados[] = $this->resultadoPersistService->recalcularEstudiante(
                $aula,
                $estudiante->id,
                $this->conclusionParaRecalculo($estudiante->id, $aula, $conclusionesPorEstudiante),
            );
        }

        return [
            'resultados' => $resultados,
            'advertencias' => array_values(array_unique($advertencias)),
        ];
    }

    private function conclusionParaRecalculo(int $estudianteId, AulaEvaluacionContext $aula, $conclusionesPorEstudiante): ?string
    {
        $fila = $conclusionesPorEstudiante->get($estudianteId);
        if (is_array($fila) && array_key_exists('conclusion_descriptiva', $fila)) {
            $texto = $fila['conclusion_descriptiva'];

            return $texto !== null && $texto !== '' ? (string) $texto : null;
        }

        $existente = \App\Models\Curricular\EvalBimResultado::query()
            ->where('estudiante_id', $estudianteId)
            ->where('malla_curso_id', $aula->mallaCursoId)
            ->where('periodo_academico_id', $aula->periodoAcademicoId)
            ->where('sede', $aula->sede)
            ->where('grado', $aula->grado)
            ->where('seccion', $aula->seccion)
            ->first();

        return $existente?->conclusion_descriptiva;
    }

    private function persistirScalar(
        int $estudianteId,
        EvalBimComponente $componente,
        mixed $nota,
        int $docenteId,
        array &$advertencias,
        string $campo,
    ): void {
        if ($nota === null) {
            $existente = EvalBimNotaScalar::query()
                ->where('estudiante_id', $estudianteId)
                ->where('eval_bim_componente_id', $componente->id)
                ->first();
            if ($existente !== null && $existente->nota !== null) {
                $advertencias[] = self::ADVERTENCIA_NO_ELIMINAR_NOTA;
            }

            return;
        }

        $valor = $this->validarNota($nota, $campo);

        EvalBimNotaScalar::query()->updateOrCreate(
            [
                'estudiante_id' => $estudianteId,
                'eval_bim_componente_id' => $componente->id,
            ],
            [
                'nota' => $valor,
                'docente_id' => $docenteId,
            ],
        );
    }

    private function persistirEta(
        int $estudianteId,
        EvalBimEtaItem $eta,
        mixed $nota,
        int $docenteId,
        array &$advertencias,
        string $campo,
    ): void {
        if ($nota === null) {
            $existente = EvalBimNotaEta::query()
                ->where('estudiante_id', $estudianteId)
                ->where('eval_bim_eta_item_id', $eta->id)
                ->first();
            if ($existente !== null && $existente->nota !== null) {
                $advertencias[] = self::ADVERTENCIA_NO_ELIMINAR_NOTA;
            }

            return;
        }

        $valor = $this->validarNota($nota, $campo);

        EvalBimNotaEta::query()->updateOrCreate(
            [
                'estudiante_id' => $estudianteId,
                'eval_bim_eta_item_id' => $eta->id,
            ],
            [
                'nota' => $valor,
                'docente_id' => $docenteId,
            ],
        );
    }

    private function validarNota(mixed $nota, string $campo): float
    {
        if (! is_numeric($nota)) {
            throw ValidationException::withMessages([
                $campo => ['La nota debe ser numérica.'],
            ]);
        }

        $valor = (float) $nota;
        if ($valor < 0 || $valor > 20) {
            throw new NotaCurricularFueraDeRangoException;
        }

        return round($valor, 2);
    }
}
