<?php

use App\Models\Estudiante;
use App\Models\IndiceRiesgo;
use App\Services\MlRiskService;
use App\Services\RiesgoAcademicoService;
use Illuminate\Support\Facades\Artisan;

Artisan::command('demo:procesar-riesgos
    {--sede= : Sede (chilca/auquimarca)}
    {--anio= : Año escolar (p. ej. 2026)}
    {--bimestre= : Bimestre (1-4)}
    {--nivel= : Nivel (primaria/secundaria)}
    {--grado= : Grado (p. ej. 1°)}
    {--seccion= : Sección (p. ej. A)}
    {--force : Reprocesar aunque exista índice para ese año/bimestre}
    {--confirmar-post-import : Confirmación explícita para ejecución masiva excepcional}', function () {
    // Herramienta operativa excepcional para post-seed/post-import.
    // No es parte del flujo diario normal ni debe ejecutarse automáticamente.
    // Puede generar carga alta porque invoca procesamiento ML por estudiante.

    $sede = (string) ($this->option('sede') ?? '');
    $anio = (string) ($this->option('anio') ?? '');
    $bimestre = (string) ($this->option('bimestre') ?? '');
    $nivel = (string) ($this->option('nivel') ?? '');
    $grado = (string) ($this->option('grado') ?? '');
    $seccion = (string) ($this->option('seccion') ?? '');
    $force = (bool) $this->option('force');
    $confirmarPostImport = (bool) $this->option('confirmar-post-import');

    $this->warn('Este comando está pensado para post-importación/post-seed, no para uso normal diario.');
    $this->warn('No debe ejecutarse automáticamente: procesa riesgo con invocaciones ML por estudiante.');

    if (! $confirmarPostImport) {
        $this->error('Para ejecutar este procesamiento masivo, usa --confirmar-post-import.');

        return self::FAILURE;
    }

    if ($sede === '' || $anio === '' || $bimestre === '') {
        $this->error('Parámetros requeridos: --sede, --anio, --bimestre');

        return self::FAILURE;
    }

    /** @var RiesgoAcademicoService $riesgoAcademicoService */
    $riesgoAcademicoService = app(RiesgoAcademicoService::class);
    /** @var MlRiskService $mlRiskService */
    $mlRiskService = app(MlRiskService::class);

    $query = Estudiante::query()->where('sede', $sede)->where('anio_escolar', $anio);

    if ($nivel !== '') {
        $query->where('nivel', $nivel);
    }

    if ($grado !== '') {
        $query->where('grado', $grado);
    }

    if ($seccion !== '') {
        $query->where('seccion', $seccion);
    }

    $estudiantes = $query->orderBy('id')->get();

    $encontrados = $estudiantes->count();
    $procesados = 0;
    $omitidosPorIndice = 0;
    $omitidosPorDatos = 0;
    $fallidos = 0;

    $this->info("Estudiantes encontrados: {$encontrados}");
    $this->line("Contexto: sede={$sede}, anio={$anio}, bimestre={$bimestre}".($force ? ' (force)' : ''));

    foreach ($estudiantes as $estudiante) {
        $yaExiste = IndiceRiesgo::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $anio)
            ->where('bimestre', $bimestre)
            ->exists();

        if ($yaExiste && ! $force) {
            $omitidosPorIndice++;
            continue;
        }

        $resultado = $riesgoAcademicoService->procesarEstudiante($estudiante, $anio, $bimestre, $mlRiskService);

        if (($resultado['status'] ?? null) === 'procesado') {
            $procesados++;
            continue;
        }

        if (($resultado['status'] ?? null) === 'omitido') {
            $omitidosPorDatos++;
            continue;
        }

        $fallidos++;
    }

    $this->newLine();
    $this->info('Resumen:');
    $this->line(" - Estudiantes encontrados: {$encontrados}");
    $this->line(" - Riesgos procesados: {$procesados}");
    $this->line(" - Omitidos por índice existente: {$omitidosPorIndice}");
    $this->line(" - Omitidos por datos insuficientes: {$omitidosPorDatos}");
    $this->line(" - Fallidos por ML/error: {$fallidos}");

    return self::SUCCESS;
})->purpose(
    'Herramienta excepcional post-import/post-seed para procesar riesgos en lote (no flujo normal diario ni automático).'
);

