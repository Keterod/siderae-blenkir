<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'semana_inicio',
    'estado',
    'anio_escolar',
    'bimestre',
    'registrado_por',
])]
class Asistencia extends Model
{
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
            'semana_inicio' => 'date',
        ];
    }
}
