<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\Nota;
use App\Models\ReporteConductual;
use App\Models\VariableSocioeconomica;
use Illuminate\Support\Facades\Log;

class RiesgoAcademicoService
{
    /**
     * @return array{ok: bool, errors?: array<string, array<int, string>>}
     */
    public function validarDatosMinimos(Estudiante $estudiante, string $anio): array
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
            return ['ok' => true];
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

        return [
            'ok' => false,
            'errors' => $mensajes,
        ];
    }

    /**
     * @return array{status: string, registro?: IndiceRiesgo, alerta_generada?: Alerta|null, errors?: array<string, array<int, string>>, message?: string}
     */
    public function procesarEstudiante(Estudiante $estudiante, string $anio, string $bimestre, MlRiskService $mlRiskService): array
    {
        $validacion = $this->validarDatosMinimos($estudiante, $anio);
        if (! $validacion['ok']) {
            return [
                'status' => 'omitido',
                'errors' => $validacion['errors'],
            ];
        }

        $payload = $this->construirPayload($estudiante, $anio);

        try {
            $respuestaMl = $mlRiskService->predict($payload);
        } catch (\Throwable $e) {
            Log::warning('Error al invocar ML predict', [
                'estudiante_id' => $estudiante->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => 'fallido',
                'message' => $e->getMessage(),
            ];
        }

        try {
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

            return [
                'status' => 'procesado',
                'registro' => $registro,
                'alerta_generada' => $alertaGenerada,
            ];
        } catch (\Throwable $e) {
            Log::warning('Error al guardar índice de riesgo', [
                'estudiante_id' => $estudiante->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => 'fallido',
                'message' => 'No se pudo persistir el cálculo de riesgo.',
            ];
        }
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
