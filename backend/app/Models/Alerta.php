<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'estudiante_id',
    'indice_riesgo_id',
    'estado',
    'factores_influyentes',
    'recomendacion',
    'resultado_cierre',
    'cerrada_por',
    'fecha_cierre',
])]
class Alerta extends Model
{
    public const ESTADOS_ACTIVOS = ['pendiente', 'en_atencion'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function indiceRiesgo(): BelongsTo
    {
        return $this->belongsTo(IndiceRiesgo::class, 'indice_riesgo_id');
    }

    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'alerta_id');
    }

    public static function existeActivaParaEstudiante(int $estudianteId): bool
    {
        return static::query()
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->exists();
    }

    public static function crearPorRiesgoAltoSiAplica(Estudiante $estudiante, IndiceRiesgo $indice): ?self
    {
        if ($indice->nivel !== 'Alto') {
            return null;
        }

        if (self::existeActivaParaEstudiante($estudiante->id)) {
            return null;
        }

        return self::query()->create([
            'estudiante_id' => $estudiante->id,
            'indice_riesgo_id' => $indice->id,
            'estado' => 'pendiente',
            'factores_influyentes' => $indice->variables_utilizadas,
            'recomendacion' => 'Riesgo académico alto. Coordinar seguimiento académico, socioemocional y comunicación con la familia.',
        ]);
    }

    protected function casts(): array
    {
        return [
            'factores_influyentes' => 'array',
            'fecha_cierre' => 'datetime',
        ];
    }
}
