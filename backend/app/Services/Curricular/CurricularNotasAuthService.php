<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CurricularNotasAuthService
{
    public function esAdministrador(User $user): bool
    {
        return $user->hasRole('administrador');
    }

    public function puedeRegistrarEnAsignacion(User $user, DocenteCursoAula $asignacion): bool
    {
        if (! $user->can('registrar_notas_semanales')) {
            return false;
        }

        if ($this->esAdministrador($user)) {
            return true;
        }

        return (int) $asignacion->user_id === (int) $user->id;
    }

    public function puedeVerAsignacion(User $user, DocenteCursoAula $asignacion): bool
    {
        if ($user->can('ver_notas_academicas') && $this->esAdministrador($user)) {
            return true;
        }

        if ($user->can('gestionar_asignaciones_docente') || $user->hasRole('directivo')) {
            return $user->can('ver_notas_academicas');
        }

        return (int) $asignacion->user_id === (int) $user->id;
    }

    public function assertPuedeRegistrarEnAsignacion(User $user, DocenteCursoAula $asignacion): void
    {
        if ($this->puedeRegistrarEnAsignacion($user, $asignacion)) {
            return;
        }

        throw ValidationException::withMessages([
            'asignacion_docente_id' => ['La asignación no pertenece al docente autenticado.'],
        ]);
    }

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     sede: string,
     *     grado: string,
     *     seccion: string,
     *     malla_curso_id: int
     * }  $filtros
     */
    public function resolverAsignacionActiva(array $filtros): ?DocenteCursoAula
    {
        return DocenteCursoAula::query()
            ->where('activo', true)
            ->where('malla_curso_id', (int) $filtros['malla_curso_id'])
            ->where('anio_escolar', $filtros['anio_escolar'])
            ->where('nivel', $filtros['nivel'])
            ->where('grado', $filtros['grado'])
            ->where('seccion', $filtros['seccion'])
            ->where('sede', $filtros['sede'])
            ->with(['user', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
            ->orderBy('id')
            ->first();
    }
}
