<?php

namespace App\Services\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CatalogoCompetenciaCapacidadQuery
{
    public function __construct(
        private readonly CompetenciaCapacidadService $usoService = new CompetenciaCapacidadService,
    ) {}

    /**
     * @return Builder<Competencia>
     */
    public function queryCompetenciasPorArea(Request $request, Area $area): Builder
    {
        $query = Competencia::query()
            ->where('area_id', $area->id)
            ->orderBy('nombre');

        $this->aplicarFiltroActivo($request, $query);

        return $query;
    }

    /**
     * @return Builder<Capacidad>
     */
    public function queryCapacidadesPorCompetencia(Request $request, Competencia $competencia): Builder
    {
        $query = Capacidad::query()
            ->where('competencia_id', $competencia->id)
            ->orderBy('nombre');

        $this->aplicarFiltroActivo($request, $query);

        return $query;
    }

    /**
     * @param  Builder<Competencia>|Builder<Capacidad>  $query
     */
    private function aplicarFiltroActivo(Request $request, Builder $query): void
    {
        $activoParam = $request->query('activo');

        if ($activoParam === null || $activoParam === '') {
            $query->where('activo', true);

            return;
        }

        if ($activoParam === 'all') {
            if (! $request->user()?->can('gestionar_competencias_capacidades')) {
                throw ValidationException::withMessages([
                    'activo' => ['No tiene permiso para listar registros inactivos.'],
                ]);
            }

            return;
        }

        $activo = filter_var($activoParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($activo === null) {
            throw ValidationException::withMessages([
                'activo' => ['El filtro activo no es válido. Use true, false o all.'],
            ]);
        }

        $query->where('activo', $activo);
    }

    /**
     * @param  Collection<int, Competencia>  $competencias
     * @return list<array<string, mixed>>
     */
    public function serializarCompetencias(Request $request, Collection $competencias): array
    {
        $conteoUso = $request->boolean('conteo_uso');
        $incluirCapacidades = $request->boolean('incluir_capacidades');

        return $competencias->map(function (Competencia $competencia) use ($request, $conteoUso, $incluirCapacidades) {
            $payload = $competencia->toArray();

            if ($conteoUso) {
                $payload['conteo_uso'] = $this->usoService->contarUsoActivoCompetencia($competencia->id);
            }

            if ($incluirCapacidades) {
                $caps = $this->queryCapacidadesPorCompetencia($request, $competencia)->get();
                $payload['capacidades'] = $this->serializarCapacidades($request, $caps);
            }

            return $payload;
        })->all();
    }

    /**
     * @param  Collection<int, Capacidad>  $capacidades
     * @return list<array<string, mixed>>
     */
    public function serializarCapacidades(Request $request, Collection $capacidades): array
    {
        $conteoUso = $request->boolean('conteo_uso');

        return $capacidades->map(function (Capacidad $capacidad) use ($conteoUso) {
            $payload = $capacidad->toArray();

            if ($conteoUso) {
                $payload['conteo_uso'] = $this->usoService->contarUsoActivoCapacidad($capacidad->id);
            }

            return $payload;
        })->all();
    }
}
