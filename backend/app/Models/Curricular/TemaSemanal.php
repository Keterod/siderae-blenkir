<?php

namespace App\Models\Curricular;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'malla_curso_id',
    'periodo_academico_id',
    'semana_academica_id',
    'titulo',
    'descripcion',
    'creado_por',
    'activo',
])]
class TemaSemanal extends Model
{
    protected $table = 'temas_semanales';

    public function mallaCurso(): BelongsTo
    {
        return $this->belongsTo(MallaCurso::class, 'malla_curso_id');
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_academico_id');
    }

    public function semanaAcademica(): BelongsTo
    {
        return $this->belongsTo(SemanaAcademica::class, 'semana_academica_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function competencias(): BelongsToMany
    {
        return $this->belongsToMany(Competencia::class, 'tema_competencias', 'tema_semanal_id', 'competencia_id')
            ->withTimestamps();
    }

    public function capacidades(): BelongsToMany
    {
        return $this->belongsToMany(Capacidad::class, 'tema_capacidades', 'tema_semanal_id', 'capacidad_id')
            ->withPivot('competencia_id')
            ->withTimestamps();
    }

    public function notasSemanales(): HasMany
    {
        return $this->hasMany(NotaSemanal::class, 'tema_semanal_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
