<?php

namespace App\Services\Curricular;

use App\Models\Curricular\SeccionAula;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeccionAulaService
{
    /**
     * @return Collection<int, SeccionAula>
     */
    public function listar(
        ?string $nivel = null,
        ?string $grado = null,
        ?bool $activo = null,
        bool $incluirInactivas = false,
        ?string $q = null,
    ): Collection {
        $query = SeccionAula::query()
            ->orderBy('nivel')
            ->orderBy('grado')
            ->orderBy('orden')
            ->orderBy('nombre');

        if ($nivel !== null && $nivel !== '') {
            $query->where('nivel', $nivel);
        }

        if ($grado !== null && $grado !== '') {
            $query->where('grado', $grado);
        }

        if ($activo !== null) {
            $query->where('activo', $activo);
        } elseif (! $incluirInactivas) {
            $query->where('activo', true);
        }

        if ($q !== null && trim($q) !== '') {
            $termino = '%'.trim($q).'%';
            $query->where(function (Builder $sub) use ($termino): void {
                $sub->where('nombre', 'like', $termino)
                    ->orWhere('codigo', 'like', $termino);
            });
        }

        return $query->get();
    }

    /**
     * @param  array{
     *     nivel: string,
     *     grado: string,
     *     nombre: string,
     *     codigo?: string|null,
     *     orden?: int|null,
     *     activo?: bool|null
     * }  $datos
     */
    public function crear(array $datos): SeccionAula
    {
        $nivel = $datos['nivel'];
        $grado = $datos['grado'];
        $this->validarGrado($nivel, $grado);

        $nombre = $this->normalizarNombre($datos['nombre']);
        $activo = $datos['activo'] ?? true;

        $this->validarDuplicadoNombreActivo($nivel, $grado, $nombre);
        $this->assertNoExisteNombre($nivel, $grado, $nombre);

        $codigo = isset($datos['codigo']) && trim((string) $datos['codigo']) !== ''
            ? $this->normalizarCodigo($datos['codigo'])
            : $this->generarCodigoUnico($nivel, $grado, $nombre);

        $this->assertNoExisteCodigo($nivel, $grado, $codigo);

        $orden = $datos['orden'] ?? $this->siguienteOrden($nivel, $grado);

        return SeccionAula::query()->create([
            'nivel' => $nivel,
            'grado' => $grado,
            'nombre' => $nombre,
            'codigo' => $codigo,
            'orden' => $orden,
            'activo' => $activo,
        ]);
    }

    /**
     * @param  array{nombre?: string, codigo?: string|null, orden?: int|null}  $datos
     */
    public function actualizar(SeccionAula $seccion, array $datos): SeccionAula
    {
        $nombreCambio = false;

        if (isset($datos['nombre'])) {
            $nombre = $this->normalizarNombre($datos['nombre']);
            if ($nombre !== $seccion->nombre) {
                if ($seccion->activo) {
                    $this->validarDuplicadoNombreActivo(
                        $seccion->nivel,
                        $seccion->grado,
                        $nombre,
                        $seccion->id,
                    );
                    $this->assertNoExisteNombre(
                        $seccion->nivel,
                        $seccion->grado,
                        $nombre,
                        $seccion->id,
                    );
                }
                $seccion->nombre = $nombre;
                $nombreCambio = true;
            }
        }

        if (array_key_exists('codigo', $datos)) {
            $codigoRaw = $datos['codigo'];
            if ($codigoRaw === null || trim((string) $codigoRaw) === '') {
                $seccion->codigo = $this->generarCodigoUnico(
                    $seccion->nivel,
                    $seccion->grado,
                    $seccion->nombre,
                    $seccion->id,
                );
            } else {
                $codigo = $this->normalizarCodigo($codigoRaw);
                $this->assertNoExisteCodigo(
                    $seccion->nivel,
                    $seccion->grado,
                    $codigo,
                    $seccion->id,
                );
                $seccion->codigo = $codigo;
            }
        } elseif ($nombreCambio) {
            $seccion->codigo = $this->generarCodigoUnico(
                $seccion->nivel,
                $seccion->grado,
                $seccion->nombre,
                $seccion->id,
            );
        }

        if (array_key_exists('orden', $datos) && $datos['orden'] !== null) {
            $seccion->orden = (int) $datos['orden'];
        }

        $seccion->save();

        return $seccion->fresh();
    }

    public function desactivar(SeccionAula $seccion): SeccionAula
    {
        if (! $seccion->activo) {
            return $seccion;
        }

        $seccion->activo = false;
        $seccion->save();

        return $seccion->fresh();
    }

    public function reactivar(SeccionAula $seccion): SeccionAula
    {
        if ($seccion->activo) {
            return $seccion;
        }

        $this->validarDuplicadoNombreActivo(
            $seccion->nivel,
            $seccion->grado,
            $seccion->nombre,
            $seccion->id,
        );

        $seccion->activo = true;
        $seccion->save();

        return $seccion->fresh();
    }

    private function validarGrado(string $nivel, string $grado): void
    {
        if (! CatalogoNivelGrado::esGradoValido($nivel, $grado)) {
            throw ValidationException::withMessages([
                'grado' => ['El grado no es válido para el nivel indicado.'],
            ]);
        }
    }

    private function normalizarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
    }

    private function normalizarCodigo(string $codigo): string
    {
        $slug = Str::slug(trim($codigo), '_');

        return $slug !== '' ? $slug : 'seccion';
    }

    private function generarCodigoUnico(
        string $nivel,
        string $grado,
        string $nombre,
        ?int $excluirId = null,
    ): string {
        $base = Str::slug($nombre, '_');
        if ($base === '') {
            $base = 'seccion';
        }

        $codigo = $base;
        $sufijo = 2;

        while ($this->existeCodigo($nivel, $grado, $codigo, $excluirId)) {
            $codigo = $base.'_'.$sufijo;
            $sufijo++;
        }

        return $codigo;
    }

    private function siguienteOrden(string $nivel, string $grado): int
    {
        $max = SeccionAula::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->max('orden');

        return ((int) $max) + 1;
    }

    private function validarDuplicadoNombreActivo(
        string $nivel,
        string $grado,
        string $nombre,
        ?int $excluirId = null,
    ): void {
        $query = SeccionAula::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('nombre', $nombre)
            ->where('activo', true);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe una sección activa con ese nombre en el mismo nivel y grado.'],
            ]);
        }
    }

    private function assertNoExisteNombre(
        string $nivel,
        string $grado,
        string $nombre,
        ?int $excluirId = null,
    ): void {
        $query = SeccionAula::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('nombre', $nombre);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe una sección con ese nombre en el mismo nivel y grado.'],
            ]);
        }
    }

    private function assertNoExisteCodigo(
        string $nivel,
        string $grado,
        string $codigo,
        ?int $excluirId = null,
    ): void {
        if ($this->existeCodigo($nivel, $grado, $codigo, $excluirId)) {
            throw ValidationException::withMessages([
                'codigo' => ['Ya existe una sección con ese código en el mismo nivel y grado.'],
            ]);
        }
    }

    private function existeCodigo(
        string $nivel,
        string $grado,
        string $codigo,
        ?int $excluirId = null,
    ): bool {
        $query = SeccionAula::query()
            ->where('nivel', $nivel)
            ->where('grado', $grado)
            ->where('codigo', $codigo);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
