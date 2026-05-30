<?php

namespace Database\Seeders\Demo;

use App\Models\Curricular\AsistenciaDiaria;
use App\Models\Estudiante;
use Illuminate\Database\Seeder;

/**
 * Asistencia diaria curricular demo (sin tabla asistencias legacy).
 */
class AsistenciaCurricularDemoSeeder extends Seeder
{
    /** @var list<string> */
    private const FECHAS_DEMO = [
        '2026-05-25',
        '2026-05-26',
        '2026-05-27',
        '2026-05-28',
        '2026-05-29',
    ];

    /** @var list<string> */
    private const ESTADOS_ROTACION = ['presente', 'presente', 'tarde', 'falta', 'justificado'];

    public function run(): void
    {
        $docente = DemoCurricularContext::docente();
        $estudiantes = DemoCurricularContext::estudiantesAulaPrincipal();

        if ($estudiantes->isEmpty()) {
            return;
        }

        foreach ($estudiantes as $indiceEstudiante => $estudiante) {
            foreach (self::FECHAS_DEMO as $indiceFecha => $fecha) {
                $estado = self::ESTADOS_ROTACION[($indiceEstudiante + $indiceFecha) % count(self::ESTADOS_ROTACION)];
                $this->sembrarAsistencia($estudiante, $fecha, $estado, $docente->id);
            }
        }
    }

    private function sembrarAsistencia(Estudiante $estudiante, string $fecha, string $estado, int $registradoPor): void
    {
        AsistenciaDiaria::query()->updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'anio_escolar' => DemoCurricularContext::ANIO_ESCOLAR,
                'nivel' => DemoCurricularContext::NIVEL_PRIMARIA,
                'grado' => DemoCurricularContext::GRADO_ESTUDIANTE_PRIMARIA,
                'seccion' => DemoCurricularContext::SECCION_PRINCIPAL,
                'sede' => DemoCurricularContext::SEDE_PRINCIPAL,
                'fecha' => $fecha,
            ],
            [
                'estado' => $estado,
                'observacion' => $estado === 'falta' ? 'Falta demo sin justificar' : null,
                'registrado_por' => $registradoPor,
            ],
        );
    }
}
