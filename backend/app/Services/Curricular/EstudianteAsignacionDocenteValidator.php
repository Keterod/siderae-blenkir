<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\Estudiante;

class EstudianteAsignacionDocenteValidator
{
    public function __construct(
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
    ) {}

    public function perteneceAAsignacion(Estudiante $estudiante, DocenteCursoAula $asignacion): bool
    {
        if (! $asignacion->activo) {
            return false;
        }

        if ($estudiante->anio_escolar !== $asignacion->anio_escolar) {
            return false;
        }

        if ($estudiante->sede !== $asignacion->sede) {
            return false;
        }

        if ($estudiante->seccion !== $asignacion->seccion) {
            return false;
        }

        if ($estudiante->nivel !== $asignacion->nivel) {
            return false;
        }

        $gradoLegacy = $estudiante->grado;
        $gradoCurricular = $this->equivalenciaGradoService->aCurricular(
            (string) $estudiante->nivel,
            (string) $gradoLegacy
        );

        if ($gradoCurricular === null || $gradoCurricular !== $asignacion->grado) {
            return false;
        }

        return true;
    }
}
