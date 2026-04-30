<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\Nota;
use App\Models\ReporteConductual;
use App\Models\VariableSocioeconomica;
use App\Services\MlRiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcesarRiesgoController extends Controller
{
    public function store(Request $request, Estudiante $estudiante, MlRiskService $mlRiskService): JsonResponse
    {
        $validated = $request->validate([
            'bimestre' => ['sometimes', 'string', 'in:1,2,3,4'],
        ]);

        $bimestre = $validated['bimestre'] ?? '1';
        $anio = $estudiante->anio_escolar;

        $faltantes = $this->validarDatosMinimos($estudiante, $anio);

        if ($faltantes !== null) {
            return response()->json([
                'message' => 'Faltan datos mínimos para calcular el riesgo.',
                'errors' => $faltantes,
            ], 422);
        }

        $payload = $this->construirPayload($estudiante, $anio);

        try {
            $respuestaMl = $mlRiskService->predict($payload);
        } catch (\Throwable $e) {
            Log::warning('Error al invocar ML predict', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        $indice = (float) $respuestaMl['indice_riesgo'];
        $indice = max(0.0, min(1.0, $indice));

        $nivel = IndiceRiesgo::clasificarNivelDesdeIndice($indice);

        $modelosScores = null;

        if (isset($respuestaMl['modelos']) && is_array($respuestaMl['modelos'])) {
            $modelosScores = $respuestaMl['modelos'];
        } elseif (isset($respuestaMl['modelos_scores']) && is_array($respuestaMl['modelos_scores'])) {
            $modelosScores = $respuestaMl['modelos_scores'];
        }

        $registro = IndiceRiesgo::create([
            'estudiante_id' => $estudiante->id,
            'indice' => $indice,
            'nivel' => $nivel,
            'anio_escolar' => $anio,
            'bimestre' => $bimestre,
            'variables_utilizadas' => $payload,
            'modelos_scores' => $modelosScores,
        ]);

        $alertaGenerada = Alerta::crearPorRiesgoAltoSiAplica($estudiante, $registro);

        return response()->json(array_merge(
            $registro->toArray(),
            [
                'alerta_generada' => $alertaGenerada ? $alertaGenerada->toArray() : null,
            ]
        ), 201);
    }

    /**
     * @return array<string, array<int, string>>|null
     */
    private function validarDatosMinimos(Estudiante $estudiante, string $anio): ?array
    {
        $tieneNotas = Nota::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->exists();

        $tieneAsistencias = Asistencia::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->exists();

        $tieneVariables = VariableSocioeconomica::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->exists();

        if ($tieneNotas && $tieneAsistencias && $tieneVariables) {
            return null;
        }

        $mensajes = [];

        if (! $tieneNotas) {
            $mensajes['notas'] = ['Se requiere al menos una nota para el año escolar del estudiante.'];
        }

        if (! $tieneAsistencias) {
            $mensajes['asistencias'] = ['Se requiere al menos un registro de asistencia para el año escolar del estudiante.'];
        }

        if (! $tieneVariables) {
            $mensajes['variables_socioeconomicas'] = ['Se requieren variables socioeconómicas para el año escolar del estudiante.'];
        }

        return $mensajes;
    }

    /**
     * @return array<string, mixed>
     */
    private function construirPayload(Estudiante $estudiante, string $anio): array
    {
        $notas = Nota::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->get();

        $promedioNotas = (float) $notas->avg('nota');

        $asistencias = Asistencia::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->get();

        $totalAsis = $asistencias->count();
        $faltas = $asistencias->where('estado', 'falta')->count();
        $porcentajeAsistencia = $totalAsis > 0
            ? (($totalAsis - $faltas) / $totalAsis) * 100.0
            : 0.0;

        $reportesCount = ReporteConductual::query()
            ->where('estudiante_id', $estudiante->id)
            ->count();

        /** @var VariableSocioeconomica $vars */
        $vars = VariableSocioeconomica::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->first();

        $distancia = $vars->distancia_colegio_km !== null
            ? (float) $vars->distancia_colegio_km
            : 0.0;

        return [
            'promedio_notas' => round($promedioNotas, 4),
            'porcentaje_asistencia' => round($porcentajeAsistencia, 4),
            'reportes_conductuales' => $reportesCount,
            'fast_test_puntaje' => 0,
            'nivel_socioeconomico' => $vars->nivel_socioeconomico,
            'acceso_internet' => (bool) $vars->acceso_internet,
            'distancia_colegio' => $distancia,
        ];
    }
}
