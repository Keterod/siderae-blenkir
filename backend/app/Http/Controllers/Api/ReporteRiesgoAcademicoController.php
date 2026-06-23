<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndiceRiesgo;
use App\Support\SedeOperativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteRiesgoAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = IndiceRiesgo::query()
            ->with('estudiante')
            ->whereHas('estudiante', static function ($q): void {
                $q->where('sede', SedeOperativa::CHILCA);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('anio_escolar')) {
            $query->where('anio_escolar', (string) $request->query('anio_escolar'));
        }

        if ($request->filled('bimestre')) {
            $query->where('bimestre', (string) $request->query('bimestre'));
        }

        if ($request->filled('grado')) {
            $query->whereHas('estudiante', static function ($q) use ($request): void {
                $q->where('grado', (string) $request->query('grado'));
            });
        }

        if ($request->filled('seccion')) {
            $query->whereHas('estudiante', static function ($q) use ($request): void {
                $q->where('seccion', (string) $request->query('seccion'));
            });
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', (string) $request->query('nivel'));
        }

        $paginado = $query->paginate(
            perPage: (int) $request->query('per_page', 15),
            page: (int) $request->query('page', 1),
        );

        return response()->json($paginado->through(static function (IndiceRiesgo $indice): array {
            $estudiante = $indice->estudiante;

            return [
                'id' => $indice->id,
                'estudiante_id' => $indice->estudiante_id,
                'estudiante' => $estudiante ? trim("{$estudiante->apellidos}, {$estudiante->nombres}") : null,
                'grado' => $estudiante?->grado,
                'seccion' => $estudiante?->seccion,
                'anio_escolar' => $indice->anio_escolar,
                'bimestre' => $indice->bimestre,
                'indice' => (float) $indice->indice,
                'nivel' => $indice->nivel,
                'fecha' => $indice->created_at?->toDateString(),
            ];
        }));
    }
}
