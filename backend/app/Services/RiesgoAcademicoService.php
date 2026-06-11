<?php

namespace App\Services;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\NotaSemanal;
use App\Models\Alerta;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\ReporteConductual;
use App\Models\VariableSocioeconomica;
use App\Services\Curricular\AsistenciaDiariaResumenService;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\EquivalenciaGradoService;
use Illuminate\Support\Facades\Log;

class RiesgoAcademicoService
{
    public const MENSAJE_INICIAL_NO_DISPONIBLE = 'Riesgo académico no disponible para Inicial en esta versión.';

    public function __construct(
        private readonly AsistenciaDiariaResumenService $asistenciaDiariaResumenService = new AsistenciaDiariaResumenService,
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
    ) {}

    /**
     * @return array{ok: bool, errors?: array<string, array<int, string>>, status?: string, message?: string}
     */
    public function validarDatosMinimos(Estudiante $estudiante, string $anio): array
    {
        if ($estudiante->nivel === CatalogoNivelGrado::NIVEL_INICIAL) {
            return [
                'ok' => false,
                'status' => 'no_disponible',
                'message' => self::MENSAJE_INICIAL_NO_DISPONIBLE,
                'errors' => [
                    'nivel' => [self::MENSAJE_INICIAL_NO_DISPONIBLE],
                ],
            ];
        }

        $tieneAcademicos = $this->tieneDatosAcademicosCurriculares($estudiante, $anio);

        $tieneAsistencias = AsistenciaDiaria::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->exists();

        $tieneVariables = VariableSocioeconomica::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->exists();

        if ($tieneAcademicos && $tieneAsistencias && $tieneVariables) {
            return ['ok' => true];
        }

        $mensajes = [];

        if (! $tieneAcademicos) {
            $mensajes['datos_academicos_curriculares'] = [
                'Se requiere evaluación bimestral o notas semanales curriculares para el año escolar del estudiante.',
            ];
        }

        if (! $tieneAsistencias) {
            $mensajes['asistencias_curriculares'] = [
                'Se requiere al menos un registro de asistencia curricular diaria para el año escolar del estudiante.',
            ];
        }

        if (! $tieneVariables) {
            $mensajes['variables_socioeconomicas'] = [
                'Se requieren variables socioeconómicas para el año escolar del estudiante.',
            ];
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
                'status' => $validacion['status'] ?? 'omitido',
                'errors' => $validacion['errors'] ?? [],
                'message' => $validacion['message'] ?? null,
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
        $promedioNotas = $this->resolverPromedioNotas($estudiante, $anio);
        if ($promedioNotas === null) {
            throw new \RuntimeException('No hay datos académicos curriculares para construir el payload de riesgo.');
        }

        $resumenAsistencia = $this->asistenciaDiariaResumenService->construirPorEstudiante([
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => $anio,
        ]);

        $porcentajeAsistencia = (float) ($resumenAsistencia['totales']['porcentaje_asistencia_efectiva'] ?? 0.0);

        $reportesCount = ReporteConductual::query()
            ->where('estudiante_id', $estudiante->id)
            ->activos()
            ->count();

        /** @var VariableSocioeconomica $vars */
        $vars = VariableSocioeconomica::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->firstOrFail();

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

    private function tieneDatosAcademicosCurriculares(Estudiante $estudiante, string $anio): bool
    {
        if ($this->resolverPromedioNotas($estudiante, $anio) !== null) {
            return true;
        }

        return false;
    }

    private function resolverPromedioNotas(Estudiante $estudiante, string $anio): ?float
    {
        $promedioBimestral = $this->promedioDesdeEvaluacionBimestral($estudiante, $anio);
        if ($promedioBimestral !== null) {
            return $promedioBimestral;
        }

        return $this->promedioDesdeNotasSemanales($estudiante, $anio);
    }

    private function promedioDesdeEvaluacionBimestral(Estudiante $estudiante, string $anio): ?float
    {
        $valores = $this->queryResultadosBimestralesCompletos($estudiante, $anio)
            ->pluck('nivel_logro_numerico')
            ->map(fn ($valor) => $valor !== null ? (float) $valor : null)
            ->filter(fn (?float $valor) => $valor !== null);

        if ($valores->isEmpty()) {
            return null;
        }

        return round($valores->avg(), 4);
    }

    private function promedioDesdeNotasSemanales(Estudiante $estudiante, string $anio): ?float
    {
        $valores = NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereNotNull('ce_calculado')
            ->whereHas('temaSemanal.periodoAcademico', fn ($q) => $q->where('anio_escolar', $anio))
            ->pluck('ce_calculado')
            ->map(fn ($valor) => (float) $valor);

        if ($valores->isEmpty()) {
            return null;
        }

        return round($valores->avg(), 4);
    }

  /**
     * @return \Illuminate\Database\Eloquent\Builder<EvalBimResultado>
     */
    private function queryResultadosBimestralesCompletos(Estudiante $estudiante, string $anio)
    {
        $gradoCurricular = $this->equivalenciaGradoService->aCurricular(
            (string) $estudiante->nivel,
            (string) $estudiante->grado,
        );

        $query = EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('estado_calculo', EvalBimEstadoCalculo::Completo)
            ->whereNotNull('nivel_logro_numerico')
            ->whereHas('periodoAcademico', fn ($q) => $q->where('anio_escolar', $anio))
            ->where('sede', $estudiante->sede)
            ->where('grado', $estudiante->grado)
            ->where('seccion', $estudiante->seccion);

        if ($gradoCurricular !== null && in_array($estudiante->nivel, [CatalogoNivelGrado::NIVEL_PRIMARIA, CatalogoNivelGrado::NIVEL_SECUNDARIA], true)) {
            $query->whereHas('mallaCurso.mallaCurricular', fn ($q) => $q
                ->where('nivel', $estudiante->nivel)
                ->where('grado', $gradoCurricular));
        }

        return $query;
    }
}
