<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['malla_curricular_id', 'area_id', 'curso_catalogo_id', 'orden', 'activo'])]
class MallaCurso extends Model
{
    protected $table = 'malla_cursos';

    public function mallaCurricular(): BelongsTo
    {
        return $this->belongsTo(MallaCurricular::class, 'malla_curricular_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function cursoCatalogo(): BelongsTo
    {
        return $this->belongsTo(CursoCatalogo::class, 'curso_catalogo_id');
    }

    public function temasSemanales(): HasMany
    {
        return $this->hasMany(TemaSemanal::class, 'malla_curso_id');
    }

    public function asignacionesDocente(): HasMany
    {
        return $this->hasMany(DocenteCursoAula::class, 'malla_curso_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
