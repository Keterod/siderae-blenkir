<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\AsignacionDocenteDuplicadaException;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DocenteCursoAulaValidator
{
    /**
     * @param  array{anio_escolar: string, nivel: string, grado: string, seccion: string, sede: string, malla_curso_id: int, user_id: int}  $datos
     */
    public function validarAsignacionUnicaActiva(array $datos, ?int $exceptoId = null): void
    {
        $query = DocenteCursoAula::query()
            ->where('anio_escolar', $datos['anio_escolar'])
            ->where('nivel', $datos['nivel'])
            ->where('grado', $datos['grado'])
            ->where('seccion', $datos['seccion'])
            ->where('sede', $datos['sede'])
            ->where('malla_curso_id', $datos['malla_curso_id'])
            ->where('activo', true);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        if ($query->exists()) {
            throw new AsignacionDocenteDuplicadaException;
        }
    }

    public function validarUsuarioDocente(User $usuario): void
    {
        if (! $usuario->hasRole('docente')) {
            throw ValidationException::withMessages([
                'docente_id' => ['El usuario indicado no tiene rol docente.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $mallaCursoIds
     */
    public function validarMallaCursosEnMallaGrado(
        array $mallaCursoIds,
        string $anioEscolar,
        string $nivel,
        string $grado,
    ): void {
        if ($mallaCursoIds === []) {
            return;
        }

        $malla = MallaCurricular::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->first();

        if ($malla === null) {
            throw ValidationException::withMessages([
                'grado' => ['No existe malla curricular para el año, nivel y grado indicados.'],
            ]);
        }

        $validos = MallaCurso::query()
            ->where('malla_curricular_id', $malla->id)
            ->whereIn('id', $mallaCursoIds)
            ->pluck('id')
            ->all();

        $invalidos = array_values(array_diff($mallaCursoIds, $validos));
        if ($invalidos !== []) {
            throw ValidationException::withMessages([
                'malla_curso_ids' => ['Uno o más cursos no pertenecen a la malla del grado indicado.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $mallaCursoIds
     */
    public function validarMallaCursosActivos(array $mallaCursoIds): void
    {
        if ($mallaCursoIds === []) {
            return;
        }

        $inactivos = MallaCurso::query()
            ->whereIn('id', $mallaCursoIds)
            ->where('activo', false)
            ->with('cursoCatalogo')
            ->get();

        if ($inactivos->isEmpty()) {
            return;
        }

        $nombres = $inactivos
            ->map(fn (MallaCurso $curso): string => $curso->cursoCatalogo?->nombre ?? 'curso')
            ->implode(', ');

        throw ValidationException::withMessages([
            'malla_curso_ids' => ["No se pueden asignar cursos inactivos en la malla: {$nombres}."],
        ]);
    }
}
