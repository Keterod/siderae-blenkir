<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre',
    'nivel',
    'grado',
    'anio_escolar',
    'sede',
    'activo',
])]
class Materia extends Model
{
    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class);
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}
