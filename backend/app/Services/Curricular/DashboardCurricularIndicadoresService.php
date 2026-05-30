<?php

namespace App\Services\Curricular;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\MallaCurso;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Builder;

class DashboardCurricularIndicadoresService
{
    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     * @return array{
     *   total_estudiantes_activos: int,
     *   registros_asistencia_diaria: int,
     *   resultados_bimestrales: int,
     *   cursos_malla_activos: int,
     *   asignaciones_docente_activas: int,
     * }
     */
    public function calcular(array $filtros): array
    {
        return [
            'total_estudiantes_activos' => $this->contarEstudiantesActivos($filtros),
            'registros_asistencia_diaria' => $this->contarAsistenciasDiarias($filtros),
            'resultados_bimestrales' => $this->contarResultadosBimestrales($filtros),
            'cursos_malla_activos' => $this->contarCursosMallaActivos($filtros),
            'asignaciones_docente_activas' => $this->contarAsignacionesDocente($filtros),
        ];
    }

    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     */
    private function contarEstudiantesActivos(array $filtros): int
    {
        return $this->aplicarFiltrosEstudiante(Estudiante::query()->where('activo', true), $filtros)->count();
    }

    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     */
    private function contarAsistenciasDiarias(array $filtros): int
    {
        $query = AsistenciaDiaria::query();

        if (($filtros['sede'] ?? '') !== '') {
            $query->where('sede', $filtros['sede']);
        }
        if (($filtros['nivel'] ?? '') !== '') {
            $query->where('nivel', $filtros['nivel']);
        }
        if (($filtros['grado'] ?? '') !== '') {
            $query->where('grado', $filtros['grado']);
        }
        if (($filtros['seccion'] ?? '') !== '') {
            $query->where('seccion', $filtros['seccion']);
        }

        return $query->count();
    }

    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     */
    private function contarResultadosBimestrales(array $filtros): int
    {
        $query = EvalBimResultado::query();

        if (($filtros['sede'] ?? '') !== '') {
            $query->where('sede', $filtros['sede']);
        }
        if (($filtros['grado'] ?? '') !== '') {
            $query->where('grado', $filtros['grado']);
        }
        if (($filtros['seccion'] ?? '') !== '') {
            $query->where('seccion', $filtros['seccion']);
        }

        if (($filtros['nivel'] ?? '') !== '') {
            $query->whereHas('mallaCurso.mallaCurricular', fn (Builder $q) => $q->where('nivel', $filtros['nivel']));
        }

        return $query->count();
    }

    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     */
    private function contarCursosMallaActivos(array $filtros): int
    {
        $query = MallaCurso::query()->where('activo', true);

        if (($filtros['nivel'] ?? '') !== '' || ($filtros['grado'] ?? '') !== '') {
            $query->whereHas('mallaCurricular', function (Builder $q) use ($filtros): void {
                if (($filtros['nivel'] ?? '') !== '') {
                    $q->where('nivel', $filtros['nivel']);
                }
                if (($filtros['grado'] ?? '') !== '') {
                    $gradoCurricular = (new EquivalenciaGradoService)->aCurricular(
                        (string) ($filtros['nivel'] ?? ''),
                        (string) $filtros['grado'],
                    );
                    if ($gradoCurricular !== null) {
                        $q->where('grado', $gradoCurricular);
                    }
                }
            });
        }

        return $query->count();
    }

    /**
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     */
    private function contarAsignacionesDocente(array $filtros): int
    {
        $query = DocenteCursoAula::query()->where('activo', true);

        if (($filtros['sede'] ?? '') !== '') {
            $query->where('sede', $filtros['sede']);
        }
        if (($filtros['nivel'] ?? '') !== '') {
            $query->where('nivel', $filtros['nivel']);
        }
        if (($filtros['seccion'] ?? '') !== '') {
            $query->where('seccion', $filtros['seccion']);
        }
        if (($filtros['grado'] ?? '') !== '') {
            $gradoCurricular = (new EquivalenciaGradoService)->aCurricular(
                (string) ($filtros['nivel'] ?? ''),
                (string) $filtros['grado'],
            );
            if ($gradoCurricular !== null) {
                $query->where('grado', $gradoCurricular);
            }
        }

        return $query->count();
    }

    /**
     * @param  Builder<Estudiante>  $query
     * @param  array{sede:?string,nivel:?string,grado:?string,seccion:?string}  $filtros
     * @return Builder<Estudiante>
     */
    private function aplicarFiltrosEstudiante(Builder $query, array $filtros): Builder
    {
        if (($filtros['sede'] ?? '') !== '') {
            $query->where('sede', $filtros['sede']);
        }
        if (($filtros['nivel'] ?? '') !== '') {
            $query->where('nivel', $filtros['nivel']);
        }
        if (($filtros['grado'] ?? '') !== '') {
            $query->where('grado', $filtros['grado']);
        }
        if (($filtros['seccion'] ?? '') !== '') {
            $query->where('seccion', $filtros['seccion']);
        }

        return $query;
    }
}
