<?php

namespace App\Services\Curricular\EvaluacionBimestral;

use App\Models\Curricular\EvalBimEscalaLogro;

class EscalaLogroService
{
    /** @var list<array{codigo: string, min: float, max: float}> */
    private const RANGOS_DEFECTO = [
        ['codigo' => 'AD', 'min' => 18.0, 'max' => 20.0],
        ['codigo' => 'A', 'min' => 14.0, 'max' => 17.99],
        ['codigo' => 'B', 'min' => 11.0, 'max' => 13.99],
        ['codigo' => 'C', 'min' => 0.0, 'max' => 10.99],
    ];

    public function literalDesdeNumerico(?float $nivelNumerico): ?string
    {
        if ($nivelNumerico === null) {
            return null;
        }

        $nota = round($nivelNumerico, 2);
        $rangos = $this->cargarRangos();

        foreach ($rangos as $rango) {
            if ($nota >= $rango['min'] && $nota <= $rango['max']) {
                return $rango['codigo'];
            }
        }

        return null;
    }

    /**
     * @return list<array{codigo_literal: string, etiqueta: string|null, orden: int, nota_min: float, nota_max: float}>
     */
    public function listarEscalaActiva(): array
    {
        $filas = EvalBimEscalaLogro::query()
            ->where('activo', true)
            ->orderByDesc('orden')
            ->get();

        if ($filas->isEmpty()) {
            return array_map(fn (array $r) => [
                'codigo_literal' => $r['codigo'],
                'etiqueta' => null,
                'orden' => 0,
                'nota_min' => $r['min'],
                'nota_max' => $r['max'],
            ], self::RANGOS_DEFECTO);
        }

        return $filas->map(fn (EvalBimEscalaLogro $f) => [
            'codigo_literal' => $f->codigo_literal,
            'etiqueta' => $f->etiqueta,
            'orden' => (int) $f->orden,
            'nota_min' => (float) $f->nota_min,
            'nota_max' => (float) $f->nota_max,
        ])->values()->all();
    }

    /**
     * @return list<array{codigo: string, min: float, max: float}>
     */
    private function cargarRangos(): array
    {
        $filas = EvalBimEscalaLogro::query()
            ->where('activo', true)
            ->orderByDesc('orden')
            ->get();

        if ($filas->isEmpty()) {
            return self::RANGOS_DEFECTO;
        }

        return $filas->map(fn (EvalBimEscalaLogro $f) => [
            'codigo' => $f->codigo_literal,
            'min' => (float) $f->nota_min,
            'max' => (float) $f->nota_max,
        ])->all();
    }
}
