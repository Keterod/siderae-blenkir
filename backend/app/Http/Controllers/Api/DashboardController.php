<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardQueryRequest;
use App\Models\Alerta;
use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * Resumen JSON del dashboard con filtros reales y porcentajes (Sprint 6B).
     */
    public function index(DashboardQueryRequest $request): JsonResponse
    {
        return response()->json($this->construirCarga($request));
    }

    /**
     * Exportación PDF básica con los mismos filtros que index.
     * Permiso: ver_dashboard (sin permiso específico de exportación en seeders).
     */
    public function export(DashboardQueryRequest $request): Response
    {
        $carga = $this->construirCarga($request);
        $usuario = $request->user();

        $generadoAt = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');

        activity()
            ->causedBy($usuario)
            ->withProperties([
                'accion' => 'dashboard.pdf_exportado',
                'filtros' => $carga['filtros_aplicados'] ?? [],
            ])
            ->log('dashboard.pdf_exportado');

        return Pdf::loadView('pdf.dashboard', array_merge($carga, [
            'generado_at' => $generadoAt,
            'usuario_email' => $usuario?->email ?? '—',
        ]))
            ->download('dashboard-siderae-blenkir.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function construirCarga(DashboardQueryRequest $request): array
    {
        $f = $request->filtrosAplicados();

        $filtrosAplicados = array_filter([
            'sede' => $f['sede'],
            'nivel' => $f['nivel'],
            'grado' => $f['grado'],
            'seccion' => $f['seccion'],
            'nivel_riesgo' => $f['nivel_riesgo'],
        ], static fn ($v) => $v !== null && $v !== '');

        $opcionesFiltros = $this->opcionesFiltrosDesdeBd();

        $qLoc = Estudiante::query();

        if (($f['sede'] ?? '') !== '') {
            $qLoc->where('sede', $f['sede']);
        }

        if (($f['nivel'] ?? '') !== '') {
            $qLoc->where('nivel', $f['nivel']);
        }

        if (($g = $f['grado'] ?? null) !== null && $g !== '') {
            $qLoc->where('grado', $g);
        }

        if (($s = $f['seccion'] ?? null) !== null && $s !== '') {
            $qLoc->where('seccion', $s);
        }

        $idsTodos = $qLoc->pluck('id');

        if ($idsTodos->isEmpty()) {
            return $this->cargaVacia($filtrosAplicados, $opcionesFiltros);
        }

        $ultimoIds = DB::table('indices_riesgo')
            ->whereIn('estudiante_id', $idsTodos)
            ->selectRaw('max(id) as id')
            ->groupBy('estudiante_id')
            ->pluck('id');

        /** @var \Illuminate\Support\Collection<int, IndiceRiesgo> $indicesUltimos */
        $indicesUltimos = $ultimoIds->isEmpty()
            ? collect()
            : IndiceRiesgo::query()->whereIn('id', $ultimoIds)->get();

        $slugPorEstudiante = [];
        foreach ($indicesUltimos as $ir) {
            $slug = self::nivelIndiceASlug((string) $ir->nivel);
            if ($slug !== null) {
                $slugPorEstudiante[$ir->estudiante_id] = $slug;
            }
        }

        $filtroNivelRiesgo = $f['nivel_riesgo'] ?? null;

        if ($filtroNivelRiesgo !== null && $filtroNivelRiesgo !== '') {
            $idsScoped = collect($slugPorEstudiante)
                ->filter(fn ($slug) => $slug === $filtroNivelRiesgo)
                ->keys()
                ->values();
        } else {
            $idsScoped = $idsTodos;
        }

        $totalEstudiantes = $idsScoped->count();

        $riesgosPorNivel = ['alto' => 0, 'medio' => 0, 'bajo' => 0];

        foreach ($idsScoped as $eid) {
            if (! isset($slugPorEstudiante[$eid])) {
                continue;
            }
            $cl = $slugPorEstudiante[$eid];
            $riesgosPorNivel[$cl]++;
        }

        $sumaRiesgos = $riesgosPorNivel['alto'] + $riesgosPorNivel['medio'] + $riesgosPorNivel['bajo'];

        $porcentajesRiesgo = [
            'alto' => self::porcentajeEntero($riesgosPorNivel['alto'], $sumaRiesgos),
            'medio' => self::porcentajeEntero($riesgosPorNivel['medio'], $sumaRiesgos),
            'bajo' => self::porcentajeEntero($riesgosPorNivel['bajo'], $sumaRiesgos),
        ];

        $idsParaAlertas = $idsScoped;

        $conteosAlerta = Alerta::query()
            ->whereIn('estudiante_id', $idsParaAlertas)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $alertasPorEstado = [
            'pendiente' => (int) ($conteosAlerta['pendiente'] ?? 0),
            'en_atencion' => (int) ($conteosAlerta['en_atencion'] ?? 0),
            'cerrada' => (int) ($conteosAlerta['cerrada'] ?? 0),
        ];

        $sumaAlertas =
            $alertasPorEstado['pendiente']
            + $alertasPorEstado['en_atencion']
            + $alertasPorEstado['cerrada'];

        $porcentajesAlertas = [
            'pendiente' => self::porcentajeEntero($alertasPorEstado['pendiente'], $sumaAlertas),
            'en_atencion' => self::porcentajeEntero($alertasPorEstado['en_atencion'], $sumaAlertas),
            'cerrada' => self::porcentajeEntero($alertasPorEstado['cerrada'], $sumaAlertas),
        ];

        $ultimosRiesgos = IndiceRiesgo::query()
            ->with(['estudiante:id,codigo,nombres,apellidos'])
            ->whereIn('estudiante_id', $idsParaAlertas)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (IndiceRiesgo $indice) => self::filaUltimoRiesgo($indice))
            ->values()
            ->all();

        return [
            'total_estudiantes' => $totalEstudiantes,
            'riesgos_por_nivel' => $riesgosPorNivel,
            'porcentajes_riesgo' => $porcentajesRiesgo,
            'alertas_por_estado' => $alertasPorEstado,
            'porcentajes_alertas' => $porcentajesAlertas,
            'ultimos_riesgos' => $ultimosRiesgos,
            'filtros_aplicados' => $filtrosAplicados,
            'opciones_filtros' => $opcionesFiltros,
        ];
    }

    /**
     * Opções derivadas de BD (no inventar valores).
     *
     * @return array<string, array<int, string>>
     */
    private function opcionesFiltrosDesdeBd(): array
    {
        $sedes = Estudiante::query()->distinct()->orderBy('sede')->pluck('sede')->filter()->values()->map(fn ($v) => (string) $v)->all();
        $nivelesEducativos = Estudiante::query()->distinct()->orderBy('nivel')->pluck('nivel')->filter()->values()->map(fn ($v) => (string) $v)->all();
        $grados = Estudiante::query()->distinct()->orderBy('grado')->pluck('grado')->filter()->values()->map(fn ($v) => (string) $v)->all();
        $secciones = Estudiante::query()->distinct()->orderBy('seccion')->pluck('seccion')->filter()->values()->map(fn ($v) => (string) $v)->all();

        return [
            'sedes' => array_values($sedes),
            'niveles' => array_values($nivelesEducativos),
            'grados' => array_values($grados),
            'secciones' => array_values($secciones),
            'niveles_riesgo' => ['alto', 'medio', 'bajo'],
        ];
    }

    /**
     * @param  array<string, string|null>  $filtrosAplicados
     * @param  array<string, array<int, string>>  $opcionesFiltros
     * @return array<string, mixed>
     */
    private function cargaVacia(array $filtrosAplicados, array $opcionesFiltros): array
    {
        return [
            'total_estudiantes' => 0,
            'riesgos_por_nivel' => ['alto' => 0, 'medio' => 0, 'bajo' => 0],
            'porcentajes_riesgo' => ['alto' => 0, 'medio' => 0, 'bajo' => 0],
            'alertas_por_estado' => ['pendiente' => 0, 'en_atencion' => 0, 'cerrada' => 0],
            'porcentajes_alertas' => ['pendiente' => 0, 'en_atencion' => 0, 'cerrada' => 0],
            'ultimos_riesgos' => [],
            'filtros_aplicados' => $filtrosAplicados,
            'opciones_filtros' => $opcionesFiltros,
        ];
    }

    /**
     * Porcentaje entero 0–100 (redondeo estándar PHP).
     */
    private static function porcentajeEntero(int $parte, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($parte / $total) * 100, 2);
    }

    private static function nivelIndiceASlug(string $nivelDb): ?string
    {
        return match ($nivelDb) {
            'Alto' => 'alto',
            'Medio' => 'medio',
            'Bajo' => 'bajo',
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function filaUltimoRiesgo(IndiceRiesgo $indice): array
    {
        $est = $indice->estudiante;
        $nombreEstudiante = $est !== null
            ? trim((string) ($est->nombres ?? '').' '.(string) ($est->apellidos ?? ''))
            : '';

        return [
            'id' => $indice->id,
            'estudiante_id' => $indice->estudiante_id,
            'estudiante' => $nombreEstudiante,
            'codigo' => $est?->codigo ?? '',
            'indice' => (float) $indice->indice,
            'nivel' => match ($indice->nivel) {
                'Alto' => 'alto',
                'Medio' => 'medio',
                'Bajo' => 'bajo',
                default => strtolower((string) $indice->nivel),
            },
            'fecha' => $indice->created_at?->toIso8601String(),
            'anio_escolar' => $indice->anio_escolar,
            'bimestre' => $indice->bimestre,
        ];
    }
}
