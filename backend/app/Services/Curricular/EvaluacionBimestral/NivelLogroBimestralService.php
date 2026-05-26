<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\DTO\Curricular\NivelLogroBimestralResultado;
use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimNotaScalar;
use Illuminate\Support\Collection;

class NivelLogroBimestralService
{
    public function __construct(
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
        private readonly PromedioCriteriosService $promedioCriteriosService = new PromedioCriteriosService,
        private readonly PromedioEtaService $promedioEtaService = new PromedioEtaService,
        private readonly EscalaLogroService $escalaLogroService = new EscalaLogroService,
    ) {}

    public function calcularParaEstudiante(
        AulaEvaluacionContext $aula,
        int $estudianteId,
        ?string $conclusionDescriptiva = null,
    ): NivelLogroBimestralResultado {
        $config = $this->componentesResolver->resolver($aula->mallaCursoId, $aula->periodoAcademicoId);
        /** @var Collection<int, EvalBimComponente> $activos */
        $activos = $config['componentes_activos'];

        $pendientes = [];
        $valoresPorCodigo = [];
        $detalle = ['componentes' => []];

        $promedioCriterios = null;
        $oral = null;
        $promedioEta = null;
        $examen = null;

        $etaAula = $this->promedioEtaService->calcularParaAula($aula, $config['eta_items_activos']);
        $etaEstudiante = $etaAula['por_estudiante'][$estudianteId] ?? ['valor' => null, 'pendiente_bloque' => true];

        $notasScalar = EvalBimNotaScalar::query()
            ->where('estudiante_id', $estudianteId)
            ->whereIn('eval_bim_componente_id', $activos->pluck('id'))
            ->get()
            ->keyBy('eval_bim_componente_id');

        foreach ($activos as $componente) {
            $codigo = $componente->codigo;
            $peso = (float) $componente->peso;
            $valor = null;
            $pendiente = false;

            switch ($componente->tipo) {
                case EvalBimComponenteTipo::PromedioCriterios:
                    $res = $this->promedioCriteriosService->calcularParaEstudiante($aula, $estudianteId);
                    $valor = $res['valor'];
                    $pendiente = $res['pendiente'];
                    $promedioCriterios = $valor;
                    break;

                case EvalBimComponenteTipo::Oral:
                    $valor = $this->notaScalarRegistrada($notasScalar->get($componente->id)?->nota);
                    $pendiente = $valor === null;
                    $oral = $valor;
                    break;

                case EvalBimComponenteTipo::PromedioEta:
                    if ($etaEstudiante['pendiente_bloque']) {
                        $pendiente = true;
                        $valor = null;
                    } else {
                        $valor = $etaEstudiante['valor'];
                    }
                    $promedioEta = $valor;
                    $detalle['eta_participantes'] = $etaAula['participantes_ids'];
                    $detalle['eta_pesos_efectivos'] = $etaAula['pesos_efectivos'];
                    break;

                case EvalBimComponenteTipo::ExamenBimestral:
                    $valor = $this->notaScalarRegistrada($notasScalar->get($componente->id)?->nota);
                    $pendiente = $valor === null;
                    $examen = $valor;
                    break;

                case EvalBimComponenteTipo::Personalizado:
                    $valor = $this->notaScalarRegistrada($notasScalar->get($componente->id)?->nota);
                    $pendiente = $valor === null;
                    break;
            }

            if ($pendiente) {
                $pendientes[] = $codigo;
            } else {
                $valoresPorCodigo[$codigo] = $valor;
            }

            $detalle['componentes'][] = [
                'codigo' => $codigo,
                'tipo' => $componente->tipo->value,
                'peso' => $peso,
                'valor' => $valor,
                'pendiente' => $pendiente,
            ];
        }

        if ($pendientes !== []) {
            return new NivelLogroBimestralResultado(
                estadoCalculo: EvalBimEstadoCalculo::Pendiente,
                nivelLogroNumerico: null,
                nivelLogroLiteral: null,
                promedioCriterios: $promedioCriterios,
                oral: $oral,
                promedioEta: $promedioEta,
                examenBimestral: $examen,
                detalle: $detalle,
                pendientes: $pendientes,
            );
        }

        $nivelNumerico = $this->calcularNivelPonderado($activos, $valoresPorCodigo);
        $literal = $this->escalaLogroService->literalDesdeNumerico($nivelNumerico);

        return new NivelLogroBimestralResultado(
            estadoCalculo: EvalBimEstadoCalculo::Completo,
            nivelLogroNumerico: $nivelNumerico,
            nivelLogroLiteral: $literal,
            promedioCriterios: $promedioCriterios,
            oral: $oral,
            promedioEta: $promedioEta,
            examenBimestral: $examen,
            detalle: array_merge($detalle, [
                'conclusion_descriptiva' => $conclusionDescriptiva,
            ]),
            pendientes: [],
        );
    }

    private function notaScalarRegistrada(mixed $nota): ?float
    {
        if ($nota === null) {
            return null;
        }

        $valor = (float) $nota;

        return $valor >= 0 && $valor <= 20 ? $valor : null;
    }

    /**
     * @param  Collection<int, EvalBimComponente>  $activos
     * @param  array<string, float|null>  $valoresPorCodigo
     */
    private function calcularNivelPonderado(Collection $activos, array $valoresPorCodigo): float
    {
        $suma = 0.0;
        foreach ($activos as $componente) {
            $valor = $valoresPorCodigo[$componente->codigo] ?? null;
            if ($valor === null) {
                continue;
            }
            $suma += $valor * ((float) $componente->peso / 100);
        }

        return round($suma, 2);
    }
}
