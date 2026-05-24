<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['anio_escolar', 'nivel', 'grado', 'estado', 'plantilla_curricular_id'])]
class MallaCurricular extends Model
{
    protected $table = 'mallas_curriculares';

    public function plantillaCurricular(): BelongsTo
    {
        return $this->belongsTo(PlantillaCurricular::class, 'plantilla_curricular_id');
    }

    public function mallaCursos(): HasMany
    {
        return $this->hasMany(MallaCurso::class, 'malla_curricular_id');
    }

    protected function casts(): array
    {
        return [];
    }
}
