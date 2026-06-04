<?php

namespace Database\Seeders\Demo;

use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Services\Curricular\AsignacionDocenteBulkService;
use Illuminate\Database\Seeder;

/**
 * Asigna docentes demo a cursos de malla para el flujo curricular vigente.
 */
class AsignacionDocenteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $bulkService = new AsignacionDocenteBulkService;

        $mallaCursoIds = DemoCurricularContext::mallaCursosPrimaria2do()
            ->pluck('id')
            ->all();

        $bulkService->sincronizar([
            'docente_id' => DemoCurricularContext::docente()->id,
            'anio_escolar' => DemoCurricularContext::ANIO_ESCOLAR,
            'nivel' => DemoCurricularContext::NIVEL_PRIMARIA,
            'grado' => DemoCurricularContext::GRADO_CURRICULAR_PRIMARIA,
            'seccion' => DemoCurricularContext::SECCION_PRINCIPAL,
            'sede' => DemoCurricularContext::SEDE_PRINCIPAL,
            'malla_curso_ids' => $mallaCursoIds,
        ]);

        $this->asignarDocenteSecundario($bulkService);
    }

    private function asignarDocenteSecundario(AsignacionDocenteBulkService $bulkService): void
    {
        $mallaSecundaria = MallaCurricular::query()
            ->where('anio_escolar', DemoCurricularContext::ANIO_ESCOLAR)
            ->where('nivel', DemoCurricularContext::NIVEL_SECUNDARIA)
            ->where('grado', DemoCurricularContext::GRADO_CURRICULAR_SECUNDARIA)
            ->first();

        if ($mallaSecundaria === null) {
            return;
        }

        $mallaCursoId = MallaCurso::query()
            ->where('malla_curricular_id', $mallaSecundaria->id)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->value('id');

        if ($mallaCursoId === null) {
            return;
        }

        $bulkService->sincronizar([
            'docente_id' => DemoCurricularContext::docenteSecundario()->id,
            'anio_escolar' => DemoCurricularContext::ANIO_ESCOLAR,
            'nivel' => DemoCurricularContext::NIVEL_SECUNDARIA,
            'grado' => DemoCurricularContext::GRADO_CURRICULAR_SECUNDARIA,
            'seccion' => DemoCurricularContext::SECCION_SECUNDARIA,
            'sede' => DemoCurricularContext::SEDE_PRINCIPAL,
            'malla_curso_ids' => [$mallaCursoId],
        ]);
    }
}
