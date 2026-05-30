<?php

namespace App\Services\Curricular;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Estudiante;

class AsistenciaDiariaFormularioService
{
    /**
     * @param  array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string, fecha: string}  $contexto
     * @return array<string, mixed>
     */
    public function construir(array $contexto): array
    {
        $estudiantes = Estudiante::query()
            ->where('activo', true)
            ->where('anio_escolar', $contexto['anio_escolar'])
            ->where('nivel', $contexto['nivel'])
            ->where('sede', $contexto['sede'])
            ->where('grado', $contexto['grado'])
            ->where('seccion', $contexto['seccion'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $asistencias = AsistenciaDiaria::query()
            ->where('anio_escolar', $contexto['anio_escolar'])
            ->where('nivel', $contexto['nivel'])
            ->where('sede', $contexto['sede'])
            ->where('grado', $contexto['grado'])
            ->where('seccion', $contexto['seccion'])
            ->whereDate('fecha', $contexto['fecha'])
            ->whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->get()
            ->keyBy('estudiante_id');

        $filasEstudiantes = $estudiantes->map(function (Estudiante $estudiante) use ($asistencias) {
            $registro = $asistencias->get($estudiante->id);

            return [
                'id' => $estudiante->id,
                'codigo' => $estudiante->codigo,
                'nombres' => $estudiante->nombres,
                'apellidos' => $estudiante->apellidos,
                'asistencia' => $registro === null ? null : [
                    'estado' => $registro->estado,
                    'observacion' => $registro->observacion,
                    'updated_at' => $registro->updated_at?->toIso8601String(),
                ],
            ];
        })->values()->all();

        $registrados = $asistencias->count();

        return [
            'contexto' => $contexto,
            'estados_permitidos' => AsistenciaDiaria::ESTADOS,
            'estudiantes' => $filasEstudiantes,
            'totales' => [
                'registrados' => $registrados,
                'alumnos' => $estudiantes->count(),
            ],
        ];
    }
}
