<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plantilla_curricular_id', 'area_id', 'curso_catalogo_id', 'orden', 'activo'])]
class PlantillaCurso extends Model
{
    protected $table = 'plantilla_cursos';

    public function plantillaCurricular(): BelongsTo
    {
        return $this->belongsTo(PlantillaCurricular::class, 'plantilla_curricular_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function cursoCatalogo(): BelongsTo
    {
        return $this->belongsTo(CursoCatalogo::class, 'curso_catalogo_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
