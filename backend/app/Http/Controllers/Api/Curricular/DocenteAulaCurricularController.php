<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Models\Curricular\DocenteCursoAula;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocenteAulaCurricularController extends Controller
{
    public function aulasCursos(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DocenteCursoAula::query()
            ->where('user_id', $user->id)
            ->where('activo', true)
            ->with([
                'mallaCurso.area',
                'mallaCurso.cursoCatalogo',
                'mallaCurso.mallaCurricular',
            ])
            ->orderBy('anio_escolar')
            ->orderBy('grado')
            ->orderBy('seccion');

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }

        return response()->json($query->get());
    }

    /**
     * Contextos deduplicados (aula + curso de malla) para el modo consulta global de notas.
     */
    public function contextosAulaConsulta(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->can('ver_notas_academicas')) {
            return response()->json(['message' => 'Permiso denegado.'], 403);
        }

        if (! NotaSemanalController::usuarioPuedeConsultaGlobalNotas($user)) {
            return response()->json(['message' => 'No autorizado para listar contextos de consulta global.'], 403);
        }

        $query = DocenteCursoAula::query()
            ->where('activo', true)
            ->with([
                'mallaCurso.area',
                'mallaCurso.cursoCatalogo',
            ]);

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }

        foreach (['nivel', 'sede', 'grado', 'seccion'] as $campo) {
            if ($request->filled($campo)) {
                $query->where($campo, $request->query($campo));
            }
        }

        if ($request->filled('area_id')) {
            $query->whereHas('mallaCurso', function ($q) use ($request): void {
                $q->where('area_id', (int) $request->query('area_id'));
            });
        }

        if ($request->filled('malla_curso_id')) {
            $query->where('malla_curso_id', (int) $request->query('malla_curso_id'));
        }

        /** @var \Illuminate\Support\Collection<int, \App\Models\Curricular\DocenteCursoAula> $asignaciones */
        $asignaciones = $query->orderBy('anio_escolar')
            ->orderBy('sede')
            ->orderBy('nivel')
            ->orderBy('grado')
            ->orderBy('seccion')
            ->get();

        $vistos = [];
        $resultado = [];

        foreach ($asignaciones as $a) {
            $malla = $a->mallaCurso;
            if ($malla === null) {
                continue;
            }

            $clave = implode('|', [
                $a->anio_escolar,
                $a->nivel,
                $a->sede,
                $a->grado,
                $a->seccion,
                (string) $malla->id,
            ]);

            if (isset($vistos[$clave])) {
                continue;
            }

            $vistos[$clave] = true;

            $resultado[] = [
                'clave' => $clave,
                'asignacion_docente_id' => $a->id,
                'anio_escolar' => $a->anio_escolar,
                'nivel' => $a->nivel,
                'sede' => $a->sede,
                'grado' => $a->grado,
                'seccion' => $a->seccion,
                'area_id' => $malla->area_id,
                'area_nombre' => $malla->area?->nombre,
                'malla_curso_id' => $malla->id,
                'curso_nombre' => $malla->cursoCatalogo?->nombre ?? '',
                'titulo_opcion' => Str::substr(
                    trim(
                        "{$a->grado} {$a->seccion} · " . ($malla->area?->nombre ? "{$malla->area->nombre} · " : '') . ($malla->cursoCatalogo?->nombre ?? ''),
                    ),
                    0,
                    120,
                ),
            ];
        }

        return response()->json($resultado);
    }
}
