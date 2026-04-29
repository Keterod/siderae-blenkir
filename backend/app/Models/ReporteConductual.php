<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
])]
class ReporteConductual extends Model
{
    protected $table = 'reportes_conductuales';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
