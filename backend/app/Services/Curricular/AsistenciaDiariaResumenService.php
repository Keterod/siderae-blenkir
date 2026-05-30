<?php

namespace App\Services\Curricular;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Estudiante;

class AsistenciaDiariaResumenService
{
    /**
     * @param  array{estudiante_id: int, anio_escolar: string, fecha_desde?: string|null, fecha_hasta?: string|null}  $params
     * @return array<string, mixed>
     */
    public function construirPorEstudiante(array $params): array
    {
        $estudiante = Estudiante::query()->findOrFail($params['estudiante_id']);

        $query = AsistenciaDiaria::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('anio_escolar', $params['anio_escolar']);

        if (! empty($params['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $params['fecha_desde']);
        }

        if (! empty($params['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $params['fecha_hasta']);
        }

        $registros = $query->get();

        $presente = $registros->where('estado', 'presente')->count();
        $tarde = $registros->where('estado', 'tarde')->count();
        $falta = $registros->where('estado', 'falta')->count();
        $justificado = $registros->where('estado', 'justificado')->count();
        $total = $registros->count();

        $porcentaje = $total > 0
            ? round((($presente + $tarde + $justificado) / $total) * 100, 2)
            : 0.0;

        return [
            'estudiante_id' => $estudiante->id,
            'anio_escolar' => $params['anio_escolar'],
            'fecha_desde' => $params['fecha_desde'] ?? null,
            'fecha_hasta' => $params['fecha_hasta'] ?? null,
            'totales' => [
                'presente' => $presente,
                'tarde' => $tarde,
                'falta' => $falta,
                'justificado' => $justificado,
                'total_registros' => $total,
                'porcentaje_asistencia_efectiva' => $porcentaje,
            ],
        ];
    }
}
