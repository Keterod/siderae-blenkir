<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use Illuminate\Support\Facades\DB;

class EvaluacionBimestralConfiguracionService
{
    /** @var list<array{codigo: string, tipo: EvalBimComponenteTipo, nombre: string, peso: float, orden: int}> */
    private const COMPONENTES_SISTEMA = [
        ['codigo' => 'promedio_criterios', 'tipo' => EvalBimComponenteTipo::PromedioCriterios, 'nombre' => 'Promedio de criterios', 'peso' => 25.00, 'orden' => 1],
        ['codigo' => 'oral', 'tipo' => EvalBimComponenteTipo::Oral, 'nombre' => 'Oral', 'peso' => 25.00, 'orden' => 2],
        ['codigo' => 'promedio_eta', 'tipo' => EvalBimComponenteTipo::PromedioEta, 'nombre' => 'Promedio ETA', 'peso' => 25.00, 'orden' => 3],
        ['codigo' => 'examen_bimestral', 'tipo' => EvalBimComponenteTipo::ExamenBimestral, 'nombre' => 'Examen bimestral', 'peso' => 25.00, 'orden' => 4],
    ];

    /** @var list<array{nombre: string, peso_interno: float, orden: int}> */
    private const ETAS_DEFECTO = [
        ['nombre' => 'ETA 1', 'peso_interno' => 33.33, 'orden' => 1],
        ['nombre' => 'ETA 2', 'peso_interno' => 33.33, 'orden' => 2],
        ['nombre' => 'ETA 3', 'peso_interno' => 33.34, 'orden' => 3],
    ];

    public function asegurarConfiguracionPorDefecto(int $mallaCursoId, int $periodoAcademicoId): void
    {
        DB::transaction(function () use ($mallaCursoId, $periodoAcademicoId): void {
            foreach (self::COMPONENTES_SISTEMA as $def) {
                EvalBimComponente::query()->firstOrCreate(
                    [
                        'malla_curso_id' => $mallaCursoId,
                        'periodo_academico_id' => $periodoAcademicoId,
                        'codigo' => $def['codigo'],
                    ],
                    [
                        'tipo' => $def['tipo'],
                        'nombre' => $def['nombre'],
                        'peso' => $def['peso'],
                        'orden' => $def['orden'],
                        'activo' => true,
                    ]
                );
            }

            $componenteEta = EvalBimComponente::query()
                ->where('malla_curso_id', $mallaCursoId)
                ->where('periodo_academico_id', $periodoAcademicoId)
                ->where('codigo', 'promedio_eta')
                ->firstOrFail();

            foreach (self::ETAS_DEFECTO as $def) {
                EvalBimEtaItem::query()->firstOrCreate(
                    [
                        'eval_bim_componente_id' => $componenteEta->id,
                        'nombre' => $def['nombre'],
                    ],
                    [
                        'peso_interno' => $def['peso_interno'],
                        'orden' => $def['orden'],
                        'activo' => true,
                    ]
                );
            }
        });
    }

    public function crearComponentePersonalizado(
        int $mallaCursoId,
        int $periodoAcademicoId,
        string $nombre,
        ?PesosComponentesService $pesosService = null,
    ): EvalBimComponente {
        $this->asegurarConfiguracionPorDefecto($mallaCursoId, $periodoAcademicoId);

        $codigo = 'personalizado_'.uniqid();
        $maxOrden = (int) EvalBimComponente::query()
            ->where('malla_curso_id', $mallaCursoId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->max('orden');

        $componente = EvalBimComponente::query()->create([
            'malla_curso_id' => $mallaCursoId,
            'periodo_academico_id' => $periodoAcademicoId,
            'tipo' => EvalBimComponenteTipo::Personalizado,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'peso' => 0,
            'orden' => $maxOrden + 1,
            'activo' => true,
        ]);

        ($pesosService ?? new PesosComponentesService)->redistribuirTrasAgregar(
            $componente,
            $mallaCursoId,
            $periodoAcademicoId,
        );

        return $componente->refresh();
    }
}
