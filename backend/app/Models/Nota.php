<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'materia_id',
    'anio_escolar',
    'bimestre',
    'curso',
    'nota',
    'nota_conducta',
])]
class Nota extends Model
{
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
            'nota_conducta' => 'decimal:2',
        ];
    }
}
