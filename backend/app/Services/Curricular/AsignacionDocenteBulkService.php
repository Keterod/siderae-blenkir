<?php

namespace App\Services\Curricular;

use App\Exceptions\Curricular\AsignacionDocenteDuplicadaException;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionDocenteBulkService
{
    public function __construct(
        private readonly DocenteCursoAulaValidator $validator = new DocenteCursoAulaValidator,
    ) {}

    /**
     * @param  array{
     *     docente_id: int,
     *     anio_escolar: string,
     *     nivel: string,
     *     grado: string,
     *     seccion: string,
     *     sede: string,
     *     malla_curso_ids: list<int>
     * }  $datos
     * @return array{asignaciones: Collection<int, DocenteCursoAula>, resumen: list<array{grado: string, seccion: string, cursos: list<array{area: string, curso: string}>}>}
     */
    public function sincronizar(array $datos): array
    {
        $docente = User::query()->findOrFail($datos['docente_id']);
        $this->validator->validarUsuarioDocente($docente);
        $this->validator->validarAnioEscolarActivo($datos['anio_escolar']);

        $contexto = [
            'anio_escolar' => $datos['anio_escolar'],
            'nivel' => $datos['nivel'],
            'grado' => $datos['grado'],
            'seccion' => $datos['seccion'],
            'sede' => $datos['sede'],
        ];

        $mallaCursoIds = array_values(array_unique($datos['malla_curso_ids'] ?? []));

        $this->validator->validarMallaCursosEnMallaGrado(
            $mallaCursoIds,
            $datos['anio_escolar'],
            $datos['nivel'],
            $datos['grado'],
        );
        $this->validator->validarMallaCursosActivos($mallaCursoIds);

        $asignaciones = DB::transaction(function () use ($docente, $contexto, $mallaCursoIds): Collection {
            $this->validarSinConflictoConOtrosDocentes($docente->id, $contexto, $mallaCursoIds);

            $propiasActivas = DocenteCursoAula::query()
                ->where($contexto)
                ->where('user_id', $docente->id)
                ->where('activo', true)
                ->get()
                ->keyBy('malla_curso_id');

            foreach ($mallaCursoIds as $mallaCursoId) {
                if ($propiasActivas->has($mallaCursoId)) {
                    continue;
                }

                DocenteCursoAula::query()->create([
                    'user_id' => $docente->id,
                    'malla_curso_id' => $mallaCursoId,
                    ...$contexto,
                    'activo' => true,
                ]);
            }

            foreach ($propiasActivas as $mallaCursoId => $asignacion) {
                if (! in_array($mallaCursoId, $mallaCursoIds, true)) {
                    $asignacion->update(['activo' => false]);
                }
            }

            return DocenteCursoAula::query()
                ->where($contexto)
                ->where('user_id', $docente->id)
                ->where('activo', true)
                ->with(['user:id,name,email', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
                ->orderBy('malla_curso_id')
                ->get();
        });

        return [
            'asignaciones' => $asignaciones,
            'resumen' => $this->construirResumenDocente(
                $docente->id,
                $datos['anio_escolar'],
                $datos['nivel'],
                $datos['sede'],
            ),
        ];
    }

    /**
     * @return list<array{grado: string, seccion: string, cursos: list<array{area: string, curso: string, malla_curso_id: int}>}>
     */
    public function construirResumenDocente(int $docenteId, string $anioEscolar, string $nivel, string $sede): array
    {
        $asignaciones = DocenteCursoAula::query()
            ->where('user_id', $docenteId)
            ->where('activo', true)
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('sede', $sede)
            ->with(['mallaCurso.area', 'mallaCurso.cursoCatalogo'])
            ->orderBy('grado')
            ->orderBy('seccion')
            ->orderBy('malla_curso_id')
            ->get();

        return $this->agruparResumen($asignaciones);
    }

    /**
     * @return list<array{grado: string, seccion: string, cursos: list<array{area: string, curso: string, malla_curso_id: int}>}>
     */
    public function construirResumenGeneral(string $anioEscolar, string $nivel, string $sede, ?string $grado = null, ?string $seccion = null): array
    {
        $query = DocenteCursoAula::query()
            ->where('activo', true)
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('sede', $sede)
            ->with(['user:id,name,email', 'mallaCurso.area', 'mallaCurso.cursoCatalogo'])
            ->orderBy('grado')
            ->orderBy('seccion')
            ->orderBy('malla_curso_id');

        if ($grado !== null && $grado !== '') {
            $query->where('grado', $grado);
        }
        if ($seccion !== null && $seccion !== '') {
            $query->where('seccion', $seccion);
        }

        return $this->agruparResumen($query->get(), incluirDocente: true);
    }

    /**
     * @param  Collection<int, DocenteCursoAula>  $asignaciones
     * @return list<array<string, mixed>>
     */
    private function agruparResumen(Collection $asignaciones, bool $incluirDocente = false): array
    {
        $grupos = [];

        foreach ($asignaciones as $asignacion) {
            $clave = $asignacion->grado.'|'.$asignacion->seccion;
            if (! isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'grado' => $asignacion->grado,
                    'seccion' => $asignacion->seccion,
                    'cursos' => [],
                ];
                if ($incluirDocente) {
                    $grupos[$clave]['docente'] = [
                        'id' => $asignacion->user?->id,
                        'name' => $asignacion->user?->name,
                    ];
                }
            }

            $curso = [
                'malla_curso_id' => $asignacion->malla_curso_id,
                'area' => $asignacion->mallaCurso?->area?->nombre ?? '',
                'curso' => $asignacion->mallaCurso?->cursoCatalogo?->nombre ?? '',
            ];

            if ($incluirDocente) {
                $curso['docente'] = [
                    'id' => $asignacion->user?->id,
                    'name' => $asignacion->user?->name,
                ];
            }

            $grupos[$clave]['cursos'][] = $curso;
        }

        return array_values($grupos);
    }

    /**
     * @param  array{anio_escolar: string, nivel: string, grado: string, seccion: string, sede: string}  $contexto
     * @param  list<int>  $mallaCursoIds
     */
    private function validarSinConflictoConOtrosDocentes(int $docenteId, array $contexto, array $mallaCursoIds): void
    {
        if ($mallaCursoIds === []) {
            return;
        }

        $conflictos = DocenteCursoAula::query()
            ->where($contexto)
            ->where('activo', true)
            ->whereIn('malla_curso_id', $mallaCursoIds)
            ->where('user_id', '!=', $docenteId)
            ->with(['user:id,name', 'mallaCurso.cursoCatalogo'])
            ->get();

        if ($conflictos->isEmpty()) {
            return;
        }

        $detalles = $conflictos->map(function (DocenteCursoAula $asignacion): string {
            $curso = $asignacion->mallaCurso?->cursoCatalogo?->nombre ?? 'curso';

            return sprintf(
                '%s ya está asignado a %s.',
                $curso,
                $asignacion->user?->name ?? 'otro docente',
            );
        })->implode(' ');

        throw new AsignacionDocenteDuplicadaException($detalles);
    }
}
