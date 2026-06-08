<?php

/**
 * Dataset Inicial II Bimestre 2026 — SIDERAE-Blenkir (sede Chilca).
 *
 * Criterios base: Untitled-1.php (documento principal, normalizado).
 * Criterios extra: Aprestamiento, Inglés y Educación Física.
 */

use App\Services\Curricular\CatalogoNivelGrado;

$mapaCursoCanonico = [
    'Raz. Matemático' => 'Razonamiento Matemático',
    'Raz. Verbal' => 'Razonamiento Verbal',
];

/**
 * @return list<array{grado: string, area: string, curso: string, tema: string, competencia: string, capacidad: string, criterio: string, orden: int}>
 */
$normalizarDesdeUntitled = static function () use ($mapaCursoCanonico): array {
    $ruta = base_path('Untitled-1.php');
    if (! is_file($ruta)) {
        throw new RuntimeException("No se encontró Untitled-1.php en: {$ruta}");
    }

    $legacy = require $ruta;
    if (! is_array($legacy)) {
        throw new RuntimeException('Untitled-1.php debe retornar un array.');
    }

    $criterios = [];
    foreach ($legacy as $item) {
        $textoCriterio = trim((string) ($item['criterio'] ?? ''));
        $descripcion = isset($item['descripcion']) && $item['descripcion'] !== null
            ? trim((string) $item['descripcion'])
            : '';

        $tema = $textoCriterio;
        $criterioEvaluacion = $descripcion !== '' ? $descripcion : $textoCriterio;

        if (str_contains($textoCriterio, ':')) {
            [$prefijo, $resto] = explode(':', $textoCriterio, 2);
            $tema = trim($prefijo);
            $criterioEvaluacion = trim($resto) !== '' ? trim($resto) : $textoCriterio;
        }

        $cursoLegacy = (string) ($item['curso'] ?? '');
        $curso = $mapaCursoCanonico[$cursoLegacy] ?? $cursoLegacy;

        $criterios[] = [
            'grado' => (string) $item['grado'],
            'area' => (string) $item['area'],
            'curso' => $curso,
            'tema' => $tema,
            'competencia' => trim((string) ($item['competencia'] ?? '')),
            'capacidad' => trim((string) ($item['capacidad'] ?? '')),
            'criterio' => $criterioEvaluacion,
            'orden' => (int) ($item['orden'] ?? 0),
        ];
    }

    return $criterios;
};

$extra = require __DIR__.'/inicial_ii_bimestre_2026_criterios_extra.php';
$criterios = array_merge($normalizarDesdeUntitled(), $extra);

return [
    'meta' => [
        'anio_escolar' => '2026',
        'bimestre' => '2',
        'nivel' => CatalogoNivelGrado::NIVEL_INICIAL,
        'sede' => 'chilca',
        'total_criterios_esperados' => 289,
    ],
    'grados' => CatalogoNivelGrado::GRADOS_INICIAL,
    'cursos_canonicos' => [
        ['area' => 'Matemática', 'curso' => 'Aritmética', 'orden' => 1],
        ['area' => 'Matemática', 'curso' => 'Geometría', 'orden' => 2],
        ['area' => 'Matemática', 'curso' => 'Razonamiento Matemático', 'orden' => 3],
        ['area' => 'Comunicación', 'curso' => 'Comunicación', 'orden' => 4],
        ['area' => 'Comunicación', 'curso' => 'Razonamiento Verbal', 'orden' => 5],
        ['area' => 'Comunicación', 'curso' => 'Aprestamiento', 'orden' => 6],
        ['area' => 'Ciencia y Tecnología', 'curso' => 'Ciencia y Tecnología', 'orden' => 7],
        ['area' => 'Personal Social', 'curso' => 'Personal Social', 'orden' => 8],
        ['area' => 'Inglés', 'curso' => 'Inglés', 'orden' => 9],
        ['area' => 'Educación Física', 'curso' => 'Educación Física', 'orden' => 10],
    ],
    'aulas' => [
        '3 años' => ['ARDILLITAS', 'ESTRELLITAS DE MAR', 'PULPITOS', 'TIBURONCITOS'],
        '4 años' => ['HORMIGUITAS', 'LEONCITOS', 'PUMITAS', 'TIGRESITOS'],
        '5 años' => ['CANGREJITOS', 'LORITOS', 'PALOMITAS', 'PATITOS', 'POLLITOS'],
    ],
    'estudiantes_demo' => [
        'codigo_inicio' => 83_000_001,
        'codigo_fin' => 83_000_052,
        'por_aula' => 4,
        'sede' => 'chilca',
        'anio_escolar' => '2026',
    ],
    'criterios' => $criterios,
];
