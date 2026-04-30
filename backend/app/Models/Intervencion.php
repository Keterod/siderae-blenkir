<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'alerta_id',
    'estudiante_id',
    'registrado_por',
    'tipo',
    'descripcion',
    'fecha',
])]
class Intervencion extends Model
{

    protected $table = 'intervenciones';

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class, 'alerta_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
