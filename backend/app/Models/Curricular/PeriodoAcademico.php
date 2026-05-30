<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'anio_escolar_id',
    'anio_escolar',
    'bimestre',
    'fecha_inicio',
    'fecha_fin',
    'semanas_planificadas',
    'activo',
    'estado',
    'es_vigente',
])]
class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function semanasAcademicas(): HasMany
    {
        return $this->hasMany(SemanaAcademica::class, 'periodo_academico_id');
    }

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
            'es_vigente' => 'boolean',
        ];
    }
}
