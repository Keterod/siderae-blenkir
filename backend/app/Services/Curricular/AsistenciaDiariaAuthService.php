<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AsistenciaDiariaAuthService
{
    public function __construct(
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
    ) {}
    /**
     * @param  array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string}  $contexto
     */
    public function autorizarVer(User $user, array $contexto): void
    {
        if (! $user->can('ver_asistencia_curricular')) {
            throw new AuthorizationException('Permiso denegado.');
        }

        if ($this->puedeAccederGlobalmente($user)) {
            return;
        }

        if ($this->tieneAsignacionActivaEnAula($user, $contexto)) {
            return;
        }

        throw new AuthorizationException('No autorizado para consultar asistencia de esta aula.');
    }

    /**
     * @param  array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string}  $contexto
     */
    public function autorizarRegistrar(User $user, array $contexto): void
    {
        if (! $user->can('registrar_asistencia_curricular')) {
            throw new AuthorizationException('Permiso denegado.');
        }

        if ($this->puedeRegistrarGlobalmente($user)) {
            return;
        }

        if ($this->tieneAsignacionActivaEnAula($user, $contexto)) {
            return;
        }

        throw new AuthorizationException('No autorizado para registrar asistencia en esta aula.');
    }

    public function puedeRegistrar(User $user): bool
    {
        return $user->can('registrar_asistencia_curricular');
    }

    /**
     * @param  array{anio_escolar: string, nivel: string, sede: string, grado: string, seccion: string}  $contexto
     */
    public function tieneAsignacionActivaEnAula(User $user, array $contexto): bool
    {
        $gradoCurricular = $this->equivalenciaGradoService->aCurricular(
            (string) $contexto['nivel'],
            (string) $contexto['grado'],
        );

        if ($gradoCurricular === null) {
            return false;
        }

        return DocenteCursoAula::query()
            ->where('user_id', $user->id)
            ->where('activo', true)
            ->where('anio_escolar', $contexto['anio_escolar'])
            ->where('nivel', $contexto['nivel'])
            ->where('sede', $contexto['sede'])
            ->where('grado', $gradoCurricular)
            ->where('seccion', $contexto['seccion'])
            ->exists();
    }

    private function puedeAccederGlobalmente(User $user): bool
    {
        return $user->hasRole('administrador')
            || $user->can('gestionar_asignaciones_docente')
            || $user->hasRole('directivo');
    }

    private function puedeRegistrarGlobalmente(User $user): bool
    {
        return $user->hasRole('administrador')
            || $user->can('gestionar_asignaciones_docente');
    }
}
