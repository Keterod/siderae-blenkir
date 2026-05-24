<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Models\Curricular\DocenteCursoAula;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
