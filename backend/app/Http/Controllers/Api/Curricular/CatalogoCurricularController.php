<?php

namespace App\Http\Controllers\Api\Curricular;

use App\Http\Controllers\Controller;
use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Curricular\SemanaAcademica;
use App\Services\Curricular\CatalogoCompetenciaCapacidadQuery;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoCurricularController extends Controller
{
    public function __construct(
        private readonly CatalogoCompetenciaCapacidadQuery $catalogoCompetenciaQuery = new CatalogoCompetenciaCapacidadQuery,
    ) {}
    public function nivelesGrados(): JsonResponse
    {
        $niveles = [];
        foreach (CatalogoNivelGrado::nivelesCurriculares() as $nivel) {
            $niveles[] = [
                'nivel' => $nivel,
                'grados' => CatalogoNivelGrado::gradosPorNivel($nivel),
            ];
        }

        return response()->json(['niveles' => $niveles]);
    }

    public function areas(Request $request): JsonResponse
    {
        $query = Area::query()->where('activo', true)->orderBy('nombre');

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->query('nivel'));
        }

        if ($request->boolean('incluir_cursos')) {
            $query->with(['cursosCatalogo' => fn ($q) => $q
                ->where('activo', true)
                ->where('es_institucional', true)
                ->orderBy('nombre')]);
        }

        return response()->json($query->get());
    }

    public function competenciasPorArea(Request $request, Area $area): JsonResponse
    {
        $items = $this->catalogoCompetenciaQuery
            ->queryCompetenciasPorArea($request, $area)
            ->get();

        if ($request->boolean('conteo_uso') || $request->boolean('incluir_capacidades')) {
            return response()->json(
                $this->catalogoCompetenciaQuery->serializarCompetencias($request, $items),
            );
        }

        return response()->json($items);
    }

    public function capacidadesPorCompetencia(Request $request, Competencia $competencia): JsonResponse
    {
        $items = $this->catalogoCompetenciaQuery
            ->queryCapacidadesPorCompetencia($request, $competencia)
            ->get();

        if ($request->boolean('conteo_uso')) {
            return response()->json(
                $this->catalogoCompetenciaQuery->serializarCapacidades($request, $items),
            );
        }

        return response()->json($items);
    }

    public function periodos(Request $request): JsonResponse
    {
        $query = PeriodoAcademico::query()->orderBy('bimestre');

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', $request->query('anio_escolar'));
        }

        return response()->json($query->get());
    }

    public function semanasPorPeriodo(PeriodoAcademico $periodo): JsonResponse
    {
        $semanas = SemanaAcademica::query()
            ->where('periodo_academico_id', $periodo->id)
            ->orderBy('numero_semana')
            ->get();

        return response()->json($semanas);
    }
}
