<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Services\CompletitudDatosService;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsicologoTutorSeguimientoController extends Controller
{
    public function __construct(private readonly CompletitudDatosService $completitudDatosService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Estudiante::query()
            ->where('estudiantes.sede', SedeOperativa::CHILCA);

        $this->aplicarFiltrosBase($query, $request);
        $this->aplicarFiltrosDeSenales($query, $request);
        $this->seleccionarMetricas($query);

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        $paginated = $query->paginate($perPage);

        $data = collect($paginated->items())->map(function (Estudiante $estudiante): array {
            return [
                'estudiante_id' => $estudiante->id,
                'estudiante' => trim("{$estudiante->nombres} {$estudiante->apellidos}"),
                'grado' => (string) $estudiante->grado,
                'seccion' => (string) $estudiante->seccion,
                'ultimo_indice' => $estudiante->ultimo_indice !== null ? (float) $estudiante->ultimo_indice : null,
                'ultimo_nivel' => $estudiante->ultimo_nivel,
                'fecha_ultimo_riesgo' => $estudiante->fecha_ultimo_riesgo,
                'reportes_conductuales_activos' => (int) $estudiante->reportes_conductuales_activos,
                'alertas_activas' => (int) $estudiante->alertas_activas,
                'semaforo_completitud' => $this->semaforoCompletitud($estudiante),
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    private function aplicarFiltrosBase($query, Request $request): void
    {
        if ($request->filled('anio_escolar')) {
            $query->where('estudiantes.anio_escolar', (string) $request->query('anio_escolar'));
        }

        if ($request->filled('nivel')) {
            $query->where('estudiantes.nivel', (string) $request->query('nivel'));
        }

        if ($request->filled('grado')) {
            $query->where('estudiantes.grado', (string) $request->query('grado'));
        }

        if ($request->filled('seccion')) {
            $query->where('estudiantes.seccion', (string) $request->query('seccion'));
        }
    }

    private function aplicarFiltrosDeSenales($query, Request $request): void
    {
        $query->where(function ($q): void {
            $q->whereExists($this->subqueryExisteIndiceRiesgo())
                ->orWhereExists($this->subqueryExisteReporteActivo())
                ->orWhereExists($this->subqueryExisteAlertaActiva());
        });

        if ($request->filled('nivel_riesgo')) {
            $query->where('ultimo_ir.nivel', (string) $request->query('nivel_riesgo'));
        }

        if ($request->boolean('con_reportes_activos')) {
            $query->whereExists($this->subqueryExisteReporteActivo());
        }

        if ($request->boolean('con_alertas_activas')) {
            $query->whereExists($this->subqueryExisteAlertaActiva());
        }
    }

    /**
     * @return \Closure
     */
    private function subqueryExisteIndiceRiesgo(): callable
    {
        return function ($sub): void {
            $sub->selectRaw('1')
                ->from('indices_riesgo')
                ->whereColumn('indices_riesgo.estudiante_id', 'estudiantes.id');
        };
    }

    /**
     * @return \Closure
     */
    private function subqueryExisteReporteActivo(): callable
    {
        return function ($sub): void {
            $sub->selectRaw('1')
                ->from('reportes_conductuales')
                ->whereColumn('reportes_conductuales.estudiante_id', 'estudiantes.id')
                ->where('reportes_conductuales.estado', 'activo');
        };
    }

    /**
     * @return \Closure
     */
    private function subqueryExisteAlertaActiva(): callable
    {
        return function ($sub): void {
            $sub->selectRaw('1')
                ->from('alertas')
                ->whereColumn('alertas.estudiante_id', 'estudiantes.id')
                ->whereIn('alertas.estado', ['pendiente', 'en_atencion']);
        };
    }

    private function seleccionarMetricas($query): void
    {
        $ultimoIndiceSub = DB::table('indices_riesgo')
            ->select([
                'indices_riesgo.estudiante_id',
                'indices_riesgo.indice',
                'indices_riesgo.nivel',
                'indices_riesgo.created_at',
            ])
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estudiante_id ORDER BY created_at DESC, id DESC) as rn');

        $query->leftJoinSub($ultimoIndiceSub, 'ultimo_ir', function ($join): void {
            $join->on('ultimo_ir.estudiante_id', '=', 'estudiantes.id')
                ->where('ultimo_ir.rn', '=', 1);
        });

        $query->select([
            'estudiantes.id',
            'estudiantes.nombres',
            'estudiantes.apellidos',
            'estudiantes.grado',
            'estudiantes.seccion',
            'estudiantes.nivel',
            'estudiantes.anio_escolar',
            'ultimo_ir.indice as ultimo_indice',
            'ultimo_ir.nivel as ultimo_nivel',
            DB::raw('DATE(ultimo_ir.created_at) as fecha_ultimo_riesgo'),
        ]);

        $query->selectSub(function ($sub): void {
            $sub->selectRaw('COUNT(*)')
                ->from('reportes_conductuales')
                ->whereColumn('reportes_conductuales.estudiante_id', 'estudiantes.id')
                ->where('reportes_conductuales.estado', 'activo');
        }, 'reportes_conductuales_activos');

        $query->selectSub(function ($sub): void {
            $sub->selectRaw('COUNT(*)')
                ->from('alertas')
                ->whereColumn('alertas.estudiante_id', 'estudiantes.id')
                ->whereIn('alertas.estado', ['pendiente', 'en_atencion']);
        }, 'alertas_activas');
    }

    private function semaforoCompletitud(Estudiante $estudiante): string
    {
        try {
            $resultado = $this->completitudDatosService->evaluar(
                $estudiante,
                (string) $estudiante->anio_escolar,
            );

            return $resultado['color'] ?? 'rojo';
        } catch (\Throwable) {
            return 'rojo';
        }
    }
}
