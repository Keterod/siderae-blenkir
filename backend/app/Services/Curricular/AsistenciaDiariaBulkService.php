<?php

namespace App\Services\Curricular;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciaDiariaBulkService
{
    /**
     * @param  array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string, fecha: string, filas: list<array{estudiante_id: int, estado: string, observacion?: string|null}>}  $data
     * @return array{guardados: int, creados: int, actualizados: int, fecha: string}
     */
    public function guardar(array $data, User $user): array
    {
        $creados = 0;
        $actualizados = 0;
        $fecha = Carbon::parse($data['fecha'])->toDateString();

        DB::transaction(function () use ($data, $user, $fecha, &$creados, &$actualizados): void {
            foreach ($data['filas'] as $fila) {
                Estudiante::query()->whereKey($fila['estudiante_id'])->firstOrFail();

                $estudianteId = (int) $fila['estudiante_id'];

                $registro = AsistenciaDiaria::query()
                    ->where('estudiante_id', $estudianteId)
                    ->where('anio_escolar', $data['anio_escolar'])
                    ->where('nivel', $data['nivel'])
                    ->where('grado', $data['grado'])
                    ->where('seccion', $data['seccion'])
                    ->where('sede', $data['sede'])
                    ->whereDate('fecha', $fecha)
                    ->first();

                if ($registro === null) {
                    $registro = new AsistenciaDiaria([
                        'estudiante_id' => $estudianteId,
                        'anio_escolar' => $data['anio_escolar'],
                        'nivel' => $data['nivel'],
                        'grado' => $data['grado'],
                        'seccion' => $data['seccion'],
                        'sede' => $data['sede'],
                        'fecha' => $fecha,
                        'registrado_por' => $user->id,
                    ]);
                    $creados++;
                } else {
                    $actualizados++;
                }

                $registro->estado = $fila['estado'];
                $registro->observacion = $fila['observacion'] ?? null;
                $registro->save();
            }
        });

        $total = $creados + $actualizados;

        activity()
            ->causedBy($user)
            ->withProperties([
                'accion' => 'asistencia_diaria.bulk_guardado',
                'cantidad' => $total,
                'anio_escolar' => $data['anio_escolar'],
                'nivel' => $data['nivel'],
                'sede' => $data['sede'],
                'grado' => $data['grado'],
                'seccion' => $data['seccion'],
                'fecha' => $fecha,
                'creados' => $creados,
                'actualizados' => $actualizados,
            ])
            ->log('asistencia_diaria.bulk_guardado');

        return [
            'guardados' => $total,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'fecha' => $fecha,
        ];
    }
}
