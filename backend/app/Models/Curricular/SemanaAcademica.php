<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['periodo_academico_id', 'numero_semana', 'fecha_inicio', 'fecha_fin', 'activo'])]
class SemanaAcademica extends Model
{
    protected $table = 'semanas_academicas';

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_academico_id');
    }

    public function temasSemanales(): HasMany
    {
        return $this->hasMany(TemaSemanal::class, 'semana_academica_id');
    }

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }
}
