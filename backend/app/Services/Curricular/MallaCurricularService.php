<?php

namespace App\Services\Curricular;

use App\Models\Curricular\CursoCatalogo;
use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PlantillaCurricular;
use App\Models\Curricular\PlantillaCurso;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MallaCurricularService
{
    public function obtenerOProvisionar(string $anioEscolar, string $nivel, string $grado): MallaCurricular
    {
        $malla = MallaCurricular::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->first();

        if ($malla !== null) {
            return $this->cargarRelaciones($malla);
        }

        return $this->materializarDesdePlantilla($anioEscolar, $nivel, $grado);
    }

    public function cargarDesdePlantilla(string $anioEscolar, string $nivel, string $grado): MallaCurricular
    {
        if (MallaCurricular::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->exists()) {
            throw ValidationException::withMessages([
                'anio_escolar' => ['Ya existe una malla curricular para este año, nivel y grado.'],
            ]);
        }

        return $this->materializarDesdePlantilla($anioEscolar, $nivel, $grado);
    }

    private function materializarDesdePlantilla(string $anioEscolar, string $nivel, string $grado): MallaCurricular
    {
        if (! CatalogoNivelGrado::esGradoValido($nivel, $grado)) {
            throw ValidationException::withMessages([
                'grado' => ['El grado no es válido para el nivel indicado.'],
            ]);
        }

        $plantilla = PlantillaCurricular::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('activo', true)
            ->first();

        if ($plantilla === null) {
            throw ValidationException::withMessages([
                'grado' => ['No hay plantilla curricular activa para este nivel y grado.'],
            ]);
        }

        return DB::transaction(function () use ($anioEscolar, $nivel, $grado, $plantilla) {
            $existente = MallaCurricular::query()
                ->where('anio_escolar', $anioEscolar)
                ->where('nivel', $nivel)
                ->where('grado', $grado)
                ->lockForUpdate()
                ->first();

            if ($existente !== null) {
                return $this->cargarRelaciones($existente);
            }

            $malla = MallaCurricular::query()->create([
                'anio_escolar' => $anioEscolar,
                'nivel' => $nivel,
                'grado' => $grado,
                'estado' => 'activa',
                'plantilla_curricular_id' => $plantilla->id,
            ]);

            if ($plantilla->detalle_completo) {
                $cursosPlantilla = PlantillaCurso::query()
                    ->where('plantilla_curricular_id', $plantilla->id)
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->get();

                foreach ($cursosPlantilla as $item) {
                    $yaExiste = MallaCurso::query()
                        ->where('malla_curricular_id', $malla->id)
                        ->where('area_id', $item->area_id)
                        ->where('curso_catalogo_id', $item->curso_catalogo_id)
                        ->exists();

                    if ($yaExiste) {
                        continue;
                    }

                    MallaCurso::query()->create([
                        'malla_curricular_id' => $malla->id,
                        'area_id' => $item->area_id,
                        'curso_catalogo_id' => $item->curso_catalogo_id,
                        'orden' => $item->orden,
                        'activo' => true,
                    ]);
                }
            }

            return $this->cargarRelaciones($malla);
        });
    }

    private function cargarRelaciones(MallaCurricular $malla): MallaCurricular
    {
        return $malla->load([
            'plantillaCurricular',
            'mallaCursos' => fn ($q) => $q->orderBy('orden'),
            'mallaCursos.area',
            'mallaCursos.cursoCatalogo',
        ]);
    }

    public function normalizarNombreCurso(string $nombre): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
    }

    /**
     * @param  array{area_id: int, curso_catalogo_id?: int, nombre?: string, orden?: int}  $data
     * @return array{malla_curso: MallaCurso, message: string}
     */
    public function agregarCurso(MallaCurricular $malla, array $data): array
    {
        $areaId = (int) $data['area_id'];
        $orden = (int) ($data['orden'] ?? 0);

        [$cursoCatalogo, $creadoCatalogo, $reutilizadoCatalogo] = $this->resolverCursoCatalogo(
            $areaId,
            isset($data['curso_catalogo_id']) ? (int) $data['curso_catalogo_id'] : null,
            $data['nombre'] ?? null,
        );

        $existente = MallaCurso::query()
            ->where('malla_curricular_id', $malla->id)
            ->where('area_id', $areaId)
            ->where('curso_catalogo_id', $cursoCatalogo->id)
            ->first();

        if ($existente?->activo) {
            throw ValidationException::withMessages([
                'curso_catalogo_id' => ['El curso ya existe activo en la malla.'],
            ]);
        }

        if ($existente && ! $existente->activo) {
            throw ValidationException::withMessages([
                'curso_catalogo_id' => ['El curso ya existe inactivo; use Reactivar.'],
            ]);
        }

        $mallaCurso = MallaCurso::query()->create([
            'malla_curricular_id' => $malla->id,
            'area_id' => $areaId,
            'curso_catalogo_id' => $cursoCatalogo->id,
            'orden' => $orden,
            'activo' => true,
        ]);

        $message = match (true) {
            $creadoCatalogo => 'Curso creado y agregado correctamente.',
            $reutilizadoCatalogo => 'Ya existe un curso con ese nombre en el catálogo; se agregó el existente.',
            default => 'Curso agregado correctamente.',
        };

        return [
            'malla_curso' => $mallaCurso->load(['area', 'cursoCatalogo']),
            'message' => $message,
        ];
    }

    /**
     * @return array{0: CursoCatalogo, 1: bool, 2: bool}
     */
    private function resolverCursoCatalogo(int $areaId, ?int $cursoCatalogoId, ?string $nombre): array
    {
        if ($cursoCatalogoId !== null) {
            $curso = CursoCatalogo::query()->find($cursoCatalogoId);

            if ($curso === null) {
                throw ValidationException::withMessages([
                    'curso_catalogo_id' => ['El curso del catálogo no existe.'],
                ]);
            }

            if ((int) $curso->area_id !== $areaId) {
                throw ValidationException::withMessages([
                    'curso_catalogo_id' => ['El curso no pertenece al área indicada.'],
                ]);
            }

            return [$curso, false, false];
        }

        $nombreNorm = $this->normalizarNombreCurso($nombre ?? '');
        if ($nombreNorm === '') {
            throw ValidationException::withMessages([
                'nombre' => ['El nombre del curso no puede estar vacío.'],
            ]);
        }

        $existente = CursoCatalogo::query()
            ->where('area_id', $areaId)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombreNorm)])
            ->first();

        if ($existente !== null) {
            return [$existente, false, true];
        }

        $curso = CursoCatalogo::query()->create([
            'area_id' => $areaId,
            'nombre' => $nombreNorm,
            'es_institucional' => false,
            'activo' => true,
        ]);

        return [$curso, true, false];
    }
}
