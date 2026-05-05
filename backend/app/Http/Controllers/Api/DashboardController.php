<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalEstudiantes = Estudiante::query()->count();

        $ultimoIdPorEstudiante = DB::table('indices_riesgo')
            ->selectRaw('max(id) as id')
            ->groupBy('estudiante_id')
            ->pluck('id');

        $riesgosPorNivel = [
            'alto' => 0,
            'medio' => 0,
            'bajo' => 0,
        ];

        if ($ultimoIdPorEstudiante->isNotEmpty()) {
            $niveles = IndiceRiesgo::query()
                ->whereIn('id', $ultimoIdPorEstudiante)
                ->pluck('nivel');

            foreach ($niveles as $nivel) {
                $clave = match ($nivel) {
                    'Alto' => 'alto',
                    'Medio' => 'medio',
                    'Bajo' => 'bajo',
                    default => null,
                };
                if ($clave !== null) {
                    $riesgosPorNivel[$clave]++;
                }
            }
        }

        $conteosAlerta = Alerta::query()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $alertasPorEstado = [
            'pendiente' => (int) ($conteosAlerta['pendiente'] ?? 0),
            'en_atencion' => (int) ($conteosAlerta['en_atencion'] ?? 0),
            'cerrada' => (int) ($conteosAlerta['cerrada'] ?? 0),
        ];

        $ultimosRiesgos = IndiceRiesgo::query()
            ->with(['estudiante:id,codigo,nombres,apellidos'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function (IndiceRiesgo $indice): array {
                $est = $indice->estudiante;
                $nombreEstudiante = $est !== null
                    ? trim((string) ($est->nombres ?? '').' '.(string) ($est->apellidos ?? ''))
                    : '';

                return [
                    'id' => $indice->id,
                    'estudiante_id' => $indice->estudiante_id,
                    'estudiante' => $nombreEstudiante,
                    'codigo' => $est?->codigo ?? '',
                    'indice' => (float) $indice->indice,
                    'nivel' => match ($indice->nivel) {
                        'Alto' => 'alto',
                        'Medio' => 'medio',
                        'Bajo' => 'bajo',
                        default => strtolower((string) $indice->nivel),
                    },
                    'fecha' => $indice->created_at?->toIso8601String(),
                    'anio_escolar' => $indice->anio_escolar,
                    'bimestre' => $indice->bimestre,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'total_estudiantes' => $totalEstudiantes,
            'riesgos_por_nivel' => $riesgosPorNivel,
            'alertas_por_estado' => $alertasPorEstado,
            'ultimos_riesgos' => $ultimosRiesgos,
        ]);
    }
}
