<?php

namespace App\Services\Curricular;

use App\Models\Curricular\ComponenteCalificacionNivel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ComponenteCalificacionNivelService
{
    private const TOLERANCIA_SUMA = 0.01;

    /** @var list<string> */
    private const NIVELES = [
        ComponenteCalificacionNivel::NIVEL_INICIAL,
        ComponenteCalificacionNivel::NIVEL_PRIMARIA,
        ComponenteCalificacionNivel::NIVEL_SECUNDARIA,
    ];

    /**
     * @return list<array{codigo: string, nombre: string, peso: float, es_predefinido: bool, orden: int}>
     */
    private function plantillaDefaultsPorNivel(string $nivel): array
    {
        return match ($nivel) {
            ComponenteCalificacionNivel::NIVEL_INICIAL => [
                ['codigo' => 'cuaderno', 'nombre' => 'Cuaderno', 'peso' => 100.0, 'es_predefinido' => true, 'orden' => 1],
            ],
            ComponenteCalificacionNivel::NIVEL_PRIMARIA,
            ComponenteCalificacionNivel::NIVEL_SECUNDARIA => [
                ['codigo' => 'cuaderno', 'nombre' => 'Cuaderno', 'peso' => 33.33, 'es_predefinido' => true, 'orden' => 1],
                ['codigo' => 'libro', 'nombre' => 'Libro', 'peso' => 33.33, 'es_predefinido' => true, 'orden' => 2],
                ['codigo' => 'tarea', 'nombre' => 'Tarea', 'peso' => 33.34, 'es_predefinido' => true, 'orden' => 3],
            ],
            default => throw ValidationException::withMessages([
                'nivel' => ['Nivel educativo no válido.'],
            ]),
        };
    }

    /**
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    public function listar(string $anioEscolar, ?string $nivel = null, ?bool $activo = null): Collection
    {
        $query = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->orderBy('nivel')
            ->orderBy('orden')
            ->orderBy('id');

        if ($nivel !== null) {
            $query->where('nivel', $nivel);
        }

        if ($activo !== null) {
            $query->where('activo', $activo);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    public function listarPorNivel(string $anioEscolar, string $nivel): Collection
    {
        $this->validarNivel($nivel);

        return $this->listar($anioEscolar, $nivel);
    }

    /**
     * @param  array{
     *     anio_escolar: string,
     *     nivel: string,
     *     nombre: string,
     *     codigo?: string|null,
     *     peso?: float|int|string|null,
     *     orden?: int|null,
     *     activo?: bool|null
     * }  $datos
     */
    public function crear(array $datos): ComponenteCalificacionNivel
    {
        $anioEscolar = trim($datos['anio_escolar']);
        $nivel = $datos['nivel'];
        $this->validarNivel($nivel);

        $nombre = $this->normalizarNombre($datos['nombre']);
        if ($nombre === '') {
            throw ValidationException::withMessages([
                'nombre' => ['El nombre del componente no puede estar vacío.'],
            ]);
        }

        $this->validarDuplicadoNombre($anioEscolar, $nivel, $nombre);

        $codigo = isset($datos['codigo']) && $datos['codigo'] !== ''
            ? $this->normalizarCodigo($datos['codigo'])
            : $this->generarCodigoUnico($anioEscolar, $nivel, $nombre);

        $this->validarDuplicadoCodigo($anioEscolar, $nivel, $codigo);

        $activo = $datos['activo'] ?? true;
        $peso = $activo ? (float) ($datos['peso'] ?? 0) : 0.0;

        if ($activo && $peso < 0) {
            throw ValidationException::withMessages([
                'peso' => ['El peso debe ser mayor o igual a 0.'],
            ]);
        }

        if ($activo) {
            $this->validarSumaActivosProyectadaParaCrear($anioEscolar, $nivel, $peso);
        }

        $orden = $datos['orden'] ?? $this->siguienteOrden($anioEscolar, $nivel);

        return DB::transaction(function () use ($anioEscolar, $nivel, $codigo, $nombre, $peso, $orden, $activo): ComponenteCalificacionNivel {
            $componente = ComponenteCalificacionNivel::query()->create([
                'anio_escolar' => $anioEscolar,
                'nivel' => $nivel,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'peso' => $peso,
                'orden' => $orden,
                'activo' => $activo,
                'es_predefinido' => false,
            ]);

            return $componente->fresh();
        });
    }

    /**
     * @param  array{nombre?: string, peso?: float|int|string|null, orden?: int|null}  $datos
     */
    public function actualizar(ComponenteCalificacionNivel $componente, array $datos): ComponenteCalificacionNivel
    {
        $cambios = [];

        if (isset($datos['nombre'])) {
            $nombre = $this->normalizarNombre($datos['nombre']);
            if ($nombre === '') {
                throw ValidationException::withMessages([
                    'nombre' => ['El nombre del componente no puede estar vacío.'],
                ]);
            }
            $this->validarDuplicadoNombre(
                $componente->anio_escolar,
                $componente->nivel,
                $nombre,
                $componente->id,
            );
            $componente->nombre = $nombre;
        }

        if (array_key_exists('orden', $datos) && $datos['orden'] !== null) {
            $componente->orden = (int) $datos['orden'];
        }

        if (array_key_exists('peso', $datos) && $datos['peso'] !== null) {
            if (! $componente->activo) {
                throw ValidationException::withMessages([
                    'peso' => ['No puede editar el peso de un componente inactivo. Reactívelo primero o use la sincronización atómica.'],
                ]);
            }

            $peso = (float) $datos['peso'];
            if ($peso < 0) {
                throw ValidationException::withMessages([
                    'peso' => ['El peso debe ser mayor o igual a 0.'],
                ]);
            }

            $cambios['peso'] = $peso;
            $this->validarConfiguracionActivaProyectada(
                $componente->anio_escolar,
                $componente->nivel,
                [$componente->id => $cambios],
            );
            $componente->peso = $peso;
        }

        $componente->save();

        return $componente->fresh();
    }

    public function desactivar(ComponenteCalificacionNivel $componente): ComponenteCalificacionNivel
    {
        if (! $componente->activo) {
            return $componente;
        }

        $activos = $this->queryActivos($componente->anio_escolar, $componente->nivel)->count();
        if ($activos <= 1) {
            throw ValidationException::withMessages([
                'activo' => ['Debe permanecer al menos un componente activo en el nivel.'],
            ]);
        }

        $componente->activo = false;
        $componente->peso = 0;
        $componente->save();

        $this->redistribuirActivosEquitativamente($componente->anio_escolar, $componente->nivel);

        return $componente->fresh();
    }

    /**
     * @param  array{peso?: float|int|string|null}  $datos
     */
    public function reactivar(ComponenteCalificacionNivel $componente, array $datos = []): ComponenteCalificacionNivel
    {
        if ($componente->activo) {
            return $componente;
        }

        $peso = array_key_exists('peso', $datos) && $datos['peso'] !== null
            ? (float) $datos['peso']
            : (float) $componente->peso;

        if ($peso <= 0) {
            throw ValidationException::withMessages([
                'peso' => ['Indique un peso mayor a 0 para reactivar el componente.'],
            ]);
        }

        $this->validarConfiguracionActivaProyectada(
            $componente->anio_escolar,
            $componente->nivel,
            [$componente->id => ['peso' => $peso, 'activo' => true]],
        );

        $componente->activo = true;
        $componente->peso = $peso;
        $componente->save();

        return $componente->fresh();
    }

    /**
     * @param  list<array{id: int, orden?: int, peso?: float|int|string|null, activo?: bool|null}>  $ordenes
     * @return Collection<int, ComponenteCalificacionNivel>
     */
    public function reordenar(string $anioEscolar, string $nivel, array $ordenes): Collection
    {
        $this->validarNivel($nivel);

        return DB::transaction(function () use ($anioEscolar, $nivel, $ordenes): Collection {
            $componentes = ComponenteCalificacionNivel::query()
                ->where('anio_escolar', $anioEscolar)
                ->where('nivel', $nivel)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $ids = array_column($ordenes, 'id');
            if ($componentes->count() === 0 || count($ids) === 0) {
                throw ValidationException::withMessages([
                    'ordenes' => ['Debe indicar al menos un componente del año y nivel.'],
                ]);
            }

            foreach ($ids as $id) {
                if (! $componentes->has($id)) {
                    throw ValidationException::withMessages([
                        'ordenes' => ['Uno o más componentes no pertenecen al año y nivel indicados.'],
                    ]);
                }
            }

            foreach ($ordenes as $indice => $item) {
                /** @var ComponenteCalificacionNivel $componente */
                $componente = $componentes->get($item['id']);

                if (array_key_exists('orden', $item) && $item['orden'] !== null) {
                    $componente->orden = (int) $item['orden'];
                }

                if (array_key_exists('activo', $item) && $item['activo'] !== null) {
                    $componente->activo = (bool) $item['activo'];
                    if (! $componente->activo) {
                        $componente->peso = 0;
                    }
                }

                if (array_key_exists('peso', $item) && $item['peso'] !== null) {
                    if (! $componente->activo) {
                        throw ValidationException::withMessages([
                            "ordenes.{$indice}.peso" => ['No puede asignar peso a un componente inactivo.'],
                        ]);
                    }

                    $peso = (float) $item['peso'];
                    if ($peso < 0) {
                        throw ValidationException::withMessages([
                            "ordenes.{$indice}.peso" => ['El peso debe ser mayor o igual a 0.'],
                        ]);
                    }

                    $componente->peso = $peso;
                }
            }

            $this->validarEstadoActivosColeccionCompleta($componentes);

            foreach ($componentes as $componente) {
                if ($componente->isDirty()) {
                    $componente->save();
                }
            }

            return $this->listarPorNivel($anioEscolar, $nivel);
        });
    }

    /**
     * @return array{
     *     suma: float,
     *     valido: bool,
     *     completo: bool,
     *     faltante: float,
     *     excede: float,
     *     cantidad_activos: int,
     *     componentes_activos: list<array{id: int, codigo: string, nombre: string, peso: float}>
     * }
     */
    public function evaluarSumaActivos(string $anioEscolar, string $nivel): array
    {
        $this->validarNivel($nivel);

        $activos = $this->queryActivos($anioEscolar, $nivel)->orderBy('orden')->get();
        $suma = round((float) $activos->sum(fn (ComponenteCalificacionNivel $c) => (float) $c->peso), 2);
        $completo = $activos->isNotEmpty() && abs($suma - 100.0) <= self::TOLERANCIA_SUMA;
        $excede = $activos->isNotEmpty() && $suma > 100.0 + self::TOLERANCIA_SUMA
            ? round($suma - 100.0, 2)
            : 0.0;
        $faltante = $activos->isNotEmpty() && ! $completo && $suma < 100.0 - self::TOLERANCIA_SUMA
            ? round(100.0 - $suma, 2)
            : 0.0;

        return [
            'suma' => $suma,
            'valido' => $completo,
            'completo' => $completo,
            'faltante' => $faltante,
            'excede' => $excede,
            'cantidad_activos' => $activos->count(),
            'componentes_activos' => $activos->map(fn (ComponenteCalificacionNivel $c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'peso' => (float) $c->peso,
            ])->values()->all(),
        ];
    }

    /**
     * Crea los defaults institucionales para los tres niveles si aún no existen filas en ese año.
     *
     * @return array{inicial: int, primaria: int, secundaria: int}
     */
    public function asegurarDefaults(string $anioEscolar): array
    {
        $anioEscolar = trim($anioEscolar);
        $creados = [];

        foreach (self::NIVELES as $nivel) {
            $existentes = ComponenteCalificacionNivel::query()
                ->where('anio_escolar', $anioEscolar)
                ->where('nivel', $nivel)
                ->count();

            if ($existentes > 0) {
                $creados[$nivel] = 0;

                continue;
            }

            $insertados = 0;
            foreach ($this->plantillaDefaultsPorNivel($nivel) as $def) {
                ComponenteCalificacionNivel::query()->create([
                    'anio_escolar' => $anioEscolar,
                    'nivel' => $nivel,
                    'codigo' => $def['codigo'],
                    'nombre' => $def['nombre'],
                    'peso' => $def['peso'],
                    'orden' => $def['orden'],
                    'activo' => true,
                    'es_predefinido' => $def['es_predefinido'],
                ]);
                $insertados++;
            }
            $creados[$nivel] = $insertados;
        }

        return $creados;
    }

    public function validarSumaActivos(string $anioEscolar, string $nivel): void
    {
        $evaluacion = $this->evaluarSumaActivos($anioEscolar, $nivel);

        if ($evaluacion['cantidad_activos'] === 0) {
            throw ValidationException::withMessages([
                'peso' => ['Debe haber al menos un componente activo en el nivel.'],
            ]);
        }

        if (! $evaluacion['valido']) {
            throw ValidationException::withMessages([
                'peso' => [
                    sprintf(
                        'La suma de los pesos de los componentes activos debe ser 100 (actual: %s).',
                        $evaluacion['suma']
                    ),
                ],
            ]);
        }
    }

    private function validarNivel(string $nivel): void
    {
        if (! in_array($nivel, self::NIVELES, true)) {
            throw ValidationException::withMessages([
                'nivel' => ['Nivel educativo no válido.'],
            ]);
        }
    }

    private function validarDuplicadoCodigo(string $anioEscolar, string $nivel, string $codigo, ?int $exceptId = null): void
    {
        $query = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('codigo', $codigo);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'codigo' => ['Ya existe un componente con ese código en el año y nivel indicados.'],
            ]);
        }
    }

    private function validarDuplicadoNombre(string $anioEscolar, string $nivel, string $nombre, ?int $exceptId = null): void
    {
        $normalizado = $this->normalizarNombreParaComparacion($nombre);

        $query = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $duplicado = $query->get()->first(
            fn (ComponenteCalificacionNivel $c) => $this->normalizarNombreParaComparacion($c->nombre) === $normalizado
        );

        if ($duplicado !== null) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe un componente con ese nombre en el año y nivel indicados.'],
            ]);
        }
    }

    private function queryActivos(string $anioEscolar, string $nivel): Builder
    {
        return ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('activo', true);
    }

    private function siguienteOrden(string $anioEscolar, string $nivel): int
    {
        $max = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->max('orden');

        return $max !== null ? ((int) $max) + 1 : 1;
    }

    private function generarCodigoUnico(string $anioEscolar, string $nivel, string $nombre): string
    {
        $base = Str::slug(Str::ascii($nombre), '_');
        if ($base === '') {
            $base = 'componente';
        }

        $codigo = $base;
        $sufijo = 2;
        while ($this->codigoExiste($anioEscolar, $nivel, $codigo)) {
            $codigo = $base.'_'.$sufijo;
            $sufijo++;
        }

        return $codigo;
    }

    private function codigoExiste(string $anioEscolar, string $nivel, string $codigo): bool
    {
        return ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->where('codigo', $codigo)
            ->exists();
    }

    private function normalizarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
    }

    private function normalizarNombreParaComparacion(string $nombre): string
    {
        return mb_strtolower($this->normalizarNombre($nombre));
    }

    private function normalizarCodigo(string $codigo): string
    {
        $norm = Str::slug(Str::ascii(trim($codigo)), '_');

        if ($norm === '') {
            throw ValidationException::withMessages([
                'codigo' => ['El código del componente no es válido.'],
            ]);
        }

        return $norm;
    }

    private function redistribuirActivosEquitativamente(string $anioEscolar, string $nivel): void
    {
        $activos = $this->queryActivos($anioEscolar, $nivel)->orderBy('orden')->get();

        if ($activos->isEmpty()) {
            return;
        }

        $cantidad = $activos->count();
        $base = round(100 / $cantidad, 2);
        $pesos = array_fill(0, $cantidad, $base);
        $ajuste = round(100 - array_sum($pesos), 2);
        $pesos[$cantidad - 1] = round($pesos[$cantidad - 1] + $ajuste, 2);

        foreach ($activos as $indice => $activo) {
            $activo->peso = $pesos[$indice];
            $activo->save();
        }
    }

    private function validarSumaActivosProyectadaParaCrear(string $anioEscolar, string $nivel, float $nuevoPeso): void
    {
        $sumaActual = round((float) $this->queryActivos($anioEscolar, $nivel)->sum('peso'), 2);
        $sumaProyectada = round($sumaActual + $nuevoPeso, 2);

        if ($sumaProyectada > 100.0 + self::TOLERANCIA_SUMA) {
            throw ValidationException::withMessages([
                'peso' => [
                    sprintf(
                        'La suma de componentes activos no puede superar 100%% (quedaría: %s).',
                        $sumaProyectada,
                    ),
                ],
            ]);
        }
    }

    /**
     * @param  array<int, array{peso?: float, activo?: bool}>  $cambiosPorId
     */
    private function validarConfiguracionActivaProyectada(
        string $anioEscolar,
        string $nivel,
        array $cambiosPorId,
    ): void {
        $componentes = ComponenteCalificacionNivel::query()
            ->where('anio_escolar', $anioEscolar)
            ->where('nivel', $nivel)
            ->get();

        foreach ($componentes as $componente) {
            $cambio = $cambiosPorId[$componente->id] ?? [];

            if (array_key_exists('activo', $cambio)) {
                $componente->activo = (bool) $cambio['activo'];
                if (! $componente->activo) {
                    $componente->peso = 0;
                }
            }

            if (array_key_exists('peso', $cambio) && $componente->activo) {
                $componente->peso = (float) $cambio['peso'];
            }
        }

        $this->validarEstadoActivosColeccionParaConfiguracion($componentes);
    }

    /**
     * @param  Collection<int|string, ComponenteCalificacionNivel>  $componentes
     */
    private function validarEstadoActivosColeccionParaConfiguracion(Collection $componentes): void
    {
        $activos = $componentes->filter(fn (ComponenteCalificacionNivel $c) => $c->activo);

        if ($activos->isEmpty()) {
            throw ValidationException::withMessages([
                'activo' => ['Debe permanecer al menos un componente activo en el nivel.'],
            ]);
        }

        $suma = round((float) $activos->sum(fn (ComponenteCalificacionNivel $c) => (float) $c->peso), 2);

        if ($suma > 100.0 + self::TOLERANCIA_SUMA) {
            throw ValidationException::withMessages([
                'peso' => [
                    sprintf(
                        'La suma de componentes activos no puede superar 100%% (actual: %s).',
                        $suma,
                    ),
                ],
            ]);
        }
    }

    /**
     * @param  Collection<int|string, ComponenteCalificacionNivel>  $componentes
     */
    private function validarEstadoActivosColeccionCompleta(Collection $componentes): void
    {
        $activos = $componentes->filter(fn (ComponenteCalificacionNivel $c) => $c->activo);

        if ($activos->isEmpty()) {
            throw ValidationException::withMessages([
                'activo' => ['Debe permanecer al menos un componente activo en el nivel.'],
            ]);
        }

        $suma = round((float) $activos->sum(fn (ComponenteCalificacionNivel $c) => (float) $c->peso), 2);

        if (abs($suma - 100.0) > self::TOLERANCIA_SUMA) {
            throw ValidationException::withMessages([
                'peso' => [
                    sprintf(
                        'La suma de los pesos de los componentes activos debe ser 100 (actual: %s).',
                        $suma,
                    ),
                ],
            ]);
        }
    }
}
