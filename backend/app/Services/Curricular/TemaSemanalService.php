<?php

namespace App\Services\Curricular;

use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\TemaSemanal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TemaSemanalService
{
    public function __construct(
        private readonly TemaSemanalValidator $temaSemanalValidator = new TemaSemanalValidator,
    ) {}

    /**
     * @param  array{
     *   malla_curso_id: int,
     *   periodo_academico_id: int,
     *   semana_academica_id?: int|null,
     *   titulo: string,
     *   descripcion?: string|null,
     *   creado_por?: int|null,
     *   activo?: bool,
     *   competencia_ids: list<int>,
     *   capacidad_ids: list<int>,
     * }  $datos
     */
    public function crear(array $datos): TemaSemanal
    {
        $this->validarCompetenciasCapacidades($datos);
        $this->validarSemanaReferencial($datos);

        $mallaCurso = MallaCurso::query()->with('area')->findOrFail($datos['malla_curso_id']);

        if (! $mallaCurso->activo) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['El curso de malla está inactivo.'],
            ]);
        }

        $activo = $datos['activo'] ?? true;
        if ($activo) {
            $this->temaSemanalValidator->validarDuplicadoExacto([
                'malla_curso_id' => $datos['malla_curso_id'],
                'periodo_academico_id' => $datos['periodo_academico_id'],
                'titulo' => $datos['titulo'],
                'competencia_ids' => $datos['competencia_ids'],
                'capacidad_ids' => $datos['capacidad_ids'],
            ]);
        }

        return DB::transaction(function () use ($datos, $activo, $mallaCurso) {
            $tema = TemaSemanal::query()->create([
                'malla_curso_id' => $datos['malla_curso_id'],
                'periodo_academico_id' => $datos['periodo_academico_id'],
                'semana_academica_id' => $datos['semana_academica_id'] ?? null,
                'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'] ?? null,
                'creado_por' => $datos['creado_por'] ?? null,
                'activo' => $activo,
            ]);

            $this->sincronizarRelaciones($tema, $datos['competencia_ids'], $datos['capacidad_ids'], $mallaCurso->area_id);

            return $tema->load([
                'mallaCurso.area',
                'mallaCurso.cursoCatalogo',
                'periodoAcademico',
                'semanaAcademica',
                'competencias',
                'capacidades',
            ]);
        });
    }

    /**
     * @param  array{
     *   titulo?: string,
     *   descripcion?: string|null,
     *   activo?: bool,
     *   competencia_ids?: list<int>,
     *   capacidad_ids?: list<int>,
     * }  $datos
     */
    public function actualizar(TemaSemanal $tema, array $datos): TemaSemanal
    {
        if (isset($datos['competencia_ids'], $datos['capacidad_ids'])) {
            $this->validarCompetenciasCapacidades([
                'malla_curso_id' => $tema->malla_curso_id,
                'competencia_ids' => $datos['competencia_ids'],
                'capacidad_ids' => $datos['capacidad_ids'],
            ]);
        }

        $activo = $datos['activo'] ?? $tema->activo;

        $competenciaIds = $datos['competencia_ids'] ?? $tema->competencias()->pluck('id')->all();
        $capacidadIds = $datos['capacidad_ids'] ?? $tema->capacidades()->pluck('id')->all();
        $titulo = $datos['titulo'] ?? $tema->titulo;

        if ($activo) {
            $this->temaSemanalValidator->validarDuplicadoExacto([
                'malla_curso_id' => $tema->malla_curso_id,
                'periodo_academico_id' => $tema->periodo_academico_id,
                'titulo' => $titulo,
                'competencia_ids' => $competenciaIds,
                'capacidad_ids' => $capacidadIds,
            ], $tema->id);
        }

        return DB::transaction(function () use ($tema, $datos, $activo, $competenciaIds, $capacidadIds) {
            if (isset($datos['titulo'])) {
                $tema->titulo = $datos['titulo'];
            }
            if (array_key_exists('descripcion', $datos)) {
                $tema->descripcion = $datos['descripcion'];
            }
            if (array_key_exists('activo', $datos)) {
                $tema->activo = $activo;
            }

            if (array_key_exists('semana_academica_id', $datos)) {
                $this->validarSemanaReferencial([
                    'periodo_academico_id' => $tema->periodo_academico_id,
                    'semana_academica_id' => $datos['semana_academica_id'],
                ]);
                $tema->semana_academica_id = $datos['semana_academica_id'];
            }

            $tema->save();

            if (isset($datos['competencia_ids'], $datos['capacidad_ids'])) {
                $areaId = $tema->mallaCurso()->value('area_id');
                $this->sincronizarRelaciones($tema, $datos['competencia_ids'], $datos['capacidad_ids'], (int) $areaId);
            }

            return $tema->fresh([
                'mallaCurso.area',
                'mallaCurso.cursoCatalogo',
                'periodoAcademico',
                'semanaAcademica',
                'competencias',
                'capacidades',
            ]);
        });
    }

    /**
     * @param  array{malla_curso_id: int, competencia_ids: list<int>, capacidad_ids: list<int>}  $datos
     */
    private function validarCompetenciasCapacidades(array $datos): void
    {
        if (count($datos['competencia_ids'] ?? []) < 1) {
            throw ValidationException::withMessages([
                'competencia_ids' => ['Debe seleccionar al menos una competencia.'],
            ]);
        }

        if (count($datos['capacidad_ids'] ?? []) < 1) {
            throw ValidationException::withMessages([
                'capacidad_ids' => ['Debe seleccionar al menos una capacidad.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()->findOrFail($datos['malla_curso_id']);
        $areaId = $mallaCurso->area_id;

        $competenciasValidas = Competencia::query()
            ->whereIn('id', $datos['competencia_ids'])
            ->where('area_id', $areaId)
            ->where('activo', true)
            ->count();

        if ($competenciasValidas !== count(array_unique($datos['competencia_ids']))) {
            throw ValidationException::withMessages([
                'competencia_ids' => ['Una o más competencias no pertenecen al área del curso.'],
            ]);
        }

        $capacidadesValidas = Capacidad::query()
            ->whereIn('id', $datos['capacidad_ids'])
            ->whereIn('competencia_id', $datos['competencia_ids'])
            ->where('activo', true)
            ->count();

        if ($capacidadesValidas !== count(array_unique($datos['capacidad_ids']))) {
            throw ValidationException::withMessages([
                'capacidad_ids' => ['Una o más capacidades no pertenecen a las competencias seleccionadas.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $competenciaIds
     * @param  list<int>  $capacidadIds
     */
    private function sincronizarRelaciones(TemaSemanal $tema, array $competenciaIds, array $capacidadIds, int $areaId): void
    {
        $tema->competencias()->sync($competenciaIds);

        $filasCapacidad = [];
        foreach ($capacidadIds as $capacidadId) {
            $capacidad = Capacidad::query()->findOrFail($capacidadId);
            $filasCapacidad[] = [
                'competencia_id' => $capacidad->competencia_id,
                'capacidad_id' => $capacidad->id,
            ];
        }

        DB::table('tema_capacidades')->where('tema_semanal_id', $tema->id)->delete();
        foreach ($filasCapacidad as $fila) {
            DB::table('tema_capacidades')->insert([
                'tema_semanal_id' => $tema->id,
                'competencia_id' => $fila['competencia_id'],
                'capacidad_id' => $fila['capacidad_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param  array{periodo_academico_id: int, semana_academica_id?: int|null}  $datos
     */
    private function validarSemanaReferencial(array $datos): void
    {
        if (empty($datos['semana_academica_id'])) {
            return;
        }

        $pertenece = \App\Models\Curricular\SemanaAcademica::query()
            ->where('id', $datos['semana_academica_id'])
            ->where('periodo_academico_id', $datos['periodo_academico_id'])
            ->exists();

        if (! $pertenece) {
            throw ValidationException::withMessages([
                'semana_academica_id' => ['La semana referencial no pertenece al bimestre seleccionado.'],
            ]);
        }
    }
}
