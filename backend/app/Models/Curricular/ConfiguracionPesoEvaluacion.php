<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nivel',
    'grado',
    'area_id',
    'curso_catalogo_id',
    'peso_cuaderno',
    'peso_libro',
    'peso_tarea',
    'activo',
])]
class ConfiguracionPesoEvaluacion extends Model
{
    protected $table = 'configuracion_pesos_evaluacion';

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function cursoCatalogo(): BelongsTo
    {
        return $this->belongsTo(CursoCatalogo::class, 'curso_catalogo_id');
    }

    protected function casts(): array
    {
        return [
            'peso_cuaderno' => 'decimal:2',
            'peso_libro' => 'decimal:2',
            'peso_tarea' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
