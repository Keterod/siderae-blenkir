<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardInstitucionalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estudiantesQuery = Estudiante::query()
            ->where('sede', SedeOperativa::CHILCA);

        if ($request->filled('grado')) {
            $estudiantesQuery->where('grado', (string) $request->query('grado'));
        }

        if ($request->filled('seccion')) {
            $estudiantesQuery->where('seccion', (string) $request->query('seccion'));
        }

        $estudiantes = $estudiantesQuery
            ->select(['id', 'nombres', 'apellidos', 'grado', 'seccion'])
            ->get()
            ->keyBy('id');

        $estudianteIds = $estudiantes->pluck('id');

        $ultimosIndicesPorEstudiante = $this->ultimosIndicesPorEstudiante(
            $estudianteIds,
            $request->query('anio_escolar'),
            $request->query('bimestre'),
        );

        $resumen = $this->construirResumen($estudiantes, $ultimosIndicesPorEstudiante);
        $completitud = $this->construirCompletitud($estudiantes, $ultimosIndicesPorEstudiante);
        $porGradoSeccion = $this->construirPorGradoSeccion($estudiantes, $ultimosIndicesPorEstudiante);
        $ultimosRiesgos = $this->construirUltimosRiesgos(
            $estudianteIds,
            $request->query('anio_escolar'),
            $request->query('bimestre'),
        );

        return response()->json([
            'resumen' => $resumen,
            'completitud' => $completitud,
            'por_grado_seccion' => $porGradoSeccion,
            'ultimos_riesgos' => $ultimosRiesgos,
        ]);
    }

    /**
     * Devuelve los índices de riesgo más recientes por estudiante, filtrados opcionalmente
     * por año escolar y bimestre. No recalcula riesgo.
     *
     * @param  \Illuminate\Support\Collection<int, int>  $estudianteIds
     * @return \Illuminate\Support\Collection<int, IndiceRiesgo>
     */
    private function ultimosIndicesPorEstudiante(
        $estudianteIds,
        ?string $anioEscolar,
        ?string $bimestre,
    ) {
        if ($estudianteIds->isEmpty()) {
            return collect();
        }

        $subQuery = DB::table('indices_riesgo')
            ->whereIn('estudiante_id', $estudianteIds)
            ->when($anioEscolar !== null && $anioEscolar !== '', static function ($q) use ($anioEscolar): void {
                $q->where('anio_escolar', (string) $anioEscolar);
            })
            ->when($bimestre !== null && $bimestre !== '', static function ($q) use ($bimestre): void {
                $q->where('bimestre', (string) $bimestre);
            })
            ->selectRaw('MAX(id) as id')
            ->groupBy('estudiante_id');

        return IndiceRiesgo::query()
            ->whereIn('id', $subQuery)
            ->get()
            ->keyBy('estudiante_id');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Estudiante>  $estudiantes
     * @param  \Illuminate\Support\Collection<int, IndiceRiesgo>  $ultimosIndices
     * @return array<string, mixed>
     */
    private function construirResumen($estudiantes, $ultimosIndices): array
    {
        $totalEstudiantes = $estudiantes->count();
        $conRiesgo = 0;
        $bajo = 0;
        $medio = 0;
        $alto = 0;

        foreach ($estudiantes as $estudiante) {
            $indice = $ultimosIndices->get($estudiante->id);

            if ($indice === null) {
                continue;
            }

            $conRiesgo++;

            match ($indice->nivel) {
                'Bajo' => $bajo++,
                'Medio' => $medio++,
                'Alto' => $alto++,
                default => null,
            };
        }

        return [
            'total_estudiantes' => $totalEstudiantes,
            'con_riesgo' => $conRiesgo,
            'riesgo_bajo' => $bajo,
            'riesgo_medio' => $medio,
            'riesgo_alto' => $alto,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Estudiante>  $estudiantes
     * @param  \Illuminate\Support\Collection<int, IndiceRiesgo>  $ultimosIndices
     * @return array<string, mixed>
     */
    private function construirCompletitud($estudiantes, $ultimosIndices): array
    {
        $total = $estudiantes->count();
        $conRiesgo = 0;

        foreach ($estudiantes as $estudiante) {
            if ($ultimosIndices->has($estudiante->id)) {
                $conRiesgo++;
            }
        }

        $sinRiesgo = $total - $conRiesgo;
        $porcentaje = $total > 0 ? round(($conRiesgo / $total) * 100, 2) : 0.0;

        return [
            'con_riesgo' => $conRiesgo,
            'sin_riesgo' => $sinRiesgo,
            'porcentaje_con_riesgo' => $porcentaje,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Estudiante>  $estudiantes
     * @param  \Illuminate\Support\Collection<int, IndiceRiesgo>  $ultimosIndices
     * @return array<int, array<string, mixed>>
     */
    private function construirPorGradoSeccion($estudiantes, $ultimosIndices): array
    {
        $grupos = $estudiantes->groupBy(static function (Estudiante $estudiante): string {
            return (string) $estudiante->grado . '|' . (string) $estudiante->seccion;
        });

        return $grupos->map(static function ($grupoEstudiantes) use ($ultimosIndices): array {
            $total = $grupoEstudiantes->count();
            $bajo = 0;
            $medio = 0;
            $alto = 0;

            foreach ($grupoEstudiantes as $estudiante) {
                $indice = $ultimosIndices->get($estudiante->id);

                if ($indice === null) {
                    continue;
                }

                match ($indice->nivel) {
                    'Bajo' => $bajo++,
                    'Medio' => $medio++,
                    'Alto' => $alto++,
                    default => null,
                };
            }

            return [
                'grado' => (string) $grupoEstudiantes->first()->grado,
                'seccion' => (string) $grupoEstudiantes->first()->seccion,
                'total_estudiantes' => $total,
                'riesgo_bajo' => $bajo,
                'riesgo_medio' => $medio,
                'riesgo_alto' => $alto,
            ];
        })->values()->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $estudianteIds
     * @return array<int, array<string, mixed>>
     */
    private function construirUltimosRiesgos(
        $estudianteIds,
        ?string $anioEscolar,
        ?string $bimestre,
    ): array {
        if ($estudianteIds->isEmpty()) {
            return [];
        }

        return IndiceRiesgo::query()
            ->with('estudiante:id,nombres,apellidos,grado,seccion')
            ->whereIn('estudiante_id', $estudianteIds)
            ->when($anioEscolar !== null && $anioEscolar !== '', static function ($q) use ($anioEscolar): void {
                $q->where('anio_escolar', (string) $anioEscolar);
            })
            ->when($bimestre !== null && $bimestre !== '', static function ($q) use ($bimestre): void {
                $q->where('bimestre', (string) $bimestre);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(static function (IndiceRiesgo $indice): array {
                $estudiante = $indice->estudiante;

                return [
                    'estudiante_id' => $indice->estudiante_id,
                    'estudiante' => $estudiante !== null
                        ? trim("{$estudiante->nombres} {$estudiante->apellidos}")
                        : '',
                    'grado' => (string) ($estudiante?->grado ?? ''),
                    'seccion' => (string) ($estudiante?->seccion ?? ''),
                    'anio_escolar' => $indice->anio_escolar,
                    'bimestre' => $indice->bimestre,
                    'indice' => (float) $indice->indice,
                    'nivel' => $indice->nivel,
                    'fecha' => $indice->created_at?->toDateString(),
                ];
            })
            ->all();
    }
}
