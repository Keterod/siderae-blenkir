<?php

namespace App\Services;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\NotaSemanal;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Models\ReporteConductual;

class CompletitudDatosService
{
    /**
     * Evalúa la completitud de datos disponibles para interpretar el riesgo académico.
     *
     * @return array<string, mixed>
     */
    public function evaluar(Estudiante $estudiante, string $anioEscolar, ?string $bimestre = null): array
    {
        $tieneNotas = $this->tieneNotasCurriculares($estudiante, $anioEscolar);
        $tieneAsistencia = $this->tieneAsistenciaCurricular($estudiante, $anioEscolar);
        $tieneReportes = $this->tieneReportesConductualesActivos($estudiante);
        $tieneIndiceRiesgo = $this->tieneIndiceRiesgo($estudiante, $anioEscolar, $bimestre);

        $razones = [
            $this->razon(
                'notas_curriculares',
                $tieneNotas,
                'Se encontraron notas curriculares o evaluación bimestral.',
                'No se encontraron notas curriculares ni evaluación bimestral.'
            ),
            $this->razon(
                'asistencia_curricular',
                $tieneAsistencia,
                'Se encontró asistencia curricular.',
                'No se encontró asistencia curricular.'
            ),
            $this->razon(
                'reportes_conductuales',
                $tieneReportes,
                'Hay reportes conductuales activos.',
                'No hay reportes conductuales activos.'
            ),
            $this->razon(
                'indice_riesgo',
                $tieneIndiceRiesgo,
                'Existe un índice de riesgo registrado.',
                'No existe un índice de riesgo registrado.'
            ),
        ];

        if ($tieneNotas && $tieneAsistencia) {
            return $this->respuesta(
                'verde',
                'Datos suficientes',
                'Los datos académicos y de asistencia son suficientes para interpretar el riesgo.',
                $razones
            );
        }

        if ($tieneNotas || $tieneAsistencia || $tieneReportes || $tieneIndiceRiesgo) {
            return $this->respuesta(
                'amarillo',
                'Datos parciales',
                'Existen algunos datos, pero la interpretación del riesgo debe hacerse con advertencia.',
                $razones
            );
        }

        return $this->respuesta(
            'rojo',
            'Datos insuficientes',
            'No hay datos suficientes para interpretar el riesgo académico.',
            $razones
        );
    }

    private function tieneNotasCurriculares(Estudiante $estudiante, string $anioEscolar): bool
    {
        if ($this->tieneEvaluacionBimestral($estudiante, $anioEscolar)) {
            return true;
        }

        return $this->tieneNotasSemanales($estudiante, $anioEscolar);
    }

    private function tieneEvaluacionBimestral(Estudiante $estudiante, string $anioEscolar): bool
    {
        return EvalBimResultado::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('sede', $estudiante->sede)
            ->where('grado', $estudiante->grado)
            ->where('seccion', $estudiante->seccion)
            ->whereNotNull('nivel_logro_numerico')
            ->whereHas('periodoAcademico', fn ($q) => $q->where('anio_escolar', $anioEscolar))
            ->exists();
    }

    private function tieneNotasSemanales(Estudiante $estudiante, string $anioEscolar): bool
    {
        return NotaSemanal::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereNotNull('ce_calculado')
            ->whereHas('temaSemanal.periodoAcademico', fn ($q) => $q->where('anio_escolar', $anioEscolar))
            ->exists();
    }

    private function tieneAsistenciaCurricular(Estudiante $estudiante, string $anioEscolar): bool
    {
        return AsistenciaDiaria::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anioEscolar)
            ->exists();
    }

    private function tieneReportesConductualesActivos(Estudiante $estudiante): bool
    {
        return ReporteConductual::query()
            ->where('estudiante_id', $estudiante->id)
            ->activos()
            ->exists();
    }

    private function tieneIndiceRiesgo(Estudiante $estudiante, string $anioEscolar, ?string $bimestre): bool
    {
        $query = IndiceRiesgo::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anioEscolar);

        if ($bimestre !== null && $bimestre !== '') {
            $query->where('bimestre', $bimestre);
        }

        return $query->exists();
    }

    private function razon(string $dato, bool $presente, string $mensajePresente, string $mensajeAusente): array
    {
        return [
            'dato' => $dato,
            'presente' => $presente,
            'mensaje' => $presente ? $mensajePresente : $mensajeAusente,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $razones
     * @return array<string, mixed>
     */
    private function respuesta(string $color, string $etiqueta, string $mensaje, array $razones): array
    {
        return [
            'color' => $color,
            'etiqueta' => $etiqueta,
            'mensaje' => $mensaje,
            'razones' => $razones,
        ];
    }
}
