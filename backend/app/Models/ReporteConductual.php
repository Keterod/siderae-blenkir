<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'registrado_por',
    'fecha',
    'tipo_conducta',
    'descripcion',
    'nivel_gravedad',
    'accion_inmediata',
    'estado',
])]
class ReporteConductual extends Model
{
    protected $table = 'reportes_conductuales';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * @param  Builder<ReporteConductual>  $query
     * @return Builder<ReporteConductual>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
