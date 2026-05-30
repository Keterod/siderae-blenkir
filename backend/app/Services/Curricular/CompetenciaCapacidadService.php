<?php

namespace App\Services\Curricular;

use App\Models\Curricular\Area;
use App\Models\Curricular\Capacidad;
use App\Models\Curricular\Competencia;
use App\Models\Curricular\TemaSemanal;
use Illuminate\Validation\ValidationException;

class CompetenciaCapacidadService
{
    public function normalizarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
    }

    /**
     * @param  array{nombre: string, descripcion?: string|null, codigo?: string|null}  $datos
     */
    public function crearCompetencia(Area $area, array $datos): Competencia
    {
        if (! $area->activo) {
            throw ValidationException::withMessages([
                'area_id' => ['El área está inactiva.'],
            ]);
        }

        $nombre = $this->normalizarNombre($datos['nombre']);
        if ($nombre === '') {
            throw ValidationException::withMessages([
                'nombre' => ['El nombre de la competencia no puede estar vacío.'],
            ]);
        }

        $this->validarDuplicadoCompetencia($area->id, $nombre);

        return Competencia::query()->create([
            'area_id' => $area->id,
            'nombre' => $nombre,
            'descripcion' => $datos['descripcion'] ?? null,
            'codigo' => isset($datos['codigo']) ? $this->normalizarCodigo($datos['codigo']) : null,
            'activo' => true,
        ]);
    }

    /**
     * @param  array{nombre?: string, descripcion?: string|null, codigo?: string|null}  $datos
     */
    public function actualizarCompetencia(Competencia $competencia, array $datos): Competencia
    {
        if (isset($datos['nombre'])) {
            $nombre = $this->normalizarNombre($datos['nombre']);
            if ($nombre === '') {
                throw ValidationException::withMessages([
                    'nombre' => ['El nombre de la competencia no puede estar vacío.'],
                ]);
            }
            $this->validarDuplicadoCompetencia($competencia->area_id, $nombre, $competencia->id);
            $competencia->nombre = $nombre;
        }

        if (array_key_exists('descripcion', $datos)) {
            $competencia->descripcion = $datos['descripcion'];
        }

        if (array_key_exists('codigo', $datos)) {
            $competencia->codigo = $this->normalizarCodigo($datos['codigo']);
        }

        $competencia->save();

        return $competencia->fresh();
    }

    public function desactivarCompetencia(Competencia $competencia): Competencia
    {
        if (! $competencia->activo) {
            return $competencia;
        }

        if ($this->contarUsoActivoCompetencia($competencia->id) > 0) {
            throw ValidationException::withMessages([
                'competencia' => ['No se puede desactivar: la competencia está vinculada a criterios activos.'],
            ]);
        }

        $competencia->update(['activo' => false]);

        return $competencia->fresh();
    }

    public function reactivarCompetencia(Competencia $competencia): Competencia
    {
        $area = Area::query()->find($competencia->area_id);
        if ($area === null || ! $area->activo) {
            throw ValidationException::withMessages([
                'competencia' => ['No se puede reactivar: el área asociada no está activa.'],
            ]);
        }

        $competencia->update(['activo' => true]);

        return $competencia->fresh();
    }

    /**
     * @param  array{nombre: string, descripcion?: string|null}  $datos
     */
    public function crearCapacidad(Competencia $competencia, array $datos): Capacidad
    {
        if (! $competencia->activo) {
            throw ValidationException::withMessages([
                'competencia_id' => ['La competencia está inactiva.'],
            ]);
        }

        $nombre = $this->normalizarNombre($datos['nombre']);
        if ($nombre === '') {
            throw ValidationException::withMessages([
                'nombre' => ['El nombre de la capacidad no puede estar vacío.'],
            ]);
        }

        $this->validarDuplicadoCapacidad($competencia->id, $nombre);

        return Capacidad::query()->create([
            'competencia_id' => $competencia->id,
            'nombre' => $nombre,
            'descripcion' => $datos['descripcion'] ?? null,
            'activo' => true,
        ]);
    }

    /**
     * @param  array{nombre?: string, descripcion?: string|null}  $datos
     */
    public function actualizarCapacidad(Capacidad $capacidad, array $datos): Capacidad
    {
        if (isset($datos['nombre'])) {
            $nombre = $this->normalizarNombre($datos['nombre']);
            if ($nombre === '') {
                throw ValidationException::withMessages([
                    'nombre' => ['El nombre de la capacidad no puede estar vacío.'],
                ]);
            }
            $this->validarDuplicadoCapacidad($capacidad->competencia_id, $nombre, $capacidad->id);
            $capacidad->nombre = $nombre;
        }

        if (array_key_exists('descripcion', $datos)) {
            $capacidad->descripcion = $datos['descripcion'];
        }

        $capacidad->save();

        return $capacidad->fresh();
    }

    public function desactivarCapacidad(Capacidad $capacidad): Capacidad
    {
        if (! $capacidad->activo) {
            return $capacidad;
        }

        if ($this->contarUsoActivoCapacidad($capacidad->id) > 0) {
            throw ValidationException::withMessages([
                'capacidad' => ['No se puede desactivar: la capacidad está vinculada a criterios activos.'],
            ]);
        }

        $capacidad->update(['activo' => false]);

        return $capacidad->fresh();
    }

    public function reactivarCapacidad(Capacidad $capacidad): Capacidad
    {
        $competencia = Competencia::query()->find($capacidad->competencia_id);
        if ($competencia === null || ! $competencia->activo) {
            throw ValidationException::withMessages([
                'capacidad' => ['No se puede reactivar: la competencia asociada no está activa.'],
            ]);
        }

        $capacidad->update(['activo' => true]);

        return $capacidad->fresh();
    }

    public function contarUsoActivoCompetencia(int $competenciaId): int
    {
        return (int) TemaSemanal::query()
            ->where('activo', true)
            ->whereHas('competencias', fn ($q) => $q->where('competencias.id', $competenciaId))
            ->count();
    }

    public function contarUsoActivoCapacidad(int $capacidadId): int
    {
        return (int) TemaSemanal::query()
            ->where('activo', true)
            ->whereHas('capacidades', fn ($q) => $q->where('capacidades.id', $capacidadId))
            ->count();
    }

    private function validarDuplicadoCompetencia(int $areaId, string $nombre, ?int $exceptoId = null): void
    {
        $query = Competencia::query()
            ->where('area_id', $areaId)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)]);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe una competencia con ese nombre en el área.'],
            ]);
        }
    }

    private function validarDuplicadoCapacidad(int $competenciaId, string $nombre, ?int $exceptoId = null): void
    {
        $query = Capacidad::query()
            ->where('competencia_id', $competenciaId)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)]);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe una capacidad con ese nombre en la competencia.'],
            ]);
        }
    }

    private function normalizarCodigo(?string $codigo): ?string
    {
        if ($codigo === null) {
            return null;
        }

        $norm = trim($codigo);

        return $norm === '' ? null : $norm;
    }
}
