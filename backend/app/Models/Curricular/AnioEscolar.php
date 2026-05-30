<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'anio',
    'nombre',
    'fecha_inicio',
    'fecha_fin',
    'estado',
    'es_activo',
])]
class AnioEscolar extends Model
{
    protected $table = 'anios_escolares';

    public function periodosAcademicos(): HasMany
    {
        return $this->hasMany(PeriodoAcademico::class, 'anio_escolar_id');
    }

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_activo' => 'boolean',
        ];
    }
}
